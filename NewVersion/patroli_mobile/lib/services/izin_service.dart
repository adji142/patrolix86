import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:patroli_mobile/config/app_config.dart';

class IzinService {
  static const String _baseUrl = AppConfig.baseUrl;

  Map<String, String> _headers(String token) => {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      };

  Future<Map<String, dynamic>> fetchRiwayat(
    String token, {
    int? tahun,
    int? bulan,
  }) async {
    final params = <String, String>{};
    if (tahun != null) params['tahun'] = tahun.toString();
    if (bulan != null) params['bulan'] = bulan.toString();

    final uri = Uri.parse('$_baseUrl/pengajuan-izin').replace(queryParameters: params);
    final response = await http.get(uri, headers: _headers(token));
    return jsonDecode(response.body) as Map<String, dynamic>;
  }

  Future<Map<String, dynamic>> submit(
    String token,
    Map<String, dynamic> params,
  ) async {
    final response = await http.post(
      Uri.parse('$_baseUrl/pengajuan-izin'),
      headers: _headers(token),
      body: jsonEncode(params),
    );
    return jsonDecode(response.body) as Map<String, dynamic>;
  }
}