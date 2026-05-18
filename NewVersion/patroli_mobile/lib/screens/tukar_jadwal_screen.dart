import 'dart:convert';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:image/image.dart' as img;
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import 'package:patroli_mobile/config/app_config.dart';
import 'package:provider/provider.dart';

import 'package:patroli_mobile/providers/auth_provider.dart';
import 'package:patroli_mobile/services/tukar_jadwal_service.dart';

class TukarJadwalScreen extends StatefulWidget {
  const TukarJadwalScreen({super.key});

  @override
  State<TukarJadwalScreen> createState() => _TukarJadwalScreenState();
}

class _TukarJadwalScreenState extends State<TukarJadwalScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final TukarJadwalService _tukarService = TukarJadwalService();

  // ── Tab Form ──────────────────────────────────────────────────────────────
  final _formKey = GlobalKey<FormState>();
  DateTime? _tglTukar;
  dynamic _selectedJadwalAwal;
  dynamic _selectedJadwalBaru;
  final _keteranganCtrl = TextEditingController();
  final List<File> _fotoFiles = [];
  bool _isSubmitting = false;

  List<dynamic> _allJadwal = [];
  List<dynamic> _shifts = [];
  bool _isLoadingJadwal = false;

  // ── Tab Riwayat ───────────────────────────────────────────────────────────
  List<dynamic> _riwayat = [];
  bool _isLoadingRiwayat = false;

  static const Color _primary = Color(0xFF0D2B5E);

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _tabController.addListener(() {
      if (_tabController.index == 1 && _riwayat.isEmpty) {
        _fetchRiwayat();
      }
    });
    _initData();
  }

  Future<void> _initData() async {
    await _fetchJadwal();
    await _fetchShifts();
  }

  @override
  void dispose() {
    _tabController.dispose();
    _keteranganCtrl.dispose();
    super.dispose();
  }

  // ── Data ──────────────────────────────────────────────────────────────────
  Future<void> _fetchJadwal() async {
    final token = context.read<AuthProvider>().token;
    if (token == null) return;
    setState(() => _isLoadingJadwal = true);
    try {
      final response = await _tukarService.fetchJadwal(token); 
      if (response['success'] == true) {
        setState(() => _allJadwal = response['data'] ?? []);
      }
    } catch (e) {
      debugPrint('Fetch Jadwal Error: $e');
    } finally {
      setState(() => _isLoadingJadwal = false);
    }
  }

  Future<void> _fetchShifts() async {
    final auth = context.read<AuthProvider>();
    final token = auth.token;
    final locId = auth.meUser?.locationID;
    if (token == null || locId == null) return;

    try {
      final response = await _tukarService.fetchShifts(token, locId);
      if (response['success'] == true) {
        setState(() => _shifts = response['data'] ?? []);
      }
    } catch (e) {
      debugPrint('Fetch Shifts Error: $e');
    }
  }

  Future<void> _fetchRiwayat() async {
    final token = context.read<AuthProvider>().token;
    if (token == null) return;
    setState(() => _isLoadingRiwayat = true);
    try {
      final result = await _tukarService.fetchRiwayat(token);
      if (result['success'] == true) {
        setState(() => _riwayat = result['data'] ?? []);
      }
    } catch (e) {
      _showMsg('Error', 'Gagal memuat riwayat pengajuan.');
    } finally {
      setState(() => _isLoadingRiwayat = false);
    }
  }

  // ── Foto ──────────────────────────────────────────────────────────────────
  Future<void> _pickFoto() async {
    if (_fotoFiles.length >= 5) {
      _showMsg('Peringatan', 'Maksimal 5 foto per pengajuan.');
      return;
    }
    final picker = ImagePicker();
    final picked = await picker.pickImage(source: ImageSource.camera, imageQuality: 70);
    if (picked == null) return;
    setState(() => _fotoFiles.add(File(picked.path)));
  }

  Future<String> _compressToBase64(File file) async {
    final bytes = await file.readAsBytes();
    final image = img.decodeImage(bytes);
    if (image == null) return base64Encode(bytes);
    final resized = img.copyResize(image, width: 800);
    return base64Encode(img.encodeJpg(resized, quality: 60));
  }

  // ── Submit ────────────────────────────────────────────────────────────────
  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_tglTukar == null) {
      _showMsg('Peringatan', 'Tanggal tukar harus diisi.');
      return;
    }
    if (_selectedJadwalAwal == null || _selectedJadwalBaru == null) {
      _showMsg('Peringatan', 'Jadwal awal dan baru harus dipilih.');
      return;
    }

    setState(() => _isSubmitting = true);
    try {
      final token = context.read<AuthProvider>().token ?? '';
      final fotos = <String>[];
      for (final f in _fotoFiles) {
        fotos.add(await _compressToBase64(f));
      }

      final params = {
        'TanggalTukar': DateFormat('yyyy-MM-dd').format(_tglTukar!),
        'idJadwalAwal': _selectedJadwalAwal['id'],
        'TargetShiftID': _selectedJadwalBaru is Map ? _selectedJadwalBaru['id'] : null,
        'IsToOff': _selectedJadwalBaru == 'OFF',
        'Keterangan': _keteranganCtrl.text.trim(),
        if (fotos.isNotEmpty) 'Fotos': fotos,
      };

      final result = await _tukarService.submit(token, params);
      if (result['success'] == true) {
        _resetForm();
        _fetchRiwayat();
        if (mounted) {
          await showDialog(
            context: context,
            builder: (_) => AlertDialog(
              title: const Text('Berhasil'),
              content: const Text('Pengajuan tukar jadwal berhasil dikirim.'),
              actions: [
                TextButton(
                  onPressed: () {
                    Navigator.pop(context);
                    _tabController.animateTo(1);
                  },
                  child: const Text('Lihat Riwayat'),
                ),
              ],
            ),
          );
        }
      } else {
        _showMsg('Gagal', result['message']?.toString() ?? 'Terjadi kesalahan.');
      }
    } catch (e) {
      _showMsg('Error', 'Terjadi kesalahan: $e');
    } finally {
      setState(() => _isSubmitting = false);
    }
  }

  void _resetForm() {
    _formKey.currentState?.reset();
    _keteranganCtrl.clear();
    setState(() {
      _tglTukar = null;
      _selectedJadwalAwal = null;
      _selectedJadwalBaru = null;
      _fotoFiles.clear();
    });
  }

  // ── Helpers ───────────────────────────────────────────────────────────────
  void _showMsg(String title, String msg) {
    if (!mounted) return;
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        title: Text(title),
        content: Text(msg),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('OK')),
        ],
      ),
    );
  }

  Widget _approvalBadge(int approval) {
    final labels = {0: 'Pending', 1: 'Disetujui', 2: 'Ditolak'};
    final colors = {
      0: Colors.orange,
      1: Colors.green,
      2: Colors.red,
    };
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
      decoration: BoxDecoration(
        color: (colors[approval] ?? Colors.grey).withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: (colors[approval] ?? Colors.grey).withValues(alpha: 0.4)),
      ),
      child: Text(
        labels[approval] ?? '-',
        style: TextStyle(
          fontSize: 12,
          fontWeight: FontWeight.w600,
          color: colors[approval] ?? Colors.grey,
        ),
      ),
    );
  }

  // ── Build ─────────────────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF0F4FA),
      appBar: AppBar(
        title: const Text(
          'Tukar Jadwal',
          style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white),
        ),
        backgroundColor: _primary,
        iconTheme: const IconThemeData(color: Colors.white),
        centerTitle: true,
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: Colors.white,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white60,
          tabs: const [
            Tab(text: 'Buat Pengajuan'),
            Tab(text: 'Riwayat'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          _buildFormTab(),
          _buildRiwayatTab(),
        ],
      ),
    );
  }

  Widget _buildFormTab() {
    if (_isLoadingJadwal) {
      return const Center(child: CircularProgressIndicator(color: _primary));
    }

    final userNik = context.read<AuthProvider>().meUser?.username;
    final myJadwal = _allJadwal.where((j) => j['KodeKaryawan'] == userNik).toList();

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Pilih Tanggal', style: TextStyle(fontWeight: FontWeight.bold, color: _primary)),
                  const SizedBox(height: 8),
                  GestureDetector(
                    onTap: () async {
                      final picked = await showDatePicker(
                        context: context,
                        initialDate: DateTime.now(),
                        firstDate: DateTime.now().subtract(const Duration(days: 7)),
                        lastDate: DateTime.now().add(const Duration(days: 30)),
                      );
                      if (picked != null) {
                        setState(() {
                          _tglTukar = picked;
                          _selectedJadwalAwal = null;
                          _selectedJadwalBaru = null;
                        });
                      }
                    },
                    child: Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        border: Border.all(color: Colors.grey[300]!),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.calendar_today, size: 18, color: _primary),
                          const SizedBox(width: 8),
                          Text(_tglTukar == null ? 'Pilih Tanggal' : DateFormat('dd MMMM yyyy').format(_tglTukar!)),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),
            if (_tglTukar != null) ...[
              _buildCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Jadwal Saya', style: TextStyle(fontWeight: FontWeight.bold, color: _primary)),
                    const SizedBox(height: 8),
                    DropdownButtonFormField<dynamic>(
                      value: _selectedJadwalAwal,
                      items: myJadwal.where((j) => j['Tanggal'] == DateFormat('yyyy-MM-dd').format(_tglTukar!)).map((j) {
                        final shift = (j['StatusKehadiran'] == 'OFF' || j['shiftid'] == -1) ? 'OFF' : (j['shift']?['NamaShift'] ?? 'N/A');
                        return DropdownMenuItem(value: j, child: Text('Shift: $shift'));
                      }).toList(),
                      onChanged: (v) => setState(() => _selectedJadwalAwal = v),
                      decoration: InputDecoration(
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12),
                      ),
                      hint: const Text('Pilih Jadwal Anda'),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 12),
              _buildCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Tukar Dengan', style: TextStyle(fontWeight: FontWeight.bold, color: _primary)),
                    const SizedBox(height: 8),
                    DropdownButtonFormField<dynamic>(
                      value: _selectedJadwalBaru,
                      items: [
                        const DropdownMenuItem(value: 'OFF', child: Text('OFF')),
                        ..._shifts.map((s) {
                          return DropdownMenuItem(value: s, child: Text('Shift: ${s['NamaShift']}'));
                        }),
                      ],
                      onChanged: (v) => setState(() => _selectedJadwalBaru = v),
                      decoration: InputDecoration(
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                        contentPadding: const EdgeInsets.symmetric(horizontal: 12),
                      ),
                      hint: const Text('Pilih Shift Tujuan'),
                    ),
                  ],
                ),
              ),
            ],
            const SizedBox(height: 12),
            _buildCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Keterangan', style: TextStyle(fontWeight: FontWeight.bold, color: _primary)),
                  const SizedBox(height: 8),
                  TextFormField(
                    controller: _keteranganCtrl,
                    maxLines: 3,
                    decoration: InputDecoration(
                      hintText: 'Alasan tukar jadwal...',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                    validator: (v) => (v == null || v.isEmpty) ? 'Keterangan wajib diisi' : null,
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),
            _buildCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Foto Pendukung', style: TextStyle(fontWeight: FontWeight.bold, color: _primary)),
                  const SizedBox(height: 12),
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: [
                      ..._fotoFiles.asMap().entries.map((e) => Stack(
                        children: [
                          ClipRRect(
                            borderRadius: BorderRadius.circular(8),
                            child: Image.file(e.value, width: 70, height: 70, fit: BoxFit.cover),
                          ),
                          Positioned(
                            top: 0, right: 0,
                            child: GestureDetector(
                              onTap: () => setState(() => _fotoFiles.removeAt(e.key)),
                              child: Container(
                                padding: const EdgeInsets.all(2),
                                decoration: const BoxDecoration(color: Colors.red, shape: BoxShape.circle),
                                child: const Icon(Icons.close, size: 14, color: Colors.white),
                              ),
                            ),
                          ),
                        ],
                      )),
                      if (_fotoFiles.length < 5)
                        GestureDetector(
                          onTap: _pickFoto,
                          child: Container(
                            width: 70, height: 70,
                            decoration: BoxDecoration(
                              border: Border.all(color: _primary.withValues(alpha: 0.3)),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: const Icon(Icons.add_a_photo, color: _primary),
                          ),
                        ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton(
                onPressed: _isSubmitting ? null : _submit,
                style: ElevatedButton.styleFrom(
                  backgroundColor: _primary,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                child: _isSubmitting
                    ? const CircularProgressIndicator(color: Colors.white)
                    : const Text('Kirim Pengajuan', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildRiwayatTab() {
    if (_isLoadingRiwayat) {
      return const Center(child: CircularProgressIndicator(color: _primary));
    }
    if (_riwayat.isEmpty) {
      return const Center(child: Text('Belum ada riwayat pengajuan.'));
    }
    return RefreshIndicator(
      onRefresh: _fetchRiwayat,
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: _riwayat.length,
        itemBuilder: (ctx, i) {
          final item = _riwayat[i];
          return Container(
            margin: const EdgeInsets.only(bottom: 12),
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 5)],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(item['TanggalTukar'] ?? '', style: const TextStyle(fontWeight: FontWeight.bold)),
                    _approvalBadge(item['Approval'] ?? 0),
                  ],
                ),
                const Divider(),
                Text('Keterangan: ${item['Keterangan'] ?? '-'}'),
                const SizedBox(height: 4),
                Text('Status: ${(item['jadwal_awal']?['shiftid'] == -1 || item['jadwal_awal']?['StatusKehadiran'] == 'OFF') ? 'OFF' : (item['jadwal_awal']?['shift']?['NamaShift'] ?? '-')} ➔ ${item['IsToOff'] == true ? 'OFF' : (item['target_shift']?['NamaShift'] ?? (item['jadwal_baru']?['shift']?['NamaShift'] ?? '-'))}', style: const TextStyle(fontSize: 12, color: Colors.blueGrey)),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildCard({required Widget child}) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        boxShadow: [BoxShadow(color: Colors.black.withValues(alpha: 0.05), blurRadius: 5)],
      ),
      child: child,
    );
  }
}
