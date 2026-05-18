import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:patroli_mobile/config/app_config.dart';

class TukarJadwalService {
  static const String _baseUrl = AppConfig.baseUrl;

  Map<String, String> _headers(String token) => {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      };

  Future<Map<String, dynamic>> fetchRiwayat(String token) async {
    final response = await http.get(
      Uri.parse('$_baseUrl/tukar-jadwal'),
      headers: _headers(token),
    );
    return jsonDecode(response.body) as Map<String, dynamic>;
  }

  Future<Map<String, dynamic>> submit(
    String token,
    Map<String, dynamic> params,
  ) async {
    final response = await http.post(
      Uri.parse('$_baseUrl/tukar-jadwal'),
      headers: _headers(token),
      body: jsonEncode(params),
    );
    return jsonDecode(response.body) as Map<String, dynamic>;
  }

  Future<Map<String, dynamic>> fetchJadwal(String token) async {
    final response = await http.get(
      Uri.parse('$_baseUrl/jadwal-kerja'),
      headers: _headers(token),
    );
    return jsonDecode(response.body) as Map<String, dynamic>;
  }

  Future<Map<String, dynamic>> fetchShifts(String token, String locationId) async {
    final response = await http.get(
      Uri.parse('$_baseUrl/tshift?LocationID=$locationId'),
      headers: _headers(token),
    );
    return jsonDecode(response.body) as Map<String, dynamic>;
  }
}
