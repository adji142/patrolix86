import 'dart:convert';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:geolocator/geolocator.dart';
import 'package:google_mlkit_face_detection/google_mlkit_face_detection.dart';
import 'package:image/image.dart' as img;
import 'package:image_picker/image_picker.dart';
import 'package:patroli_mobile/config/app_config.dart';
import 'package:provider/provider.dart';

import 'package:patroli_mobile/models/LocalDatabaseHelper.dart';
import 'package:patroli_mobile/models/patroli_progress_model.dart';
import 'package:patroli_mobile/models/user_me_model.dart';
import 'package:patroli_mobile/providers/absensi_provider.dart';
import 'package:patroli_mobile/providers/auth_provider.dart';
import 'package:patroli_mobile/services/absensi_service.dart';
import 'package:patroli_mobile/services/face_detector_service.dart';
import 'package:patroli_mobile/services/face_recognition_service.dart';
import 'package:patroli_mobile/services/patroli_service.dart';
import 'package:patroli_mobile/screens/patroli_history_screen.dart';
import 'package:patroli_mobile/screens/patroli_scan_screen.dart';
import 'package:patroli_mobile/screens/pengajuan_izin_screen.dart';
import 'package:patroli_mobile/screens/pengajuan_cuti_screen.dart';
import 'package:patroli_mobile/screens/tukar_jadwal_screen.dart';
import 'package:patroli_mobile/screens/daily_activity_screen.dart';
import 'package:patroli_mobile/screens/guest_log_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  // ── Patroli ───────────────────────────────────────────────────────────────
  final PatroliService _patroliService = PatroliService();
  PatroliProgressModel? _progress;
  bool _isLoadingProgress = false;

  // ── Statistik Absensi Bulanan ─────────────────────────────────────────────
  Map<String, dynamic>? _monthlyStats;
  bool _isLoadingStats = false;

  // ── Absensi SDK ───────────────────────────────────────────────────────────
  final FaceDetectorService _faceDetectorService = FaceDetectorService();
  final FaceRecognitionService _faceRecognitionService = FaceRecognitionService();
  final AbsensiService _absensiService = AbsensiService();
  final ImagePicker _picker = ImagePicker();
  late LocalDatabaseHelper _dbHelper;
  static const int _embeddingVersion = 3;
  bool _isSDKInitialized = false;
  bool _sdkInitializing = false;

  @override
  void initState() {
    super.initState();
    _dbHelper = LocalDatabaseHelper(
      "user_embeddings",
      {"user_id": "TEXT PRIMARY KEY", "vector": "TEXT"},
    );
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final auth = context.read<AuthProvider>();
      auth.refreshMe();
      _fetchProgress();
      _fetchMonthlyStats();
      if (auth.token != null) {
        context.read<AbsensiProvider>().fetchToday(auth.token!);
      }
    });
  }

  @override
  void dispose() {
    _faceDetectorService.dispose();
    _faceRecognitionService.dispose();
    super.dispose();
  }

  // ── Patroli ───────────────────────────────────────────────────────────────
  Future<void> _fetchProgress() async {
    final token = context.read<AuthProvider>().token;
    if (token == null) return;
    setState(() => _isLoadingProgress = true);
    try {
      final progress = await _patroliService.fetchProgress(token);
      setState(() => _progress = progress);
    } catch (_) {
    } finally {
      setState(() => _isLoadingProgress = false);
    }
  }

  // ── Statistik Absensi Bulanan ─────────────────────────────────────────────
  Future<void> _fetchMonthlyStats() async {
    final token = context.read<AuthProvider>().token;
    if (token == null) return;
    setState(() => _isLoadingStats = true);
    try {
      final result = await _absensiService.fetchMonthlyStats(token);
      if (result['success'] == true) {
        setState(() => _monthlyStats = result['data'] as Map<String, dynamic>?);
      }
    } catch (_) {
    } finally {
      setState(() => _isLoadingStats = false);
    }
  }

  void _openHistory() {
    final token = context.read<AuthProvider>().token;
    if (token == null) return;
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => PatroliHistoryScreen(token: token)),
    );
  }

  // ── Absensi: inisialisasi SDK (lazy, pertama kali dipakai) ───────────────
  Future<bool> _ensureSDK() async {
    if (_isSDKInitialized) return true;
    if (_sdkInitializing) return false;
    _sdkInitializing = true;
    _showLoadingDialog("Menyiapkan sistem AI...");
    try {
      await _dbHelper.ensureTable("user_id TEXT PRIMARY KEY, vector TEXT");
      await _faceRecognitionService.init();
      await _ensureMasterEmbedding();
      if (mounted) setState(() => _isSDKInitialized = true);
      return true;
    } catch (e) {
      debugPrint("SDK init error: $e");
      return false;
    } finally {
      _sdkInitializing = false;
      _popLoader();
    }
  }

  Future<void> _ensureMasterEmbedding() async {
    final me = context.read<AuthProvider>().meUser;
    if (me == null) return;
    final username = me.username;
    final fotoSecurity = me.fotoSecurity ?? '';
    try {
      final db = await _dbHelper.database;
      final versionRow = await db.query(
        "user_embeddings",
        where: "user_id = ?",
        whereArgs: ["__algo_version__"],
      );
      final storedVersion = versionRow.isNotEmpty
          ? int.tryParse(versionRow.first["vector"] as String) ?? 0
          : 0;

      if (storedVersion != _embeddingVersion) {
        await db.delete("user_embeddings", where: "user_id = ?", whereArgs: [username]);
        await db.delete("user_embeddings", where: "user_id = ?", whereArgs: ["__algo_version__"]);
      }

      final existing = await db.query(
        "user_embeddings",
        where: "user_id = ?",
        whereArgs: [username],
      );
      if (existing.isEmpty && fotoSecurity.isNotEmpty && fotoSecurity != 'null') {
        final emb = await _generateEmbeddingFromBase64(fotoSecurity);
        if (emb != null) {
          await db.insert("user_embeddings", {"user_id": username, "vector": jsonEncode(emb)});
          await db.insert("user_embeddings", {"user_id": "__algo_version__", "vector": _embeddingVersion.toString()});
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
      final faces = await _faceDetectorService.detectFaces(InputImage.fromFilePath(tempFile.path));
      if (faces.isEmpty) return null;
      return await _faceRecognitionService.getEmbedding(tempFile, faces.first);
    } catch (e) {
      debugPrint("Error in _generateEmbeddingFromBase64: $e");
      return null;
    }
  }

  // ── Absensi: validasi lokasi ──────────────────────────────────────────────
  Future<bool> _validateLocation() async {
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
          locationSettings:
              const LocationSettings(accuracy: LocationAccuracy.high));
      if (position.isMocked) {
        _popLoader();
        _showMsg("Peringatan",
            "Terdeteksi GPS palsu (Mock Location). Nonaktifkan aplikasi mock location dan coba lagi.");
        return false;
      }

      if (!mounted) { _popLoader(); return false; }
      final lokasi = context.read<AuthProvider>().meUser?.lokasiPatroli;
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
            "Anda berada terlalu jauh dari ${lokasi.namaArea}, jarak anda ${distance.toStringAsFixed(0)} m.");
        return false;
      }
      return true;
    } catch (e) {
      _popLoader();
      return true;
    }
  }

  // ── Absensi: scan wajah utama ─────────────────────────────────────────────
  Future<void> _handleScan(String type) async {
    if (!mounted) return;

    final auth = context.read<AuthProvider>();
    final me = auth.meUser;
    final allowFace = me?.allowFaceRecognition ?? 0;
    final fotoSecurity = me?.fotoSecurity ?? '';

    // Rule 3: Blokir absen masuk jika AllowJadwalKerja=1 tapi jadwal belum dibuat
    if (type == "in") {
      final allowJadwal = me?.lokasiPatroli?.allowJadwalKerja ?? 0;
      if (allowJadwal == 1 && me?.activeShiftDetails == null) {
        _showMsg(
          "Absensi Ditolak",
          "Jadwal Belum dibuat. Silakan hubungi admin untuk mengatur jadwal kerja Anda.",
        );
        return;
      }
    }

    if (allowFace == 1) {
      if (fotoSecurity.isEmpty || fotoSecurity == 'null' || fotoSecurity == 'person.png') {
        _showMsg(
          "Absensi Ditolak",
          "Absensi ditolak. Anda belum mendaftarkan foto wajah (Foto Security kosong) untuk verifikasi wajah.",
        );
        return;
      }
    }

    // Inisialisasi SDK jika belum
    final sdkReady = await _ensureSDK();
    if (!sdkReady || !mounted) return;

    // Validasi lokasi hanya saat check in
    if (type == "in") {
      final valid = await _validateLocation();
      if (!valid || !mounted) return;
    }

    // Buka kamera
    final XFile? photo = await _picker.pickImage(
      source: ImageSource.camera,
      preferredCameraDevice: CameraDevice.front,
    );
    if (photo == null || !mounted) return;

    _showLoadingDialog("Memproses Wajah...");
    try {
      final auth = context.read<AuthProvider>();
      final absensiProv = context.read<AbsensiProvider>();
      final me = auth.meUser;
      final token = auth.token ?? '';
      final username = me?.username ?? '';
      final fotoSecurity = me?.fotoSecurity ?? '';
      final allowFace = me?.allowFaceRecognition ?? 0;
      final locationID = me?.lokasiPatroli?.id ?? 0;

      final faces = await _faceDetectorService
          .detectFaces(InputImage.fromFilePath(photo.path));
      if (faces.isEmpty) {
        _popLoader();
        _showMsg("Peringatan", "Wajah tidak terdeteksi. Silakan coba lagi.");
        return;
      }

      final imageFile = File(photo.path);
      final capturedEmb =
          await _faceRecognitionService.getEmbedding(imageFile, faces.first);
      if (capturedEmb == null) {
        _popLoader();
        _showMsg("Error", "Gagal memproses wajah.");
        return;
      }

      double? similarity;
      if (allowFace == 1) {
        final db = await _dbHelper.database;
        final rows = await db.query(
          "user_embeddings",
          where: "user_id = ?",
          whereArgs: [username],
        );
        List<double> targetEmb;
        if (rows.isEmpty) {
          if (fotoSecurity.isEmpty || fotoSecurity == 'null') {
            _popLoader();
            _showMsg("Peringatan", "Master wajah tidak ditemukan.");
            return;
          }
          final masterEmb = await _generateEmbeddingFromBase64(fotoSecurity);
          if (masterEmb == null) {
            _popLoader();
            _showMsg("Error", "Gagal memproses master wajah.");
            return;
          }
          targetEmb = masterEmb;
          await db.insert("user_embeddings",
              {"user_id": username, "vector": jsonEncode(targetEmb)});
        } else {
          targetEmb = List<double>.from(jsonDecode(rows.first["vector"] as String));
        }
        similarity = _faceRecognitionService.compare(capturedEmb, targetEmb);
        debugPrint("Face Similarity: $similarity");
        if (similarity <= 0.65) {
          _popLoader();
          _showMsg("Gagal",
              "Wajah tidak cocok (${(similarity * 100).toStringAsFixed(2)}%).");
          return;
        }
      }

      final imageBitmap = await _compressImage(imageFile);

      Position? pos;
      try {
        pos = await Geolocator.getCurrentPosition(
            locationSettings:
                const LocationSettings(accuracy: LocationAccuracy.medium));
      } catch (_) {}
      final koordinat = pos == null ? "" : "${pos.latitude},${pos.longitude}";
      final threshold = similarity != null
          ? (similarity * 100).toStringAsFixed(2)
          : "0";
      final now = DateTime.now();
      final tanggal = DateFormat('yyyy-MM-dd').format(now);
      final waktu = DateFormat('yyyy-MM-dd HH:mm:ss').format(now);

      final params = type == "in"
          ? {
              "LocationID": locationID,
              "Tanggal": tanggal,
              "Checkin": waktu,
              "KoordinatIN": koordinat,
              "ImageIN": imageBitmap ?? "",
              "Shift": "",
              "TresholdIn": threshold,
              "AppVersion": AppConfig.AppVersion,
            }
          : {
              "CheckOut": waktu,
              "KoordinatOUT": koordinat,
              "ImageOUT": imageBitmap ?? "",
              "TresholdOut": threshold,
            };

      final result = type == "in"
          ? await _absensiService.checkin(token, params)
          : await _absensiService.checkout(token, params);

      _popLoader();

      if (result['success'] == true) {
        await absensiProv.fetchToday(token);
        if (mounted) {
          await showDialog(
            context: context,
            builder: (_) => AlertDialog(
              title: const Text("Berhasil"),
              content:
                  Text(type == "in" ? "Berhasil Check In" : "Berhasil Check Out"),
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

  // ── Dialog helpers ────────────────────────────────────────────────────────
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
            onPressed: () => Navigator.pop(context),
            child: const Text("OK"),
          ),
        ],
      ),
    );
  }

  // ── Menu Absensi ──────────────────────────────────────────────────────────
  void _showAbsensiMenu(BuildContext context) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (sheetCtx) {
        final items = <Map<String, dynamic>>[
          {
            'icon': Icons.login,
            'label': 'Absen Masuk',
            'onTap': () {
              Navigator.pop(sheetCtx);
              _handleScan('in');
            },
          },
          {
            'icon': Icons.logout,
            'label': 'Absen Keluar',
            'onTap': () {
              Navigator.pop(sheetCtx);
              _handleScan('out');
            },
          },
          {
            'icon': Icons.description_outlined,
            'label': 'Pengajuan Izin',
            'onTap': () {
              Navigator.pop(sheetCtx);
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const PengajuanIzinScreen()),
              );
            },
          },
          {
            'icon': Icons.beach_access_outlined,
            'label': 'Pengajuan Cuti',
            'onTap': () {
              Navigator.pop(sheetCtx);
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const PengajuanCutiScreen()),
              );
            },
          },
          {
            'icon': Icons.swap_horiz,
            'label': 'Tukar Jadwal',
            'onTap': () {
              Navigator.pop(sheetCtx);
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const TukarJadwalScreen()),
              );
            }
          },
        ];

        return Padding(
          padding: const EdgeInsets.fromLTRB(16, 20, 16, 32),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: const Color(0xFF0D2B5E).withValues(alpha: 0.08),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.fingerprint,
                        color: Color(0xFF0D2B5E), size: 22),
                  ),
                  const SizedBox(width: 12),
                  const Text(
                    'Absensi',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF0D2B5E),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              const Divider(),
              ...items.map(
                (item) => ListTile(
                  leading: Icon(item['icon'] as IconData,
                      color: const Color(0xFF0D2B5E), size: 22),
                  title: Text(item['label'] as String,
                      style: const TextStyle(fontSize: 14)),
                  trailing: const Icon(Icons.chevron_right,
                      color: Colors.grey, size: 20),
                  onTap: (item['onTap'] as VoidCallback?) ??
                      () => Navigator.pop(sheetCtx),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  // ── Build ─────────────────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final me = auth.meUser;

    return Scaffold(
      backgroundColor: const Color(0xFFF0F4FA),
      body: Stack(
        children: [
          CustomScrollView(
            slivers: [
              SliverAppBar(
                expandedHeight: 180,
                pinned: true,
                backgroundColor: const Color(0xFF0D2B5E),
                foregroundColor: Colors.white,
                title: const Text(
                  'Amman Patroli',
                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
                ),
                actions: [
                  IconButton(
                    icon: const Icon(Icons.logout),
                    tooltip: 'Logout',
                    onPressed: () async {
                      await context.read<AuthProvider>().logout();
                      if (context.mounted) {
                        Navigator.pushReplacementNamed(context, '/login');
                      }
                    },
                  ),
                ],
                flexibleSpace: FlexibleSpaceBar(
                  background: Container(
                    decoration: const BoxDecoration(
                      gradient: LinearGradient(
                        colors: [Color(0xFF0D2B5E), Color(0xFF1A4A9E)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                    ),
                    child: SafeArea(
                      child: Padding(
                        padding: const EdgeInsets.fromLTRB(16, 56, 16, 16),
                        child: _buildUserInfo(me),
                      ),
                    ),
                  ),
                ),
              ),
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    children: [
                      _buildMonthlyStats(),
                      const SizedBox(height: 14),
                      _buildPatrolProgress(),
                      const SizedBox(height: 14),
                      _buildAbsensiAlert(),
                      const SizedBox(height: 14),
                      _buildMenuGrid(context),
                      const SizedBox(height: 24),
                    ],
                  ),
                ),
              ),
            ],
          ),
          if (auth.isRefreshing)
            Container(
              color: Colors.black.withValues(alpha: 0.35),
              child: const Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    CircularProgressIndicator(color: Colors.white),
                    SizedBox(height: 14),
                    Text('Memuat data...',
                        style: TextStyle(color: Colors.white, fontSize: 14)),
                  ],
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildBase64Image(String base64Str) {
    try {
      final cleaned =
          base64Str.contains(',') ? base64Str.split(',').last : base64Str;
      return Image.memory(base64Decode(cleaned), fit: BoxFit.cover);
    } catch (_) {
      return const Icon(Icons.person, color: Colors.white, size: 36);
    }
  }

  Widget _buildUserInfo(UserMeModel? me) {
    return Row(
      children: [
        Container(
          width: 68,
          height: 68,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            color: Colors.white.withValues(alpha: 0.2),
            border: Border.all(color: Colors.white, width: 2),
          ),
          child: me?.fotoSecurity != null
              ? ClipOval(child: _buildBase64Image(me!.fotoSecurity!))
              : const Icon(Icons.person, color: Colors.white, size: 36),
        ),
        const SizedBox(width: 14),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(
                me?.namaUser ?? '-',
                style: const TextStyle(
                    color: Colors.white,
                    fontSize: 16,
                    fontWeight: FontWeight.bold),
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
              if (me?.lokasiPatroli?.namaArea != null) ...[
                const SizedBox(height: 2),
                Text(
                  me!.lokasiPatroli!.namaArea,
                  style: TextStyle(
                      color: Colors.white.withValues(alpha: 0.8), fontSize: 12),
                ),
              ],
              if (me?.role != null) ...[
                const SizedBox(height: 6),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    me!.role!.rolename,
                    style: const TextStyle(
                        color: Colors.white,
                        fontSize: 11,
                        fontWeight: FontWeight.w500),
                  ),
                ),
              ],
              // ── Shift Display (3 Rules) ─────────────────────────────────
              Builder(builder: (context) {
                final allowJadwal = me?.lokasiPatroli?.allowJadwalKerja ?? 0;
                final shiftDetails = me?.activeShiftDetails;

                if (allowJadwal == 1) {
                  // Rule 1: AllowJadwalKerja=1 → tampilkan shift dari jadwalkerja
                  if (shiftDetails != null) {
                    return Padding(
                      padding: const EdgeInsets.only(top: 6),
                      child: Row(
                        children: [
                          const Icon(Icons.access_time,
                              color: Colors.white70, size: 14),
                          const SizedBox(width: 4),
                          Flexible(
                            child: Text(
                              '${shiftDetails.namaShift}: '
                              '${shiftDetails.mulaiBekerja.substring(0, 5)} - '
                              '${shiftDetails.selesaiBekerja.substring(0, 5)}',
                              style: TextStyle(
                                  color: Colors.white.withValues(alpha: 0.9),
                                  fontSize: 11),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    );
                  } else {
                    // Rule 1b: jadwal belum di-setting → peringatan merah
                    return Padding(
                      padding: const EdgeInsets.only(top: 6),
                      child: Row(
                        children: [
                          const Icon(Icons.warning_amber_rounded,
                              color: Color(0xFFFF6B6B), size: 14),
                          const SizedBox(width: 4),
                          const Text(
                            'Jadwal Belum di Setting',
                            style: TextStyle(
                                color: Color(0xFFFF6B6B),
                                fontSize: 11,
                                fontWeight: FontWeight.w600),
                          ),
                        ],
                      ),
                    );
                  }
                } else {
                  // Rule 2: AllowJadwalKerja=0 → tampilkan shift realtime
                  if (shiftDetails != null) {
                    return Padding(
                      padding: const EdgeInsets.only(top: 6),
                      child: Row(
                        children: [
                          const Icon(Icons.access_time,
                              color: Colors.white70, size: 14),
                          const SizedBox(width: 4),
                          Flexible(
                            child: Text(
                              '${shiftDetails.namaShift}: '
                              '${shiftDetails.mulaiBekerja.substring(0, 5)} - '
                              '${shiftDetails.selesaiBekerja.substring(0, 5)}',
                              style: TextStyle(
                                  color: Colors.white.withValues(alpha: 0.9),
                                  fontSize: 11),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    );
                  }
                  return const SizedBox.shrink();
                }
              }),
            ],
          ),
        ),
      ],
    );
  }

  // ── Statistik Absensi Bulanan ─────────────────────────────────────────────
  Widget _buildMonthlyStats() {
    final now = DateTime.now();
    final bulanNama = [
      '', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
      'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'
    ][now.month];

    final stats = _monthlyStats;
    final jumlahAbsensi  = stats?['jumlah_absensi']  ?? 0;
    final jumlahIzin     = stats?['jumlah_izin']     ?? 0;
    final jumlahCuti     = stats?['jumlah_cuti']     ?? 0;
    final jumlahOff      = stats?['jumlah_off']      ?? 0;
    final totalHariKerja = stats?['total_hari_kerja'] ?? 0;

    final items = [
      {
        'label': 'Hadir',
        'value': jumlahAbsensi,
        'icon': Icons.check_circle_outline,
        'color': const Color(0xFF1B8B4B),
        'bg': const Color(0xFFEAF8F0),
      },
      {
        'label': 'Izin',
        'value': jumlahIzin,
        'icon': Icons.description_outlined,
        'color': const Color(0xFF1A56DB),
        'bg': const Color(0xFFEBF2FF),
      },
      {
        'label': 'Cuti',
        'value': jumlahCuti,
        'icon': Icons.beach_access_outlined,
        'color': const Color(0xFF9333EA),
        'bg': const Color(0xFFF5EDFF),
      },
      {
        'label': 'Tidak Absen',
        'value': jumlahOff,
        'icon': Icons.highlight_off_outlined,
        'color': const Color(0xFFDC2626),
        'bg': const Color(0xFFFEF2F2),
      },
    ];

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
              color: Colors.black.withValues(alpha: 0.06),
              blurRadius: 8,
              offset: const Offset(0, 2)),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Row(
                children: [
                  Icon(Icons.bar_chart_rounded,
                      color: Color(0xFF0D2B5E), size: 20),
                  SizedBox(width: 6),
                  Text(
                    'Statistik Absensi',
                    style: TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF0D2B5E)),
                  ),
                ],
              ),
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: const Color(0xFF0D2B5E).withValues(alpha: 0.08),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  '$bulanNama ${now.year}',
                  style: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                      color: Color(0xFF0D2B5E)),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          // Loading or Stats Grid
          if (_isLoadingStats)
            const Center(
              child: Padding(
                padding: EdgeInsets.symmetric(vertical: 18),
                child: CircularProgressIndicator(
                    strokeWidth: 2.5,
                    color: Color(0xFF0D2B5E)),
              ),
            )
          else ...[
            Row(
              children: items.map((item) {
                final color = item['color'] as Color;
                final bg    = item['bg'] as Color;
                final icon  = item['icon'] as IconData;
                final value = item['value'] as int;
                final label = item['label'] as String;
                return Expanded(
                  child: Container(
                    margin: const EdgeInsets.only(right: 8),
                    padding: const EdgeInsets.symmetric(
                        vertical: 12, horizontal: 8),
                    decoration: BoxDecoration(
                      color: bg,
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                          color: color.withValues(alpha: 0.18)),
                    ),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(icon, color: color, size: 22),
                        const SizedBox(height: 6),
                        Text(
                          '$value',
                          style: TextStyle(
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                              color: color,
                              height: 1.1),
                        ),
                        const SizedBox(height: 3),
                        Text(
                          label,
                          textAlign: TextAlign.center,
                          style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w500,
                              color: color.withValues(alpha: 0.8)),
                        ),
                      ],
                    ),
                  ),
                );
              }).toList()
                ..last = Expanded(
                  child: Container(
                    margin: EdgeInsets.zero,
                    padding: const EdgeInsets.symmetric(
                        vertical: 12, horizontal: 8),
                    decoration: BoxDecoration(
                      color: items.last['bg'] as Color,
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                          color: (items.last['color'] as Color)
                              .withValues(alpha: 0.18)),
                    ),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(items.last['icon'] as IconData,
                            color: items.last['color'] as Color, size: 22),
                        const SizedBox(height: 6),
                        Text(
                          '${items.last['value']}',
                          style: TextStyle(
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                              color: items.last['color'] as Color,
                              height: 1.1),
                        ),
                        const SizedBox(height: 3),
                        Text(
                          items.last['label'] as String,
                          textAlign: TextAlign.center,
                          style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.w500,
                              color: (items.last['color'] as Color)
                                  .withValues(alpha: 0.8)),
                        ),
                      ],
                    ),
                  ),
                ),
            ),
            if (totalHariKerja > 0) ...[
              const SizedBox(height: 10),
              Row(
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  Text(
                    'Total hari kerja bulan ini: $totalHariKerja hari',
                    style: TextStyle(
                        fontSize: 10.5,
                        color: Colors.grey.shade500),
                  ),
                ],
              ),
            ],
          ],
        ],
      ),
    );
  }

  Widget _buildPatrolProgress() {
    final int total = _progress?.totalCheckpoint ?? 0;
    final int terlewati = _progress?.terlewati ?? 0;
    final double persentase = _progress?.persentase ?? 0;
    final double progressValue = total > 0 ? terlewati / total : 0;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
              color: Colors.black.withValues(alpha: 0.06),
              blurRadius: 8,
              offset: const Offset(0, 2)),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Row(
                children: [
                  Icon(Icons.route_outlined, color: Color(0xFF0D2B5E), size: 20),
                  SizedBox(width: 6),
                  Text(
                    'Pos Terlewati',
                    style: TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF0D2B5E)),
                  ),
                ],
              ),
              GestureDetector(
                onTap: _openHistory,
                child: Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                  decoration: BoxDecoration(
                    color: const Color(0xFF0D2B5E).withValues(alpha: 0.08),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(
                        color: const Color(0xFF0D2B5E).withValues(alpha: 0.15)),
                  ),
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.history, size: 14, color: Color(0xFF0D2B5E)),
                      SizedBox(width: 4),
                      Text('History',
                          style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                              color: Color(0xFF0D2B5E))),
                    ],
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          if (_isLoadingProgress)
            Column(
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: const LinearProgressIndicator(
                    minHeight: 10,
                    backgroundColor: Color(0xFFE8EDF5),
                    valueColor:
                        AlwaysStoppedAnimation<Color>(Color(0xFF0D2B5E)),
                  ),
                ),
                const SizedBox(height: 8),
                Text('Memuat data patroli...',
                    style:
                        TextStyle(fontSize: 11, color: Colors.grey.shade400)),
              ],
            )
          else ...[
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: const Color(0xFF0D2B5E).withValues(alpha: 0.08),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text('$terlewati / $total Pos',
                      style: const TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: Color(0xFF0D2B5E))),
                ),
                Text('${persentase.toStringAsFixed(1)}%',
                    style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF0D2B5E))),
              ],
            ),
            const SizedBox(height: 10),
            ClipRRect(
              borderRadius: BorderRadius.circular(8),
              child: LinearProgressIndicator(
                value: progressValue,
                minHeight: 10,
                backgroundColor: const Color(0xFFE8EDF5),
                valueColor:
                    const AlwaysStoppedAnimation<Color>(Color(0xFF0D2B5E)),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildAbsensiAlert() {
    final absensi = context.watch<AbsensiProvider>();
    return Row(
      children: [
        Expanded(
          child: _buildAlertCard(
            'Absen Masuk',
            absensi.sudahCheckin,
            Icons.login_outlined,
            absensi.sudahCheckin ? absensi.checkinDateTime : null,
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: _buildAlertCard(
            'Absen Keluar',
            absensi.sudahCheckout,
            Icons.logout_outlined,
            absensi.sudahCheckout ? absensi.checkoutTime : null,
          ),
        ),
      ],
    );
  }

  Widget _buildAlertCard(String label, bool done, IconData icon,
      [String? datetime]) {
    final Color color =
        done ? const Color(0xFF1B8B4B) : const Color(0xFFD97706);
    final Color bgColor =
        done ? const Color(0xFFEAF8F0) : const Color(0xFFFFF4E5);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Row(
        children: [
          Icon(icon, color: color, size: 26),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(done ? 'Sudah' : 'Belum',
                    style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                        color: color)),
                Text(label,
                    style: TextStyle(
                        fontSize: 11, color: color.withValues(alpha: 0.8))),
                if (datetime != null) ...[
                  const SizedBox(height: 2),
                  Text(datetime,
                      style: TextStyle(
                          fontSize: 10, color: color.withValues(alpha: 0.75)),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMenuGrid(BuildContext context) {
    final menus = [
      {
        'icon': Icons.security_outlined,
        'label': 'Patroli',
        'onTap': () {
          final token = context.read<AuthProvider>().token;
          final me = context.read<AuthProvider>().meUser;
          if (token == null) return;
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) =>
                  PatroliScanScreen(token: token, shift: me?.shift),
            ),
          ).then((_) => _fetchProgress());
        },
      },
      {
        'icon': Icons.fingerprint,
        'label': 'Absensi',
        'onTap': () => _showAbsensiMenu(context),
      },
      {
        'icon': Icons.assignment_outlined,
        'label': 'Aktifitas\nHarian',
        'onTap': () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => const DailyActivityScreen()),
          );
        },
      },
      {
        'icon': Icons.menu_book_outlined,
        'label': 'Buku\nTamu',
        'onTap': () {
          Navigator.push(
            context,
            MaterialPageRoute(builder: (_) => GuestLogScreen()),
          );
        },
      },
    ];

    return GridView.count(
      crossAxisCount: 2,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      crossAxisSpacing: 12,
      mainAxisSpacing: 12,
      childAspectRatio: 1.4,
      children: menus
          .map((menu) => _buildMenuCard(
                icon: menu['icon'] as IconData,
                label: menu['label'] as String,
                onTap: menu['onTap'] as VoidCallback,
              ))
          .toList(),
    );
  }

  Widget _buildMenuCard({
    required IconData icon,
    required String label,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          boxShadow: [
            BoxShadow(
                color: Colors.black.withValues(alpha: 0.06),
                blurRadius: 8,
                offset: const Offset(0, 2)),
          ],
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(13),
              decoration: BoxDecoration(
                color: const Color(0xFF0D2B5E).withValues(alpha: 0.08),
                shape: BoxShape.circle,
              ),
              child: Icon(icon, size: 28, color: const Color(0xFF0D2B5E)),
            ),
            const SizedBox(height: 8),
            Text(label,
                textAlign: TextAlign.center,
                style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                    color: Color(0xFF0D2B5E))),
          ],
        ),
      ),
    );
  }
}