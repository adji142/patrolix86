import 'dart:convert';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:image/image.dart' as img;
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import 'package:patroli_mobile/config/app_config.dart';
import 'package:provider/provider.dart';

import 'package:patroli_mobile/providers/auth_provider.dart';
import 'package:patroli_mobile/services/daily_activity_service.dart';

class DailyActivityScreen extends StatefulWidget {
  const DailyActivityScreen({super.key});

  @override
  State<DailyActivityScreen> createState() => _DailyActivityScreenState();
}

class _DailyActivityScreenState extends State<DailyActivityScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final DailyActivityService _service = DailyActivityService();

  // ── Tab Form ──────────────────────────────────────────────────────────────
  final _formKey = GlobalKey<FormState>();
  DateTime _tanggal = DateTime.now();
  final _deskripsiCtrl = TextEditingController();
  final List<File> _fotoFiles = [];
  bool _isSubmitting = false;

  // ── Tab Riwayat ───────────────────────────────────────────────────────────
  List<dynamic> _riwayat = [];
  bool _isLoadingRiwayat = false;
  DateTime _filterAwal = DateTime.now().subtract(const Duration(days: 7));
  DateTime _filterAkhir = DateTime.now();

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
  }

  @override
  void dispose() {
    _tabController.dispose();
    _deskripsiCtrl.dispose();
    super.dispose();
  }

  // ── Data ──────────────────────────────────────────────────────────────────
  Future<void> _fetchRiwayat() async {
    final token = context.read<AuthProvider>().token;
    if (token == null) return;
    setState(() => _isLoadingRiwayat = true);
    try {
      final tglAwalStr = DateFormat('yyyy-MM-dd').format(_filterAwal);
      final tglAkhirStr = DateFormat('yyyy-MM-dd').format(_filterAkhir);
      final result = await _service.fetchRiwayat(token, tglAwalStr, tglAkhirStr);
      if (result['success'] == true) {
        setState(() => _riwayat = result['data'] ?? []);
      }
    } catch (e) {
      _showMsg('Error', 'Gagal memuat riwayat aktifitas.');
    } finally {
      setState(() => _isLoadingRiwayat = false);
    }
  }

  // ── Foto ──────────────────────────────────────────────────────────────────
  Future<void> _pickFoto() async {
    if (_fotoFiles.length >= 3) {
      _showMsg('Peringatan', 'Maksimal 3 foto per aktifitas.');
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

    setState(() => _isSubmitting = true);
    try {
      final auth = context.read<AuthProvider>();
      final token = auth.token ?? '';
      final user = auth.meUser;

      final fotos = <String>[];
      for (final f in _fotoFiles) {
        fotos.add(await _compressToBase64(f));
      }

      final params = {
        'Tanggal': DateFormat('yyyy-MM-dd').format(_tanggal),
        'DeskripsiAktifitas': _deskripsiCtrl.text.trim(),
        'LocationID': user?.lokasiPatroli?.id,
        'KodeKaryawan': user?.username,
        'NamaKaryawan': user?.namaUser,
        if (fotos.isNotEmpty) 'Gambar1Base64': fotos[0],
        if (fotos.length > 1) 'Gambar2Base64': fotos[1],
        if (fotos.length > 2) 'Gambar3Base64': fotos[2],
      };

      final result = await _service.submit(token, params);
      if (result['success'] == true) {
        _resetForm();
        _fetchRiwayat();
        if (mounted) {
          await showDialog(
            context: context,
            builder: (_) => AlertDialog(
              title: const Text('Berhasil'),
              content: const Text('Aktifitas harian berhasil disimpan.'),
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
    _deskripsiCtrl.clear();
    setState(() {
      _tanggal = DateTime.now();
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

  // ── Build ─────────────────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF0F4FA),
      appBar: AppBar(
        title: const Text(
          'Aktifitas Harian',
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
            Tab(text: 'Input Aktifitas'),
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
                  const Text('Tanggal', style: TextStyle(fontWeight: FontWeight.bold, color: _primary)),
                  const SizedBox(height: 8),
                  GestureDetector(
                    onTap: () async {
                      final picked = await showDatePicker(
                        context: context,
                        initialDate: _tanggal,
                        firstDate: DateTime.now().subtract(const Duration(days: 30)),
                        lastDate: DateTime.now(),
                      );
                      if (picked != null) {
                        setState(() => _tanggal = picked);
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
                          Text(DateFormat('dd MMMM yyyy').format(_tanggal)),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),
            _buildCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Deskripsi Aktifitas', style: TextStyle(fontWeight: FontWeight.bold, color: _primary)),
                  const SizedBox(height: 8),
                  TextFormField(
                    controller: _deskripsiCtrl,
                    maxLines: 4,
                    decoration: InputDecoration(
                      hintText: 'Tuliskan aktifitas yang dilakukan...',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                    ),
                    validator: (v) => (v == null || v.isEmpty) ? 'Deskripsi wajib diisi' : null,
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),
            _buildCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Foto Pendukung (Maks. 3)', style: TextStyle(fontWeight: FontWeight.bold, color: _primary)),
                  const SizedBox(height: 12),
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: [
                      ..._fotoFiles.asMap().entries.map((e) => Stack(
                        children: [
                          ClipRRect(
                            borderRadius: BorderRadius.circular(8),
                            child: Image.file(e.value, width: 80, height: 80, fit: BoxFit.cover),
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
                      if (_fotoFiles.length < 3)
                        GestureDetector(
                          onTap: _pickFoto,
                          child: Container(
                            width: 80, height: 80,
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
                    : const Text('Simpan Aktifitas', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildRiwayatTab() {
    return Column(
      children: [
        _buildFilterBar(),
        Expanded(
          child: _isLoadingRiwayat
              ? const Center(child: CircularProgressIndicator(color: _primary))
              : _riwayat.isEmpty
                  ? const Center(child: Text('Belum ada data aktifitas.'))
                  : RefreshIndicator(
                      onRefresh: _fetchRiwayat,
                      child: ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: _riwayat.length,
                        itemBuilder: (ctx, i) {
                          final item = _riwayat[i];
                          return _buildActivityCard(item);
                        },
                      ),
                    ),
        ),
      ],
    );
  }

  Widget _buildFilterBar() {
    return Container(
      padding: const EdgeInsets.all(12),
      color: Colors.white,
      child: Row(
        children: [
          Expanded(
            child: GestureDetector(
              onTap: () async {
                final picked = await showDateRangePicker(
                  context: context,
                  initialDateRange: DateTimeRange(start: _filterAwal, end: _filterAkhir),
                  firstDate: DateTime.now().subtract(const Duration(days: 365)),
                  lastDate: DateTime.now(),
                );
                if (picked != null) {
                  setState(() {
                    _filterAwal = picked.start;
                    _filterAkhir = picked.end;
                  });
                  _fetchRiwayat();
                }
              },
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                decoration: BoxDecoration(
                  border: Border.all(color: Colors.grey[300]!),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.date_range, size: 16, color: _primary),
                    const SizedBox(width: 8),
                    Text(
                      '${DateFormat('dd/MM').format(_filterAwal)} - ${DateFormat('dd/MM').format(_filterAkhir)}',
                      style: const TextStyle(fontSize: 13),
                    ),
                  ],
                ),
              ),
            ),
          ),
          const SizedBox(width: 8),
          IconButton(
            onPressed: _fetchRiwayat,
            icon: const Icon(Icons.refresh, color: _primary),
          ),
        ],
      ),
    );
  }

  Widget _buildActivityCard(dynamic item) {
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
              Text(
                DateFormat('EEEE, dd MMMM yyyy').format(DateTime.parse(item['Tanggal'])),
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: _primary),
              ),
              if (item['NamaLokasi'] != null)
                Text(
                  item['NamaLokasi'],
                  style: const TextStyle(fontSize: 11, color: Colors.grey),
                ),
            ],
          ),
          const Divider(),
          Text(
            item['DeskripsiAktifitas'] ?? '-',
            style: const TextStyle(fontSize: 14),
          ),
          const SizedBox(height: 12),
          if (item['Gambar1'] != null || item['Gambar2'] != null || item['Gambar3'] != null)
            SizedBox(
              height: 60,
              child: ListView(
                scrollDirection: Axis.horizontal,
                children: [
                  if (item['Gambar1'] != null) _buildImageThumb(item['Gambar1']),
                  if (item['Gambar2'] != null) _buildImageThumb(item['Gambar2']),
                  if (item['Gambar3'] != null) _buildImageThumb(item['Gambar3']),
                ],
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildImageThumb(String fileName) {
    final baseUrl = AppConfig.baseUrl.replaceAll('/api', '');
    final url = fileName.startsWith('http') ? fileName : '$baseUrl/activity/$fileName';
    
    return GestureDetector(
      onTap: () {
        showDialog(
          context: context,
          builder: (_) => Dialog(
            child: InteractiveViewer(child: Image.network(url)),
          ),
        );
      },
      child: Container(
        margin: const EdgeInsets.only(right: 8),
        width: 60,
        height: 60,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: Colors.grey[200]!),
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(8),
          child: Image.network(
            url,
            fit: BoxFit.cover,
            errorBuilder: (_, __, ___) => const Icon(Icons.image_not_supported, size: 20, color: Colors.grey),
          ),
        ),
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
