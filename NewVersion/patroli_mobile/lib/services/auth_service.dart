import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:patroli_mobile/config/app_config.dart';
import 'package:patroli_mobile/models/user_me_model.dart';
import 'package:patroli_mobile/models/user_model.dart';

class AuthService {
  static const String _baseUrl = AppConfig.baseUrl;

  Future<UserModel> login({
    required String recordOwnerID,
    required String username,
    required String password,
  }) async {
    final response = await http.post(
      Uri.parse('$_baseUrl/auth/login'),
      headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
      body: jsonEncode({
        'RecordOwnerID': recordOwnerID,
        'username': username,
        'password': password,
        'LoginDate': _todayFormatted(),
      }),
    );

    final Map<String, dynamic> json = jsonDecode(response.body);

    if (response.statusCode == 200 && json['success'] == true) {
      return UserModel.fromJson(json);
    }

    throw Exception(json['message'] ?? 'Login gagal. Periksa kembali data Anda.');
  }

  Future<UserMeModel> fetchMe(String token) async {
    final response = await http.get(
      Uri.parse('$_baseUrl/auth/me'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    final Map<String, dynamic> json = jsonDecode(response.body);

    if (response.statusCode == 200 && json['success'] == true) {
      return UserMeModel.fromJson(json);
    }

    throw Exception(json['message'] ?? 'Gagal mengambil data user.');
  }

  Future<Map<String, dynamic>> fetchSecurity(String token) async {
    final response = await http.get(
      Uri.parse('$_baseUrl/auth/security'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    final String decodedBody = utf8.decode(response.bodyBytes);
    final String sanitizedBody = decodedBody.replaceAllMapped(
      RegExp(r'\\([^"\\\/bfnrtu])'),
      (match) => '\\\\${match.group(1)}',
    );

    final Map<String, dynamic> json = jsonDecode(sanitizedBody);

    if (response.statusCode == 200 && json['success'] == true) {
      return json;
    }

    throw Exception(json['message'] ?? 'Gagal mengambil data security.');
  }

  Future<Map<String, dynamic>> fetchSchedule(String token) async {
    final response = await http.get(
      Uri.parse('$_baseUrl/auth/schedule'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    final Map<String, dynamic> json = jsonDecode(response.body);

    if (response.statusCode == 200 && json['success'] == true) {
      return json;
    }

    throw Exception(json['message'] ?? 'Gagal mengambil data jadwal.');
  }

  String _todayFormatted() {
    final now = DateTime.now();
    return '${now.year}-${now.month.toString().padLeft(2, '0')}-${now.day.toString().padLeft(2, '0')}';
  }
}