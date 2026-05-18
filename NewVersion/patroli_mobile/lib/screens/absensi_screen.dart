import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:google_mlkit_face_detection/google_mlkit_face_detection.dart';
import 'package:image/image.dart' as img;
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import 'package:patroli_mobile/models/LocalDatabaseHelper.dart';
import 'package:patroli_mobile/providers/absensi_provider.dart';
import 'package:patroli_mobile/providers/auth_provider.dart';
import 'package:patroli_mobile/services/absensi_service.dart';
import 'package:patroli_mobile/services/face_detector_service.dart';
import 'package:patroli_mobile/services/face_recognition_service.dart';

class AbsensiScreen extends StatefulWidget {
  /// "in" → langsung buka kamera checkin, "out" → checkout, null → manual
  final String? autoScan;
  const AbsensiScreen({super.key, this.autoScan});

  @override
  State<AbsensiScreen> createState() => _AbsensiScreenState();
}

class _AbsensiScreenState extends State<AbsensiScreen> {
  final FaceDetectorService _faceDetectorService = FaceDetectorService();
  final FaceRecognitionService _faceRecognitionService = FaceRecognitionService();
  final AbsensiService _absensiService = AbsensiService();
  final ImagePicker _picker = ImagePicker();

  late LocalDatabaseHelper _dbHelper;
  static const int _embeddingVersion = 3;

  String _timeString = "";
  String _dateString = "";
  Timer? _timer;
  Position? _currentPosition;
  Uint8List? _securityImageBytes;
  bool _isSDKInitialized = false;
  bool _autoScanTriggered = false;
  String? _capturedImageBitmap;

  // Data dari provider — dibaca di initState, disimpan lokal agar tidak rebuild terus
  String _token = "";
  String _username = "";
  String _recordOwnerID = "";
  int _locationID = 0;
  String _fotoSecurity = "";
  String _namaUser = "";
  int _allowFaceRecognition = 0;

  @override
  void initState() {
    super.initState();
    _dbHelper = LocalDatabaseHelper(
      "user_embeddings",
      {"user_id": "TEXT PRIMARY KEY", "vector": "TEXT"},
    );

    WidgetsBinding.instance.addPostFrameCallback((_) {
      final auth = context.read<AuthProvider>();
      final me = auth.meUser;
      if (me == null || auth.token == null) return;

      _token = auth.token!;
      _username = me.username;
      _recordOwnerID = me.recordOwnerID;
      _locationID = me.lokasiPatroli?.id ?? 0;
      _fotoSecurity = me.fotoSecurity ?? '';
      _namaUser = me.namaUser;
      _allowFaceRecognition = me.allowFaceRecognition;

      _decodeSecurityImage();
      _initFaceSDK();
      _startTime();
      _getCurrentPosition();
    });
  }

  @override
  void dispose() {
    _timer?.cancel();
    _faceDetectorService.dispose();
    _faceRecognitionService.dispose();
    super.dispose();
  }

  // -------------------------------------------------------------------------
  // Waktu
  // -------------------------------------------------------------------------
  void _startTime() {
    _updateTime();
    _timer = Timer.periodic(const Duration(seconds: 1), (_) => _updateTime());
  }

  void _updateTime() {
    if (!mounted) return;
    final now = DateTime.now();
    setState(() {
      _timeString = DateFormat('HH:mm:ss').format(now);
      _dateString = DateFormat('EEEE, d MMMM yyyy').format(now);
    });
  }

  // -------------------------------------------------------------------------
  // Security Image
  // -------------------------------------------------------------------------
  void _decodeSecurityImage() {
    if (_fotoSecurity.isEmpty || _fotoSecurity == 'null') return;
    try {
      final cleaned = _fotoSecurity
          .replaceAll('data:image/jpeg;base64,', '')
          .replaceAll('data:image/png;base64,', '')
          .replaceAll('\n', '')
          .replaceAll(' ', '');
      setState(() => _securityImageBytes = base64Decode(cleaned));
    } catch (e) {
      debugPrint('Error decoding security image: $e');
    }
  }

  // -------------------------------------------------------------------------
  // Face SDK
  // -------------------------------------------------------------------------
  Future<void> _initFaceSDK() async {
    // Jika autoScan aktif, tampilkan loading selama model TFLite dimuat
    if (widget.autoScan != null) _showLoadingDialog("Menyiapkan sistem AI...");

    await _dbHelper.ensureTable("user_id TEXT PRIMARY KEY, vector TEXT");
    await _faceRecognitionService.init();
    await _ensureMasterEmbeddingRegistered();

    if (!mounted) return;
    setState(() => _isSDKInitialized = true);

    if (widget.autoScan != null && !_autoScanTriggered) {
      _autoScanTriggered = true;
      _popLoader(); // tutup loading "Menyiapkan sistem AI..."
      // addPostFrameCallback agar dialog sudah benar-benar ditutup sebelum scan
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (mounted) _handleScan(widget.autoScan!);
      });
    }

    debugPrint("AI Services Initialized.");
  }

  Future<void> _ensureMasterEmbeddingRegistered() async {
    try {
      final db = await _dbHelper.database;

      final versionRow = await db.query("user_embeddings",
          where: "user_id = ?", whereArgs: ["__algo_version__"]);
      final int storedVersion = versionRow.isNotEmpty
          ? int.tryParse(versionRow.first["vector"] as String) ?? 0
          : 0;

      if (storedVersion != _embeddingVersion) {
        debugPrint("Embedding version mismatch. Clearing old embeddings...");
        await db.delete("user_embeddings",
            where: "user_id = ?", whereArgs: [_username]);
        await db.delete("user_embeddings",
            where: "user_id = ?", whereArgs: ["__algo_version__"]);
      }

      final existing = await db.query("user_embeddings",
          where: "user_id = ?", whereArgs: [_username]);

      if (existing.isEmpty && _securityImageBytes != null) {
        debugPrint("Generating master embedding from security photo...");
        final embedding = await _generateEmbeddingFromBase64(_fotoSecurity);
        if (embedding != null) {
          await db.insert("user_embeddings",
              {"user_id": _username, "vector": jsonEncode(embedding)});
          await db.insert("user_embeddings",
              {"user_id": "__algo_version__", "vector": _embeddingVersion.toString()});
          debugPrint("Master embedding saved (v$_embeddingVersion).");
        }
      }
    } catch (e) {
      debugPrint("Error registering master embedding: $e");
    }
  }

  Future<List<double>?> _generateEmbeddingFromBase64(String base64Str) async {
    try {
      final cleaned = base64Str
          .replaceAll('data:image/jpeg;base64,', '')
          .replaceAll('data:image/png;base64,', '')
          .replaceAll('\n', '')
          .replaceAll(' ', '');
      final bytes = base64Decode(cleaned);

      final tempFile = File('${Directory.systemTemp.path}/master_face.jpg');
      await tempFile.writeAsBytes(bytes);

      final inputImage = InputImage.fromFilePath(tempFile.path);
      final faces = await _faceDetectorService.detectFaces(inputImage);
      if (faces.isEmpty) return null;

      return await _faceRecognitionService.getEmbedding(tempFile, faces.first);
    } catch (e) {
      debugPrint("Error in _generateEmbeddingFromBase64: $e");
      return null;
    }
  }

  // -------------------------------------------------------------------------
  // GPS
  // -------------------------------------------------------------------------
  Future<void> _getCurrentPosition() async {
    final serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) return;
    var permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
      if (permission == LocationPermission.denied) return;
    }
    _currentPosition =
        await Geolocator.getCurrentPosition(desiredAccuracy: LocationAccuracy.high);
  }

  Future<bool> _validateCheckinLocation() async {
    if (!mounted) return false;
    _showLoadingDialog("Memvalidasi Lokasi...");

    try {
      final serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        _popLoader();
        _showMsg("Peringatan", "Layanan lokasi tidak aktif. Aktifkan GPS terlebih dahulu.");
        return false;
      }

      var permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }
      if (permission == LocationPermission.denied ||
          permission == LocationPermission.deniedForever) {
        _popLoader();
        _showMsg("Peringatan", "Izin lokasi diperlukan untuk check in.");
        return false;
      }

      final position = await Geolocator.getCurrentPosition(
          desiredAccuracy: LocationAccuracy.high);

      if (position.isMocked) {
        _popLoader();
        _showMsg("Peringatan",
            "Terdeteksi penggunaan GPS palsu (Mock Location). Silakan nonaktifkan aplikasi mock location dan coba lagi.");
        return false;
      }

      _currentPosition = position;

      // Ambil data lokasi dari meUser.lokasiPatroli (sudah ada di provider, tanpa API call tambahan)
      final auth = context.read<AuthProvider>();
      final lokasi = auth.meUser?.lokasiPatroli;

      if (lokasi == null ||
          (lokasi.latitude ?? '').isEmpty ||
          (lokasi.longitude ?? '').isEmpty ||
          (lokasi.radius ?? '').isEmpty) {
        _popLoader();
        return true;
      }

      final targetLat = double.tryParse(lokasi.latitude!);
      final targetLng = double.tryParse(lokasi.longitude!);
      final allowedRadius = double.tryParse(lokasi.radius!);

      if (targetLat == null || targetLng == null || allowedRadius == null) {
        _popLoader();
        return true;
      }

      final distance = Geolocator.distanceBetween(
        position.latitude, position.longitude, targetLat, targetLng);

      _popLoader();

      if (distance > allowedRadius) {
        _showMsg("Diluar Area",
            "Anda berada terlalu jauh dengan ${lokasi.namaArea}, jarak anda ${distance.toStringAsFixed(0)} m dari perusahaan.");
        return false;
      }

      return true;
    } catch (e) {
      _popLoader();
      debugPrint("Error validating location: $e");
      return true;
    }
  }

  // -------------------------------------------------------------------------
  // Check In / Check Out
  // -------------------------------------------------------------------------
  Future<void> _handleScan(String type) async {
    if (type == "in") {
      final locationValid = await _validateCheckinLocation();
      if (!locationValid) return;
    }

    if (!_isSDKInitialized) {
      _showMsg("Informasi", "Sistem AI sedang bersiap. Silakan tunggu sebentar.");
      return;
    }

    final XFile? photo = await _picker.pickImage(
      source: ImageSource.camera,
      preferredCameraDevice: CameraDevice.front,
    );
    if (photo == null) return;

    _showLoadingDialog("Memproses Wajah...");

    try {
      final inputImage = InputImage.fromFilePath(photo.path);
      final faces = await _faceDetectorService.detectFaces(inputImage);

      if (faces.isEmpty) {
        _popLoader();
        _showMsg("Peringatan", "Wajah tidak terdeteksi. Silakan coba lagi.");
        return;
      }

      final imageFile = File(photo.path);
      final capturedEmbedding =
          await _faceRecognitionService.getEmbedding(imageFile, faces.first);

      if (capturedEmbedding == null) {
        _popLoader();
        _showMsg("Error", "Gagal memproses wajah.");
        return;
      }

      if (_allowFaceRecognition == 1) {
        final db = await _dbHelper.database;
        final results = await db.query("user_embeddings",
            where: "user_id = ?", whereArgs: [_username]);

        List<double> targetEmbedding;
        if (results.isEmpty) {
          if (_fotoSecurity.isEmpty || _fotoSecurity == 'null') {
            _popLoader();
            _showMsg("Peringatan", "Master wajah tidak ditemukan.");
            return;
          }
          final masterEmb = await _generateEmbeddingFromBase64(_fotoSecurity);
          if (masterEmb == null) {
            _popLoader();
            _showMsg("Error", "Gagal memproses master wajah.");
            return;
          }
          targetEmbedding = masterEmb;
          await db.insert("user_embeddings",
              {"user_id": _username, "vector": jsonEncode(targetEmbedding)});
        } else {
          targetEmbedding =
              List<double>.from(jsonDecode(results.first["vector"] as String));
        }

        final similarity =
            _faceRecognitionService.compare(capturedEmbedding, targetEmbedding);
        debugPrint("Face Similarity: $similarity");

        if (similarity > 0.65) {
          _capturedImageBitmap = await _compressImage(imageFile);
          await _saveAttendance(type, similarity: similarity);
        } else {
          _popLoader();
          _showMsg("Gagal",
              "Wajah tidak cocok (Similarity: ${(similarity * 100).toStringAsFixed(2)}%).");
        }
      } else {
        _capturedImageBitmap = await _compressImage(imageFile);
        await _saveAttendance(type);
      }
    } catch (e) {
      _popLoader();
      _showMsg("Error", "Terjadi kesalahan: $e");
    }
  }

  Future<String?> _compressImage(File file) async {
    try {
      final bytes = await file.readAsBytes();
      final image = img.decodeImage(bytes);
      if (image == null) return null;
      final resized = img.copyResize(image, width: 480);
      return base64Encode(img.encodeJpg(resized, quality: 50));
    } catch (e) {
      debugPrint("Error compressing image: $e");
      return null;
    }
  }

  Future<void> _saveAttendance(String mode, {double? similarity}) async {
    final absensiProv = context.read<AbsensiProvider>();
    final threshold = similarity != null
        ? (similarity * 100).toStringAsFixed(2)
        : "0";

    try {
      Map<String, dynamic> params;
      if (mode == "in") {
        params = {
          "LocationID": _locationID,
          "KoordinatIN": _currentPosition == null
              ? ""
              : "${_currentPosition!.latitude},${_currentPosition!.longitude}",
          "ImageIN": _capturedImageBitmap ?? "",
          "Shift": "",
          "TresholdIn": threshold,
          "AppVersion": "2.0.0",
        };
      } else {
        params = {
          "KoordinatOUT": _currentPosition == null
              ? ""
              : "${_currentPosition!.latitude},${_currentPosition!.longitude}",
          "ImageOUT": _capturedImageBitmap ?? "",
          "TresholdOut": threshold,
        };
      }

      final result = mode == "in"
          ? await _absensiService.checkin(_token, params)
          : await _absensiService.checkout(_token, params);

      _popLoader();

      if (result['success'] == true) {
        await absensiProv.fetchToday(_token);
        if (mounted) {
          await showDialog(
            context: context,
            builder: (_) => AlertDialog(
              title: const Text("Berhasil"),
              content: Text(mode == "in" ? "Berhasil Check In" : "Berhasil Check Out"),
              actions: [
                TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text("OK"),
                ),
              ],
            ),
          );
        }
      } else {
        _showMsg("Gagal", result['message']?.toString() ?? "Terjadi kesalahan.");
      }
    } catch (e) {
      _popLoader();
      _showMsg("Error", "Terjadi kesalahan: $e");
    }
  }

  // -------------------------------------------------------------------------
  // Dialog helpers
  // -------------------------------------------------------------------------
  void _showLoadingDialog(String info) {
    if (!mounted) return;
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => PopScope(
        canPop: false,
        child: AlertDialog(
          content: Row(
            children: [
              const CircularProgressIndicator(),
              const SizedBox(width: 16),
              Expanded(child: Text(info)),
            ],
          ),
        ),
      ),
    );
  }

  void _popLoader() {
    if (mounted) Navigator.of(context, rootNavigator: true).pop();
  }

  void _showMsg(String title, String message) {
    if (!mounted) return;
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: Text(title),
        content: Text(message),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(context), child: const Text("OK")),
        ],
      ),
    );
  }

  // -------------------------------------------------------------------------
  // Build
  // -------------------------------------------------------------------------
  @override
  Widget build(BuildContext context) {
    const Color primaryColor = Color(0xFF1A237E);
    const Color accentColor = Color(0xFF0D2B5E);

    final absensi = context.watch<AbsensiProvider>();

    return Scaffold(
      backgroundColor: Colors.grey[100],
      appBar: AppBar(
        title: const Text(
          "Daily Attendance",
          style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white),
        ),
        backgroundColor: primaryColor,
        iconTheme: const IconThemeData(color: Colors.white),
        elevation: 0,
        centerTitle: true,
      ),
      body: SingleChildScrollView(
        child: Column(
          children: [
            // Header Profile
            Container(
              width: double.infinity,
              decoration: const BoxDecoration(
                color: primaryColor,
                borderRadius: BorderRadius.only(
                  bottomLeft: Radius.circular(30),
                  bottomRight: Radius.circular(30),
                ),
              ),
              padding: const EdgeInsets.fromLTRB(24, 8, 24, 24),
              child: Column(
                children: [
                  CircleAvatar(
                    radius: 50,
                    backgroundColor: Colors.white24,
                    backgroundImage: _securityImageBytes != null
                        ? MemoryImage(_securityImageBytes!)
                        : null,
                    child: _securityImageBytes == null
                        ? const Icon(Icons.person, size: 50, color: Colors.white)
                        : null,
                  ),
                  const SizedBox(height: 12),
                  Text(
                    _namaUser,
                    style: const TextStyle(
                        color: Colors.white,
                        fontSize: 20,
                        fontWeight: FontWeight.bold),
                  ),
                  Text(
                    "NIK: $_username",
                    style: const TextStyle(color: Colors.white70, fontSize: 14),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 24),

            // Jam Real-time
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 24),
              child: Column(
                children: [
                  Text(
                    _timeString,
                    style: const TextStyle(
                        fontSize: 48,
                        fontWeight: FontWeight.bold,
                        color: primaryColor),
                  ),
                  Text(
                    _dateString,
                    style: TextStyle(fontSize: 16, color: Colors.grey[600]),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 32),

            // Status Cards
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 24),
              child: Row(
                children: [
                  _buildStatusCard(
                    title: "Check In",
                    time: absensi.checkinTime,
                    icon: Icons.login,
                    color: Colors.green,
                  ),
                  const SizedBox(width: 16),
                  _buildStatusCard(
                    title: "Check Out",
                    time: absensi.checkoutTime,
                    icon: Icons.logout,
                    color: Colors.red,
                  ),
                ],
              ),
            ),

            const SizedBox(height: 40),

            // Tombol Aksi
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 24),
              child: Column(
                children: [
                  _buildActionButton(
                    label: "CHECK IN NOW",
                    icon: Icons.camera_front,
                    color: Colors.green[600]!,
                    onPressed: () => _handleScan("in"),
                    enabled: !absensi.sudahCheckin,
                  ),
                  const SizedBox(height: 16),
                  _buildActionButton(
                    label: "CHECK OUT NOW",
                    icon: Icons.camera_front,
                    color: Colors.red[600]!,
                    onPressed: () => _handleScan("out"),
                    enabled: absensi.sudahCheckin && !absensi.sudahCheckout,
                  ),
                ],
              ),
            ),

            const SizedBox(height: 30),
          ],
        ),
      ),
    );
  }

  Widget _buildStatusCard({
    required String title,
    required String time,
    required IconData icon,
    required Color color,
  }) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
                color: Colors.black.withValues(alpha: 0.05),
                blurRadius: 10,
                offset: const Offset(0, 4)),
          ],
        ),
        child: Column(
          children: [
            Icon(icon, color: color, size: 28),
            const SizedBox(height: 8),
            Text(title,
                style: TextStyle(color: Colors.grey[600], fontSize: 13)),
            const SizedBox(height: 4),
            Text(time,
                style: const TextStyle(
                    fontWeight: FontWeight.bold, fontSize: 18)),
          ],
        ),
      ),
    );
  }

  Widget _buildActionButton({
    required String label,
    required IconData icon,
    required Color color,
    required VoidCallback onPressed,
    bool enabled = true,
  }) {
    return SizedBox(
      width: double.infinity,
      height: 55,
      child: ElevatedButton.icon(
        onPressed: enabled ? onPressed : null,
        icon: Icon(icon, color: Colors.white),
        label: Text(label,
            style: const TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: Colors.white)),
        style: ElevatedButton.styleFrom(
          backgroundColor: enabled ? color : Colors.grey[400],
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          elevation: enabled ? 4 : 0,
        ),
      ),
    );
  }
}