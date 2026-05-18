import 'dart:convert';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:image/image.dart' as img;
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import 'package:patroli_mobile/config/app_config.dart';
import 'package:provider/provider.dart';

import 'package:patroli_mobile/providers/auth_provider.dart';
import 'package:patroli_mobile/services/izin_service.dart';

class PengajuanIzinScreen extends StatefulWidget {
  const PengajuanIzinScreen({super.key});

  @override
  State<PengajuanIzinScreen> createState() => _PengajuanIzinScreenState();
}

class _PengajuanIzinScreenState extends State<PengajuanIzinScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final IzinService _izinService = IzinService();

  // ── Tab Form ──────────────────────────────────────────────────────────────
  final _formKey = GlobalKey<FormState>();
  DateTime? _tglAwal;
  DateTime? _tglAkhir;
  final _keteranganCtrl = TextEditingController();
  final List<File> _fotoFiles = [];
  bool _isSubmitting = false;

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
  }

  @override
  void dispose() {
    _tabController.dispose();
    _keteranganCtrl.dispose();
    super.dispose();
  }

  // ── Riwayat ───────────────────────────────────────────────────────────────
  Future<void> _fetchRiwayat() async {
    final token = context.read<AuthProvider>().token;
    if (token == null) return;
    setState(() => _isLoadingRiwayat = true);
    try {
      final result = await _izinService.fetchRiwayat(token);
      if (result['success'] == true) {
        setState(() => _riwayat = result['data'] ?? []);
      }
    } catch (e) {
      _showMsg('Error', 'Gagal memuat riwayat izin.');
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
    if (_tglAwal == null || _tglAkhir == null) {
      _showMsg('Peringatan', 'Tanggal izin harus diisi.');
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
        'TglIzinAwal': DateFormat('yyyy-MM-dd').format(_tglAwal!),
        'TglIzinAkhir': DateFormat('yyyy-MM-dd').format(_tglAkhir!),
        'KeteranganIzin': _keteranganCtrl.text.trim(),
        if (fotos.isNotEmpty) 'Fotos': fotos,
      };

      final result = await _izinService.submit(token, params);
      if (result['success'] == true) {
        _resetForm();
        _fetchRiwayat();
        if (mounted) {
          await showDialog(
            context: context,
            builder: (_) => AlertDialog(
              title: const Text('Berhasil'),
              content: const Text('Pengajuan izin berhasil dikirim.'),
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
      _tglAwal = null;
      _tglAkhir = null;
      _fotoFiles.clear();
    });
  }

  // ── Pilih Tanggal ─────────────────────────────────────────────────────────
  Future<void> _pickDate(bool isAwal) async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: isAwal ? (now) : (_tglAwal ?? now),
      firstDate: DateTime(now.year - 1),
      lastDate: DateTime(now.year + 1),
      builder: (ctx, child) => Theme(
        data: Theme.of(ctx).copyWith(
          colorScheme: const ColorScheme.light(primary: _primary),
        ),
        child: child!,
      ),
    );
    if (picked == null) return;
    setState(() {
      if (isAwal) {
        _tglAwal = picked;
        if (_tglAkhir != null && _tglAkhir!.isBefore(picked)) _tglAkhir = picked;
      } else {
        _tglAkhir = picked;
      }
    });
  }

  // ── Dialog helpers ────────────────────────────────────────────────────────
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

  // ── Badge status ──────────────────────────────────────────────────────────
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
          'Pengajuan Izin',
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

  // ── Tab Form ──────────────────────────────────────────────────────────────
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
                  const Text(
                    'Periode Izin',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: _primary),
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(child: _buildDateField('Tanggal Mulai', _tglAwal, () => _pickDate(true))),
                      const SizedBox(width: 12),
                      Expanded(child: _buildDateField('Tanggal Selesai', _tglAkhir, () => _pickDate(false))),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),
            _buildCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Keterangan',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: _primary),
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: _keteranganCtrl,
                    maxLines: 4,
                    decoration: InputDecoration(
                      hintText: 'Tuliskan alasan pengajuan izin...',
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(8),
                        borderSide: const BorderSide(color: _primary),
                      ),
                      contentPadding: const EdgeInsets.all(12),
                    ),
                    validator: (v) => (v == null || v.trim().isEmpty) ? 'Keterangan tidak boleh kosong' : null,
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),
            _buildCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'Foto Pendukung',
                        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: _primary),
                      ),
                      Text(
                        '${_fotoFiles.length}/5',
                        style: TextStyle(fontSize: 13, color: Colors.grey[600]),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Opsional. Maks. 5 foto.',
                    style: TextStyle(fontSize: 12, color: Colors.grey[500]),
                  ),
                  const SizedBox(height: 12),
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: [
                      ..._fotoFiles.asMap().entries.map(
                        (e) => _buildFotoThumbnail(e.key, e.value),
                      ),
                      if (_fotoFiles.length < 5)
                        GestureDetector(
                          onTap: _pickFoto,
                          child: Container(
                            width: 72,
                            height: 72,
                            decoration: BoxDecoration(
                              border: Border.all(color: _primary.withValues(alpha: 0.4), width: 1.5),
                              borderRadius: BorderRadius.circular(8),
                              color: _primary.withValues(alpha: 0.05),
                            ),
                            child: const Icon(Icons.add_photo_alternate_outlined, color: _primary, size: 28),
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
              height: 52,
              child: ElevatedButton(
                onPressed: _isSubmitting ? null : _submit,
                style: ElevatedButton.styleFrom(
                  backgroundColor: _primary,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  elevation: 2,
                ),
                child: _isSubmitting
                    ? const SizedBox(
                        width: 22,
                        height: 22,
                        child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                      )
                    : const Text(
                        'Kirim Pengajuan',
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white),
                      ),
              ),
            ),
            const SizedBox(height: 16),
          ],
        ),
      ),
    );
  }

  Widget _buildDateField(String label, DateTime? value, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
        decoration: BoxDecoration(
          border: Border.all(color: Colors.grey[300]!),
          borderRadius: BorderRadius.circular(8),
          color: Colors.white,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(label, style: TextStyle(fontSize: 11, color: Colors.grey[500])),
            const SizedBox(height: 4),
            Row(
              children: [
                const Icon(Icons.calendar_today_outlined, size: 16, color: _primary),
                const SizedBox(width: 6),
                Text(
                  value != null ? DateFormat('dd MMM yyyy').format(value) : 'Pilih tanggal',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w500,
                    color: value != null ? Colors.black87 : Colors.grey[400],
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFotoThumbnail(int index, File file) {
    return Stack(
      children: [
        ClipRRect(
          borderRadius: BorderRadius.circular(8),
          child: Image.file(file, width: 72, height: 72, fit: BoxFit.cover),
        ),
        Positioned(
          top: 2,
          right: 2,
          child: GestureDetector(
            onTap: () => setState(() => _fotoFiles.removeAt(index)),
            child: Container(
              padding: const EdgeInsets.all(2),
              decoration: const BoxDecoration(color: Colors.red, shape: BoxShape.circle),
              child: const Icon(Icons.close, size: 14, color: Colors.white),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildCard({required Widget child}) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: child,
    );
  }

  // ── Tab Riwayat ───────────────────────────────────────────────────────────
  Widget _buildRiwayatTab() {
    if (_isLoadingRiwayat) {
      return const Center(child: CircularProgressIndicator(color: _primary));
    }

    if (_riwayat.isEmpty) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.description_outlined, size: 60, color: Colors.grey[300]),
            const SizedBox(height: 12),
            Text('Belum ada riwayat pengajuan izin.',
                style: TextStyle(color: Colors.grey[500], fontSize: 14)),
            const SizedBox(height: 16),
            TextButton.icon(
              onPressed: _fetchRiwayat,
              icon: const Icon(Icons.refresh),
              label: const Text('Muat Ulang'),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _fetchRiwayat,
      color: _primary,
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: _riwayat.length,
        itemBuilder: (ctx, i) => _buildRiwayatCard(_riwayat[i]),
      ),
    );
  }

  Widget _buildRiwayatCard(Map<String, dynamic> item) {
    final tglAwal = item['TglIzinAwal'] ?? '-';
    final tglAkhir = item['TglIzinAkhir'] ?? '-';
    final keterangan = item['KeteranganIzin'] ?? '-';
    final approval = item['Approval'] as int? ?? 0;
    final catatan = item['CatatanApproval'];
    final fotos = (item['fotos'] as List?) ?? [];

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  const Icon(Icons.date_range, size: 16, color: _primary),
                  const SizedBox(width: 6),
                  Text(
                    '$tglAwal – $tglAkhir',
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                  ),
                ],
              ),
              _approvalBadge(approval),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            keterangan,
            style: TextStyle(fontSize: 13, color: Colors.grey[700]),
            maxLines: 3,
            overflow: TextOverflow.ellipsis,
          ),
          if (catatan != null && catatan.toString().isNotEmpty) ...[
            const SizedBox(height: 6),
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Icon(Icons.comment_outlined, size: 14, color: Colors.grey),
                const SizedBox(width: 4),
                Expanded(
                  child: Text(
                    catatan.toString(),
                    style: const TextStyle(fontSize: 12, color: Colors.grey),
                  ),
                ),
              ],
            ),
          ],
          if (fotos.isNotEmpty) ...[
            const SizedBox(height: 10),
            SizedBox(
              height: 60,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                itemCount: fotos.length,
                separatorBuilder: (_, __) => const SizedBox(width: 6),
                itemBuilder: (ctx, i) => ClipRRect(
                  borderRadius: BorderRadius.circular(6),
                  child: Image.network(
                    '${AppConfig.baseUrl.replaceAll('/api', '')}/pengajuan-izin/${fotos[i]['FileName']}',
                    width: 60,
                    height: 60,
                    fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => Container(
                      width: 60,
                      height: 60,
                      color: Colors.grey[200],
                      child: const Icon(Icons.broken_image_outlined, size: 20),
                    ),
                  ),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }
}