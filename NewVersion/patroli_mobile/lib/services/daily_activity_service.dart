import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:patroli_mobile/config/app_config.dart';

class DailyActivityService {
  final String _baseUrl = AppConfig.baseUrl;

  Future<Map<String, dynamic>> fetchRiwayat(String token, String tglAwal, String tglAkhir) async {
    try {
      final response = await http.get(
        Uri.parse('$_baseUrl/review-daily-activity?tgl_awal=$tglAwal&tgl_akhir=$tglAkhir'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );
      return jsonDecode(response.body);
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  Future<Map<String, dynamic>> submit(String token, Map<String, dynamic> params) async {
    try {
      final response = await http.post(
        Uri.parse('$_baseUrl/review-daily-activity'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: jsonEncode(params),
      );
      return jsonDecode(response.body);
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }

  Future<Map<String, dynamic>> delete(String token, int id) async {
    try {
      final response = await http.delete(
        Uri.parse('$_baseUrl/review-daily-activity/$id'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );
      return jsonDecode(response.body);
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    }
  }
}
