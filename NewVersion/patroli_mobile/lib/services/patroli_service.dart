import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:patroli_mobile/config/app_config.dart';
import 'package:patroli_mobile/models/patroli_progress_model.dart';

class PatroliService {
  static const String _baseUrl = AppConfig.baseUrl;

  Future<PatroliProgressModel> fetchProgress(String token) async {
    final response = await http.get(
      Uri.parse('$_baseUrl/patroli/progress'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    final Map<String, dynamic> json = jsonDecode(response.body);

    if (response.statusCode == 200 && json['success'] == true) {
      return PatroliProgressModel.fromJson(json['data']);
    }

    throw Exception(json['message'] ?? 'Gagal mengambil data progress patroli.');
  }

  Future<void> submitPatroli({
    required String token,
    required String kodeCheckPoint,
    required String koordinat,
    required String image,
    required String tanggalPatroli,
    String? catatan,
    int? shift,
    int? shiftJadwal,
  }) async {
    final response = await http.post(
      Uri.parse('$_baseUrl/patroli/'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({
        'KodeCheckPoint': kodeCheckPoint,
        'Koordinat': koordinat,
        'Image': image,
        'TanggalPatroli': tanggalPatroli,
        if (catatan != null && catatan.isNotEmpty) 'Catatan': catatan,
        if (shift != null) 'Shift': shift,
        if (shiftJadwal != null) 'ShiftJadwal': shiftJadwal,
      }),
    );

    final Map<String, dynamic> json = jsonDecode(response.body);

    if ((response.statusCode == 200 || response.statusCode == 201) &&
        json['success'] == true) {
      return;
    }

    throw Exception(json['message'] ?? 'Gagal menyimpan data patroli.');
  }

  Future<List<PatroliHistoryItem>> fetchHistory(
    String token, {
    String? tanggal,
  }) async {
    final queryParams = <String, String>{};
    if (tanggal != null) queryParams['tanggal'] = tanggal;

    final uri = Uri.parse('$_baseUrl/patroli/history')
        .replace(queryParameters: queryParams.isEmpty ? null : queryParams);

    final response = await http.get(
      uri,
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    final Map<String, dynamic> json = jsonDecode(response.body);

    if (response.statusCode == 200 && json['success'] == true) {
      return (json['data'] as List)
          .map((item) => PatroliHistoryItem.fromJson(item))
          .toList();
    }

    throw Exception(json['message'] ?? 'Gagal mengambil history patroli.');
  }
}