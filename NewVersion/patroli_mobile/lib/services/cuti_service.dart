import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:patroli_mobile/config/app_config.dart';

class CutiService {
  final String _base = AppConfig.baseUrl;

  Map<String, String> _headers(String token) => {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer $token',
      };

  Future<Map<String, dynamic>> fetchKategori(String token) async {
    final res = await http.get(
      Uri.parse('$_base/pengajuan-cuti/kategori'),
      headers: _headers(token),
    );
    return jsonDecode(res.body);
  }

  Future<Map<String, dynamic>> fetchRiwayat(String token,
      {int? tahun, int? bulan}) async {
    final params = <String, String>{};
    if (tahun != null) params['tahun'] = tahun.toString();
    if (bulan != null) params['bulan'] = bulan.toString();

    final uri = Uri.parse('$_base/pengajuan-cuti').replace(queryParameters: params.isEmpty ? null : params);
    final res = await http.get(uri, headers: _headers(token));
    return jsonDecode(res.body);
  }

  Future<Map<String, dynamic>> submit(
      String token, Map<String, dynamic> params) async {
    final res = await http.post(
      Uri.parse('$_base/pengajuan-cuti'),
      headers: _headers(token),
      body: jsonEncode(params),
    );
    return jsonDecode(res.body);
  }
}