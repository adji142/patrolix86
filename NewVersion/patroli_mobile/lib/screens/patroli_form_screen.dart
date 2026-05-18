import 'dart:async';
import 'dart:convert';
import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:geolocator/geolocator.dart';
import 'package:image_picker/image_picker.dart';
import 'package:latlong2/latlong.dart';
import 'package:patroli_mobile/services/patroli_service.dart';

class PatroliFormScreen extends StatefulWidget {
  final String token;
  final String kodeCheckPoint;
  final int? shift;
  final int? shiftJadwal;

  const PatroliFormScreen({
    super.key,
    required this.token,
    required this.kodeCheckPoint,
    this.shift,
    this.shiftJadwal,
  });

  @override
  State<PatroliFormScreen> createState() => _PatroliFormScreenState();
}

class _PatroliFormScreenState extends State<PatroliFormScreen> {
  final PatroliService _service = PatroliService();
  final ImagePicker _imagePicker = ImagePicker();
  final MapController _mapController = MapController();
  final TextEditingController _keteranganCtrl = TextEditingController();

  Position? _position;
  StreamSubscription<Position>? _positionSub;
  bool _isMapReady = false;
  bool _isLoadingLocation = true;
  String? _locationError;

  Uint8List? _imageBytes;
  bool _isSubmitting = false;

  DateTime _currentTime = DateTime.now();
  Timer? _clockTimer;

  @override
  void initState() {
    super.initState();
    _initLocation();
    _clockTimer = Timer.periodic(const Duration(seconds: 1), (_) {
      if (mounted) setState(() => _currentTime = DateTime.now());
    });
  }

  @override
  void dispose() {
    _positionSub?.cancel();
    _clockTimer?.cancel();
    _keteranganCtrl.dispose();
    _mapController.dispose();
    super.dispose();
  }

  Future<void> _initLocation() async {
    final hasPermission = await _requestLocationPermission();
    if (!hasPermission) {
      if (mounted) {
        setState(() {
          _isLoadingLocation = false;
          _locationError = 'Izin lokasi ditolak. Aktifkan di pengaturan.';
        });
      }
      return;
    }

    try {
      final pos = await Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(
          accuracy: LocationAccuracy.high,
        ),
      );
      if (mounted) {
        setState(() {
          _position = pos;
          _isLoadingLocation = false;
        });
        if (_isMapReady) {
          _mapController.move(LatLng(pos.latitude, pos.longitude), 17);
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoadingLocation = false;
          _locationError = 'Gagal mendapatkan lokasi.';
        });
      }
      return;
    }

    // Stream untuk real-time update
    _positionSub = Geolocator.getPositionStream(
      locationSettings: const LocationSettings(
        accuracy: LocationAccuracy.high,
        distanceFilter: 5,
      ),
    ).listen((pos) {
      if (!mounted) return;
      setState(() => _position = pos);
      if (_isMapReady) {
        _mapController.move(LatLng(pos.latitude, pos.longitude), 17);
      }
    });
  }

  Future<bool> _requestLocationPermission() async {
    final serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) return false;

    var permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
    }
    return permission == LocationPermission.whileInUse ||
        permission == LocationPermission.always;
  }

  Future<void> _takePhoto() async {
    // imageQuality: 80 = kompres JPEG ke 80% sebelum dipakai
    final XFile? photo = await _imagePicker.pickImage(
      source: ImageSource.camera,
      maxWidth: 1280,
      maxHeight: 1280,
      imageQuality: 80,
    );
    if (photo == null) return;

    final bytes = await photo.readAsBytes();
    if (mounted) setState(() => _imageBytes = bytes);
  }

  Future<void> _submit() async {
    if (_position == null) {
      _showSnackbar('Lokasi GPS belum ditemukan. Tunggu sebentar.', isError: true);
      return;
    }
    if (_imageBytes == null) {
      _showSnackbar('Foto wajib diambil terlebih dahulu.', isError: true);
      return;
    }

    setState(() => _isSubmitting = true);
    try {
      final base64Image = base64Encode(_imageBytes!);
      final koordinat =
          '${_position!.latitude.toStringAsFixed(6)},${_position!.longitude.toStringAsFixed(6)}';

      await _service.submitPatroli(
        token: widget.token,
        kodeCheckPoint: widget.kodeCheckPoint,
        koordinat: koordinat,
        image: base64Image,
        catatan: _keteranganCtrl.text.trim(),
        shift: widget.shift,
        shiftJadwal: widget.shiftJadwal,
        tanggalPatroli: _formatApiDateTime(_currentTime),
      );

      if (mounted) {
        _showSnackbar('Data patroli berhasil disimpan!');
        Navigator.pop(context, true);
      }
    } catch (e) {
      if (mounted) {
        _showSnackbar(
          e.toString().replaceFirst('Exception: ', ''),
          isError: true,
        );
      }
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  String _formatApiDateTime(DateTime dt) {
    final y = dt.year;
    final mo = dt.month.toString().padLeft(2, '0');
    final d = dt.day.toString().padLeft(2, '0');
    final h = dt.hour.toString().padLeft(2, '0');
    final mi = dt.minute.toString().padLeft(2, '0');
    final s = dt.second.toString().padLeft(2, '0');
    return '$y-$mo-$d $h:$mi:$s';
  }

  String _formatDisplayDate(DateTime dt) {
    const days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    const months = [
      '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    final dayName = days[dt.weekday - 1];
    return '$dayName, ${dt.day.toString().padLeft(2, '0')} ${months[dt.month]} ${dt.year}';
  }

  String _formatDisplayTime(DateTime dt) {
    final h = dt.hour.toString().padLeft(2, '0');
    final m = dt.minute.toString().padLeft(2, '0');
    final s = dt.second.toString().padLeft(2, '0');
    return '$h:$m:$s';
  }

  void _showSnackbar(String message, {bool isError = false}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: isError ? Colors.red.shade700 : const Color(0xFF1B8B4B),
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final bool canSubmit =
        _position != null && _imageBytes != null && !_isSubmitting;

    return Scaffold(
      backgroundColor: const Color(0xFFF0F4FA),
      appBar: AppBar(
        backgroundColor: const Color(0xFF0D2B5E),
        foregroundColor: Colors.white,
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Form Patroli',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
            ),
            Text(
              widget.kodeCheckPoint,
              style: TextStyle(
                fontSize: 12,
                color: Colors.white.withValues(alpha: 0.7),
              ),
            ),
          ],
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            _buildDateTimeCard(),
            const SizedBox(height: 14),
            _buildLocationCard(),
            const SizedBox(height: 14),
            _buildPhotoCard(),
            const SizedBox(height: 14),
            _buildKeteranganCard(),
            const SizedBox(height: 24),
            _buildSubmitButton(canSubmit),
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

  // ── Kartu Tanggal & Jam ──────────────────────────────────────────────────────

  Widget _buildDateTimeCard() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF0D2B5E), Color(0xFF1A4A9E)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0D2B5E).withValues(alpha: 0.3),
            blurRadius: 8,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.15),
              shape: BoxShape.circle,
            ),
            child: const Icon(Icons.access_time, color: Colors.white, size: 22),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  _formatDisplayDate(_currentTime),
                  style: TextStyle(
                    color: Colors.white.withValues(alpha: 0.85),
                    fontSize: 12,
                    fontWeight: FontWeight.w400,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  _formatDisplayTime(_currentTime),
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 28,
                    fontWeight: FontWeight.bold,
                    fontFeatures: [FontFeature.tabularFigures()],
                  ),
                ),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.15),
              borderRadius: BorderRadius.circular(20),
            ),
            child: const Text(
              'LIVE',
              style: TextStyle(
                color: Colors.white,
                fontSize: 11,
                fontWeight: FontWeight.bold,
                letterSpacing: 1,
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ── Kartu Lokasi ────────────────────────────────────────────────────────────

  Widget _buildLocationCard() {
    return _card(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _cardHeader(
            Icons.location_on_outlined,
            'Titik Koordinat',
            required: true,
          ),
          const SizedBox(height: 12),
          ClipRRect(
            borderRadius: BorderRadius.circular(10),
            child: SizedBox(
              height: 200,
              child: _buildMap(),
            ),
          ),
          const SizedBox(height: 10),
          _buildCoordinateInfo(),
        ],
      ),
    );
  }

  Widget _buildMap() {
    if (_isLoadingLocation) {
      return Container(
        color: const Color(0xFFE8EDF5),
        child: const Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              CircularProgressIndicator(color: Color(0xFF0D2B5E)),
              SizedBox(height: 10),
              Text('Mencari lokasi GPS...',
                  style: TextStyle(fontSize: 12, color: Colors.grey)),
            ],
          ),
        ),
      );
    }

    if (_locationError != null && _position == null) {
      return Container(
        color: const Color(0xFFFFF4E5),
        child: Center(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.location_off, color: Colors.orange, size: 36),
                const SizedBox(height: 8),
                Text(
                  _locationError!,
                  textAlign: TextAlign.center,
                  style:
                      const TextStyle(fontSize: 12, color: Colors.orange),
                ),
              ],
            ),
          ),
        ),
      );
    }

    final center = _position != null
        ? LatLng(_position!.latitude, _position!.longitude)
        : const LatLng(-6.2, 106.8);

    return FlutterMap(
      mapController: _mapController,
      options: MapOptions(
        initialCenter: center,
        initialZoom: 17,
        onMapReady: () {
          _isMapReady = true;
          if (_position != null) {
            _mapController.move(
              LatLng(_position!.latitude, _position!.longitude),
              17,
            );
          }
        },
      ),
      children: [
        TileLayer(
          urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
          userAgentPackageName: 'com.patrolisiap86.app',
        ),
        if (_position != null)
          MarkerLayer(
            markers: [
              Marker(
                point: LatLng(_position!.latitude, _position!.longitude),
                width: 40,
                height: 40,
                child: const Icon(
                  Icons.location_pin,
                  color: Colors.red,
                  size: 40,
                ),
              ),
            ],
          ),
      ],
    );
  }

  Widget _buildCoordinateInfo() {
    if (_position == null) {
      return Row(
        children: [
          const SizedBox(
            width: 14,
            height: 14,
            child: CircularProgressIndicator(strokeWidth: 2),
          ),
          const SizedBox(width: 8),
          Text(
            'Menunggu GPS...',
            style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
          ),
        ],
      );
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: const Color(0xFF0D2B5E).withValues(alpha: 0.06),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        children: [
          const Icon(Icons.gps_fixed, size: 14, color: Color(0xFF0D2B5E)),
          const SizedBox(width: 6),
          Expanded(
            child: Text(
              '${_position!.latitude.toStringAsFixed(6)}, '
              '${_position!.longitude.toStringAsFixed(6)}',
              style: const TextStyle(
                fontSize: 12,
                fontFamily: 'monospace',
                color: Color(0xFF0D2B5E),
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
          Text(
            '±${_position!.accuracy.toStringAsFixed(0)}m',
            style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
          ),
        ],
      ),
    );
  }

  // ── Kartu Foto ───────────────────────────────────────────────────────────────

  Widget _buildPhotoCard() {
    return _card(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _cardHeader(Icons.photo_camera_outlined, 'Foto', required: true),
          const SizedBox(height: 12),
          if (_imageBytes == null)
            GestureDetector(
              onTap: _takePhoto,
              child: Container(
                height: 160,
                decoration: BoxDecoration(
                  color: const Color(0xFFE8EDF5),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(
                    color: const Color(0xFF0D2B5E).withValues(alpha: 0.2),
                    style: BorderStyle.solid,
                  ),
                ),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(
                      Icons.add_a_photo_outlined,
                      size: 40,
                      color: Colors.grey.shade400,
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Tap untuk ambil foto',
                      style: TextStyle(
                          fontSize: 13, color: Colors.grey.shade500),
                    ),
                  ],
                ),
              ),
            )
          else
            Stack(
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(10),
                  child: Image.memory(
                    _imageBytes!,
                    width: double.infinity,
                    height: 220,
                    fit: BoxFit.cover,
                  ),
                ),
                Positioned(
                  top: 8,
                  right: 8,
                  child: GestureDetector(
                    onTap: _takePhoto,
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 10, vertical: 6),
                      decoration: BoxDecoration(
                        color: Colors.black.withValues(alpha: 0.6),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.refresh,
                              color: Colors.white, size: 14),
                          SizedBox(width: 4),
                          Text(
                            'Ambil Ulang',
                            style: TextStyle(
                                color: Colors.white, fontSize: 12),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
        ],
      ),
    );
  }

  // ── Kartu Keterangan ─────────────────────────────────────────────────────────

  Widget _buildKeteranganCard() {
    return _card(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _cardHeader(Icons.notes_outlined, 'Keterangan', required: false),
          const SizedBox(height: 10),
          TextField(
            controller: _keteranganCtrl,
            maxLines: 3,
            maxLength: 255,
            decoration: InputDecoration(
              hintText: 'Isi keterangan jika ada...',
              hintStyle:
                  TextStyle(color: Colors.grey.shade400, fontSize: 13),
              filled: true,
              fillColor: const Color(0xFFF8FAFE),
              contentPadding: const EdgeInsets.all(12),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
                borderSide: BorderSide(
                    color: Colors.grey.shade200),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
                borderSide: BorderSide(color: Colors.grey.shade200),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(8),
                borderSide:
                    const BorderSide(color: Color(0xFF0D2B5E)),
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ── Tombol Submit ────────────────────────────────────────────────────────────

  Widget _buildSubmitButton(bool canSubmit) {
    return SizedBox(
      height: 50,
      child: ElevatedButton(
        onPressed: canSubmit ? _submit : null,
        style: ElevatedButton.styleFrom(
          backgroundColor: const Color(0xFF0D2B5E),
          disabledBackgroundColor: Colors.grey.shade300,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
        child: _isSubmitting
            ? const SizedBox(
                width: 22,
                height: 22,
                child: CircularProgressIndicator(
                  color: Colors.white,
                  strokeWidth: 2.5,
                ),
              )
            : const Text(
                'Simpan Patroli',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 15,
                  fontWeight: FontWeight.bold,
                ),
              ),
      ),
    );
  }

  // ── Helper Widgets ───────────────────────────────────────────────────────────

  Widget _card({required Widget child}) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.06),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: child,
    );
  }

  Widget _cardHeader(IconData icon, String title, {required bool required}) {
    return Row(
      children: [
        Icon(icon, color: const Color(0xFF0D2B5E), size: 18),
        const SizedBox(width: 6),
        Text(
          title,
          style: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.bold,
            color: Color(0xFF0D2B5E),
          ),
        ),
        if (required) ...[
          const SizedBox(width: 4),
          const Text(
            '*',
            style: TextStyle(
              color: Colors.red,
              fontSize: 14,
              fontWeight: FontWeight.bold,
            ),
          ),
        ],
      ],
    );
  }
}