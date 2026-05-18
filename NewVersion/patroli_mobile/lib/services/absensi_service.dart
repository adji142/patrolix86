import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:patroli_mobile/config/app_config.dart';

class AbsensiService {
  static const String _baseUrl = AppConfig.baseUrl;

  Map<String, String> _headers(String token) => {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      };

  Future<Map<String, dynamic>> fetchToday(String token) async {
    final response = await http.get(
      Uri.parse('$_baseUrl/absensi/today'),
      headers: _headers(token),
    );
    return jsonDecode(response.body) as Map<String, dynamic>;
  }

  Future<Map<String, dynamic>> fetchMonthlyStats(String token) async {
    final response = await http.get(
      Uri.parse('$_baseUrl/absensi/monthly-stats'),
      headers: _headers(token),
    );
    return jsonDecode(response.body) as Map<String, dynamic>;
  }

  Future<Map<String, dynamic>> checkin(String token, Map<String, dynamic> params) async {
    final response = await http.post(
      Uri.parse('$_baseUrl/absensi/checkin'),
      headers: _headers(token),
      body: jsonEncode(params),
    );
    return jsonDecode(response.body) as Map<String, dynamic>;
  }

  Future<Map<String, dynamic>> checkout(String token, Map<String, dynamic> params) async {
    final response = await http.post(
      Uri.parse('$_baseUrl/absensi/checkout'),
      headers: _headers(token),
      body: jsonEncode(params),
    );
    return jsonDecode(response.body) as Map<String, dynamic>;
  }
}