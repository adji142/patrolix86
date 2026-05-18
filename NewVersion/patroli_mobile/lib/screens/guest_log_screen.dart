import 'dart:convert';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:image/image.dart' as img;
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import 'package:patroli_mobile/config/app_config.dart';
import 'package:patroli_mobile/providers/auth_provider.dart';
import 'package:patroli_mobile/services/guest_log_service.dart';

class GuestLogScreen extends StatefulWidget {
  @override
  _GuestLogScreenState createState() => _GuestLogScreenState();
}

class _GuestLogScreenState extends State<GuestLogScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final GuestLogService _service = GuestLogService();
  final _namaTamuCtrl = TextEditingController();
  final _namaDicariCtrl = TextEditingController();
  final _tujuanCtrl = TextEditingController();
  final _keteranganCtrl = TextEditingController();

  DateTime _tanggal = DateTime.now();
  TimeOfDay _jamMasuk = TimeOfDay.now();
  TimeOfDay? _jamKeluar;

  File? _imageIn;
  File? _imageOut;
  final ImagePicker _picker = ImagePicker();

  bool _isLoading = false;
  List<dynamic> _riwayat = [];
  DateTime _tglAwal = DateTime.now().subtract(const Duration(days: 7));
  DateTime _tglAkhir = DateTime.now();

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
    _namaTamuCtrl.dispose();
    _namaDicariCtrl.dispose();
    _tujuanCtrl.dispose();
    _keteranganCtrl.dispose();
    super.dispose();
  }

  Future<void> _fetchRiwayat() async {
    final auth = context.read<AuthProvider>();
    final token = auth.token;
    if (token == null) return;

    setState(() => _isLoading = true);
    try {
      final result = await _service.fetchRiwayat(
        token,
        DateFormat('yyyy-MM-dd').format(_tglAwal),
        DateFormat('yyyy-MM-dd').format(_tglAkhir),
      );

      if (result['success'] == true) {
        setState(() => _riwayat = result['data'] ?? []);
      }
    } catch (e) {
      _showMsg('Error', 'Gagal memuat riwayat buku tamu.');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _pickImage(bool isIn) async {
    final pickedFile = await _picker.pickImage(source: ImageSource.camera, imageQuality: 50);
    if (pickedFile != null) {
      setState(() {
        if (isIn) {
          _imageIn = File(pickedFile.path);
        } else {
          _imageOut = File(pickedFile.path);
        }
      });
    }
  }

  Future<String?> _compressToBase64(File? file) async {
    if (file == null) return null;
    final bytes = await file.readAsBytes();
    final image = img.decodeImage(bytes);
    if (image == null) return base64Encode(bytes);
    final resized = img.copyResize(image, width: 800);
    return base64Encode(img.encodeJpg(resized, quality: 70));
  }

  Future<void> _submit() async {
    if (_namaTamuCtrl.text.isEmpty || _namaDicariCtrl.text.isEmpty || _tujuanCtrl.text.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Mohon lengkapi data wajib')));
      return;
    }

    setState(() => _isLoading = true);
    try {
      final auth = context.read<AuthProvider>();
      final token = auth.token ?? '';
      final user = auth.meUser;

      final tglStr = DateFormat('yyyy-MM-dd').format(_tanggal);
      final jamMasukStr = '${_jamMasuk.hour.toString().padLeft(2, '0')}:${_jamMasuk.minute.toString().padLeft(2, '0')}:00';
      String? jamKeluarStr;
      if (_jamKeluar != null) {
        jamKeluarStr = '${_jamKeluar!.hour.toString().padLeft(2, '0')}:${_jamKeluar!.minute.toString().padLeft(2, '0')}:00';
      }

      final imgInBase64 = await _compressToBase64(_imageIn);
      final imgOutBase64 = await _compressToBase64(_imageOut);

      final params = {
        'Tanggal': tglStr,
        'TglMasuk': '$tglStr $jamMasukStr',
        'TglKeluar': _jamKeluar != null ? '$tglStr $jamKeluarStr' : null,
        'NamaTamu': _namaTamuCtrl.text.trim(),
        'NamaYangDicari': _namaDicariCtrl.text.trim(),
        'Tujuan': _tujuanCtrl.text.trim(),
        'Keterangan': _keteranganCtrl.text.trim(),
        'LocationID': user?.lokasiPatroli?.id,
        'ImageInBase64': imgInBase64,
        'ImageOutBase64': imgOutBase64,
      };

      final result = await _service.submitGuestLog(token, params);
      if (result['success'] == true) {
        if (mounted) {
          _namaTamuCtrl.clear();
          _namaDicariCtrl.clear();
          _tujuanCtrl.clear();
          _keteranganCtrl.clear();
          setState(() {
            _imageIn = null;
            _imageOut = null;
            _jamKeluar = null;
          });
          _fetchRiwayat();
          _showMsg('Berhasil', 'Data buku tamu berhasil disimpan.');
          _tabController.animateTo(1);
        }
      } else {
        _showMsg('Gagal', result['message'] ?? 'Terjadi kesalahan.');
      }
    } catch (e) {
      _showMsg('Error', 'Terjadi kesalahan: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

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

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 2,
      child: Scaffold(
        backgroundColor: const Color(0xFFF0F4FA),
        appBar: AppBar(
          title: const Text('Buku Tamu', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white)),
          backgroundColor: _primary,
          iconTheme: const IconThemeData(color: Colors.white),
          centerTitle: true,
          bottom: TabBar(
            controller: _tabController,
            indicatorColor: Colors.white,
            labelColor: Colors.white,
            unselectedLabelColor: Colors.white60,
            tabs: const [
              Tab(icon: Icon(Icons.edit), text: 'Input Data'),
              Tab(icon: Icon(Icons.history), text: 'Riwayat'),
            ],
          ),
        ),
        body: TabBarView(
          controller: _tabController,
          children: [
            _buildForm(),
            _buildRiwayat(),
          ],
        ),
      ),
    );
  }

  Widget _buildForm() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildCard(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Informasi Tamu', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: _primary)),
                const SizedBox(height: 12),
                TextField(
                  controller: _namaTamuCtrl,
                  decoration: const InputDecoration(labelText: 'Nama Tamu *', border: OutlineInputBorder()),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _namaDicariCtrl,
                  decoration: const InputDecoration(labelText: 'Mencari Siapa? *', border: OutlineInputBorder()),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _tujuanCtrl,
                  decoration: const InputDecoration(labelText: 'Tujuan Kunjungan *', border: OutlineInputBorder()),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _keteranganCtrl,
                  decoration: const InputDecoration(labelText: 'Keterangan (Opsional)', border: OutlineInputBorder()),
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),
          _buildCard(
            child: Column(
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Tanggal', style: TextStyle(fontWeight: FontWeight.bold, color: _primary)),
                          TextButton(
                            onPressed: () async {
                              final d = await showDatePicker(context: context, initialDate: _tanggal, firstDate: DateTime(2020), lastDate: DateTime(2100));
                              if (d != null) setState(() => _tanggal = d);
                            },
                            child: Row(
                              children: [
                                const Icon(Icons.calendar_today, size: 16),
                                const SizedBox(width: 8),
                                Text(DateFormat('dd MMM yyyy').format(_tanggal)),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Jam Masuk', style: TextStyle(fontWeight: FontWeight.bold, color: _primary)),
                          TextButton(
                            onPressed: () async {
                              final t = await showTimePicker(context: context, initialTime: _jamMasuk);
                              if (t != null) setState(() => _jamMasuk = t);
                            },
                            child: Row(
                              children: [
                                const Icon(Icons.access_time, size: 16),
                                const SizedBox(width: 8),
                                Text(_jamMasuk.format(context)),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const Divider(),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Jam Keluar (Opsional)', style: TextStyle(fontWeight: FontWeight.bold, color: _primary)),
                    TextButton(
                      onPressed: () async {
                        final t = await showTimePicker(context: context, initialTime: _jamKeluar ?? TimeOfDay.now());
                        if (t != null) setState(() => _jamKeluar = t);
                      },
                      child: Row(
                        children: [
                          const Icon(Icons.exit_to_app, size: 16),
                          const SizedBox(width: 8),
                          Text(_jamKeluar?.format(context) ?? 'Belum Keluar'),
                        ],
                      ),
                    ),
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
                const Text('Foto Kunjungan', style: TextStyle(fontWeight: FontWeight.bold, color: _primary)),
                const SizedBox(height: 12),
                Row(
                  children: [
                    Expanded(
                      child: Column(
                        children: [
                          const Text('Foto Masuk', style: TextStyle(fontSize: 12)),
                          const SizedBox(height: 8),
                          GestureDetector(
                            onTap: () => _pickImage(true),
                            child: Container(
                              height: 100,
                              width: double.infinity,
                              decoration: BoxDecoration(border: Border.all(color: Colors.grey[300]!), borderRadius: BorderRadius.circular(8)),
                              child: _imageIn == null ? const Icon(Icons.camera_alt, size: 40, color: Colors.grey) : ClipRRect(borderRadius: BorderRadius.circular(8), child: Image.file(_imageIn!, fit: BoxFit.cover)),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        children: [
                          const Text('Foto Keluar', style: TextStyle(fontSize: 12)),
                          const SizedBox(height: 8),
                          GestureDetector(
                            onTap: () => _pickImage(false),
                            child: Container(
                              height: 100,
                              width: double.infinity,
                              decoration: BoxDecoration(border: Border.all(color: Colors.grey[300]!), borderRadius: BorderRadius.circular(8)),
                              child: _imageOut == null ? const Icon(Icons.camera_alt, size: 40, color: Colors.grey) : ClipRRect(borderRadius: BorderRadius.circular(8), child: Image.file(_imageOut!, fit: BoxFit.cover)),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 30),
          SizedBox(
            width: double.infinity,
            height: 50,
            child: ElevatedButton(
              onPressed: _isLoading ? null : _submit,
              style: ElevatedButton.styleFrom(
                backgroundColor: _primary,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
              child: _isLoading ? const CircularProgressIndicator(color: Colors.white) : const Text('SIMPAN DATA TAMU', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
            ),
          ),
        ],
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

  Widget _buildRiwayat() {
    return Column(
      children: [
        Padding(
          padding: EdgeInsets.all(8),
          child: Row(
            children: [
              Expanded(
                child: InkWell(
                  onTap: () async {
                    final d = await showDatePicker(context: context, initialDate: _tglAwal, firstDate: DateTime(2020), lastDate: DateTime(2100));
                    if (d != null) {
                      setState(() => _tglAwal = d);
                      _fetchRiwayat();
                    }
                  },
                  child: Container(
                    padding: EdgeInsets.symmetric(vertical: 8, horizontal: 12),
                    decoration: BoxDecoration(border: Border.all(color: Colors.grey), borderRadius: BorderRadius.circular(8)),
                    child: Text(DateFormat('dd/MM/yy').format(_tglAwal)),
                  ),
                ),
              ),
              Padding(padding: EdgeInsets.symmetric(horizontal: 8), child: Text('s/d')),
              Expanded(
                child: InkWell(
                  onTap: () async {
                    final d = await showDatePicker(context: context, initialDate: _tglAkhir, firstDate: DateTime(2020), lastDate: DateTime(2100));
                    if (d != null) {
                      setState(() => _tglAkhir = d);
                      _fetchRiwayat();
                    }
                  },
                  child: Container(
                    padding: EdgeInsets.symmetric(vertical: 8, horizontal: 12),
                    decoration: BoxDecoration(border: Border.all(color: Colors.grey), borderRadius: BorderRadius.circular(8)),
                    child: Text(DateFormat('dd/MM/yy').format(_tglAkhir)),
                  ),
                ),
              ),
            ],
          ),
        ),
        Expanded(
          child: _isLoading
              ? Center(child: CircularProgressIndicator())
              : RefreshIndicator(
                  onRefresh: _fetchRiwayat,
                  child: _riwayat.isEmpty
                      ? ListView(children: [Center(child: Padding(padding: EdgeInsets.all(40), child: Text('Tidak ada data')))])
                      : ListView.builder(
                          itemCount: _riwayat.length,
                          itemBuilder: (context, index) {
                            final item = _riwayat[index];
                            return Card(
                              margin: EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                              child: ListTile(
                                leading: CircleAvatar(child: Icon(Icons.person), backgroundColor: Colors.blue[100]),
                                title: Text(item['NamaTamu'] ?? '-', style: TextStyle(fontWeight: FontWeight.bold)),
                                subtitle: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text('Bertemu: ${item['NamaYangDicari'] ?? '-'}'),
                                    Text('Masuk: ${DateFormat('dd/MM/yy HH:mm').format(DateTime.parse(item['TglMasuk']))}'),
                                    if (item['TglKeluar'] != null) Text('Keluar: ${DateFormat('dd/MM/yy HH:mm').format(DateTime.parse(item['TglKeluar']))}', style: TextStyle(color: Colors.green)),
                                  ],
                                ),
                                trailing: Icon(Icons.chevron_right),
                                onTap: () => _showDetail(item),
                              ),
                            );
                          },
                        ),
                ),
        ),
      ],
    );
  }

  void _showDetail(dynamic item) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (context) {
        return Container(
          padding: EdgeInsets.all(20),
          height: MediaQuery.of(context).size.height * 0.8,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text('Detail Kunjungan Tamu', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                  IconButton(icon: Icon(Icons.close), onPressed: () => Navigator.pop(context)),
                ],
              ),
              Divider(),
              Expanded(
                child: ListView(
                  children: [
                    _detailRow('Nama Tamu', item['NamaTamu']),
                    _detailRow('Mencari', item['NamaYangDicari']),
                    _detailRow('Tujuan', item['Tujuan']),
                    _detailRow('Keterangan', item['Keterangan']),
                    _detailRow('Tanggal', item['Tanggal'] != null ? DateFormat('dd MMM yyyy').format(DateTime.parse(item['Tanggal'])) : '-'),
                    _detailRow('Masuk', item['TglMasuk'] != null ? DateFormat('dd MMM yyyy HH:mm').format(DateTime.parse(item['TglMasuk'])) : '-'),
                    _detailRow('Keluar', item['TglKeluar'] != null ? DateFormat('dd MMM yyyy HH:mm').format(DateTime.parse(item['TglKeluar'])) : 'Belum Keluar'),
                    SizedBox(height: 20),
                    Row(
                      children: [
                        Expanded(
                          child: Column(
                            children: [
                              Text('Foto Masuk', style: TextStyle(fontWeight: FontWeight.bold)),
                              SizedBox(height: 8),
                              _buildImageThumb(item['ImageInUrl']),
                            ],
                          ),
                        ),
                        SizedBox(width: 16),
                        Expanded(
                          child: Column(
                            children: [
                              Text('Foto Keluar', style: TextStyle(fontWeight: FontWeight.bold)),
                              SizedBox(height: 8),
                              _buildImageThumb(item['ImageOutUrl']),
                            ],
                          ),
                        ),
                      ],
                    ),
                    SizedBox(height: 30),
                    if (item['TglKeluar'] == null)
                      SizedBox(
                        width: double.infinity,
                        height: 45,
                        child: ElevatedButton.icon(
                          onPressed: () {
                            Navigator.pop(context);
                            _openCheckoutModal(item);
                          },
                          icon: Icon(Icons.exit_to_app),
                          label: Text('CHECK OUT TAMU'),
                          style: ElevatedButton.styleFrom(backgroundColor: Colors.green, foregroundColor: Colors.white),
                        ),
                      ),
                    if (item['TglKeluar'] != null)
                      SizedBox(
                        width: double.infinity,
                        height: 45,
                        child: OutlinedButton.icon(
                          onPressed: () {
                            Navigator.pop(context);
                            _openCheckoutModal(item);
                          },
                          icon: Icon(Icons.edit),
                          label: Text('UBAH DATA KELUAR'),
                          style: OutlinedButton.styleFrom(foregroundColor: Colors.blue),
                        ),
                      ),
                    SizedBox(height: 20),
                  ],
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  void _openCheckoutModal(dynamic item) {
    TimeOfDay initialJamKeluar = TimeOfDay.now();
    if (item['TglKeluar'] != null) {
      final dt = DateTime.parse(item['TglKeluar']);
      initialJamKeluar = TimeOfDay(hour: dt.hour, minute: dt.minute);
    }

    File? localImageOut;
    bool isUpdating = false;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Container(
              padding: EdgeInsets.all(20),
              height: MediaQuery.of(context).size.height * 0.6,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Update Data Keluar', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                  Text(item['NamaTamu'], style: TextStyle(color: Colors.grey)),
                  Divider(),
                  SizedBox(height: 16),
                  Text('Jam Keluar', style: TextStyle(fontWeight: FontWeight.bold)),
                  TextButton(
                    onPressed: () async {
                      final t = await showTimePicker(context: context, initialTime: initialJamKeluar);
                      if (t != null) setModalState(() => initialJamKeluar = t);
                    },
                    child: Row(
                      children: [
                        Icon(Icons.access_time),
                        SizedBox(width: 8),
                        Text(initialJamKeluar.format(context), style: TextStyle(fontSize: 16)),
                      ],
                    ),
                  ),
                  SizedBox(height: 20),
                  Text('Foto Keluar', style: TextStyle(fontWeight: FontWeight.bold)),
                  SizedBox(height: 10),
                  GestureDetector(
                    onTap: () async {
                      final picked = await _picker.pickImage(source: ImageSource.camera, imageQuality: 50);
                      if (picked != null) {
                        setModalState(() => localImageOut = File(picked.path));
                      }
                    },
                    child: Container(
                      height: 150,
                      width: double.infinity,
                      decoration: BoxDecoration(border: Border.all(color: Colors.grey[300]!), borderRadius: BorderRadius.circular(8)),
                      child: localImageOut == null
                          ? (item['ImageOutUrl'] != null
                              ? Image.network(item['ImageOutUrl'], fit: BoxFit.cover)
                              : Icon(Icons.add_a_photo, size: 40, color: Colors.grey))
                          : Image.file(localImageOut!, fit: BoxFit.cover),
                    ),
                  ),
                  Spacer(),
                  SizedBox(
                    width: double.infinity,
                    height: 50,
                    child: ElevatedButton(
                      onPressed: isUpdating
                          ? null
                          : () async {
                              setModalState(() => isUpdating = true);
                              final auth = context.read<AuthProvider>();
                              final tglStr = DateFormat('yyyy-MM-dd').format(DateTime.parse(item['Tanggal']));
                              final jamKeluarStr = '${initialJamKeluar.hour.toString().padLeft(2, '0')}:${initialJamKeluar.minute.toString().padLeft(2, '0')}:00';

                              final payload = {
                                'NamaTamu': item['NamaTamu'],
                                'NamaYangDicari': item['NamaYangDicari'],
                                'Tujuan': item['Tujuan'],
                                'Tanggal': tglStr,
                                'TglMasuk': item['TglMasuk'],
                                'TglKeluar': '$tglStr $jamKeluarStr',
                                'LocationID': item['LocationID'],
                                'Keterangan': item['Keterangan'],
                              };

                              if (localImageOut != null) {
                                payload['ImageOutBase64'] = await _compressToBase64(localImageOut);
                              }

                              final result = await _service.updateGuestLog(auth.token!, item['id'], payload);
                              setModalState(() => isUpdating = false);

                              if (result['success'] == true) {
                                Navigator.pop(context);
                                _fetchRiwayat();
                                _showMsg('Berhasil', 'Data keluar berhasil diperbarui.');
                              } else {
                                _showMsg('Gagal', result['message'] ?? 'Terjadi kesalahan');
                              }
                            },
                      child: isUpdating ? CircularProgressIndicator(color: Colors.white) : Text('SIMPAN PERUBAHAN'),
                      style: ElevatedButton.styleFrom(backgroundColor: _primary, foregroundColor: Colors.white),
                    ),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  Widget _detailRow(String label, dynamic value) {
    return Padding(
      padding: EdgeInsets.symmetric(vertical: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: TextStyle(color: Colors.grey[600], fontSize: 12)),
          Text(value?.toString() ?? '-', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w500)),
        ],
      ),
    );
  }

  Widget _buildImageThumb(String? url) {
    if (url == null || url.isEmpty) {
      return Container(
        height: 120,
        width: double.infinity,
        decoration: BoxDecoration(color: Colors.grey[200], borderRadius: BorderRadius.circular(8)),
        child: Icon(Icons.image_not_supported, color: Colors.grey),
      );
    }

    return GestureDetector(
      onTap: () {
        showDialog(context: context, builder: (_) => Dialog(child: Image.network(url)));
      },
      child: ClipRRect(
        borderRadius: BorderRadius.circular(8),
        child: Image.network(
          url,
          height: 120,
          width: double.infinity,
          fit: BoxFit.cover,
          errorBuilder: (_, __, ___) => Container(
            height: 120,
            decoration: BoxDecoration(color: Colors.grey[200], borderRadius: BorderRadius.circular(8)),
            child: Icon(Icons.broken_image, color: Colors.grey),
          ),
        ),
      ),
    );
  }
}
