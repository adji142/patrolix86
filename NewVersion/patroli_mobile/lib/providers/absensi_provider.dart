import 'package:flutter/foundation.dart';
import 'package:patroli_mobile/services/absensi_service.dart';

class AbsensiProvider extends ChangeNotifier {
  final AbsensiService _service = AbsensiService();

  Map<String, dynamic>? _todayRecord;
  bool _isLoading = false;

  Map<String, dynamic>? get todayRecord => _todayRecord;
  bool get isLoading => _isLoading;

  bool get sudahCheckin =>
      _todayRecord != null &&
      (_todayRecord!['Checkin'] ?? '').toString().isNotEmpty;

  bool get sudahCheckout =>
      sudahCheckin &&
      (_todayRecord!['CheckOut'] ?? '').toString().isNotEmpty &&
      _todayRecord!['CheckOut'].toString() != '0000-00-00 00:00:00';

  String get checkinTime {
    if (!sudahCheckin) return '--:--';
    final raw = _todayRecord!['Checkin'].toString();
    final parts = raw.split(' ');
    if (parts.length < 2) return raw;
    return parts[1].split('.')[0];
  }

  String get checkinDateTime {
    if (!sudahCheckin) return 'Belum';
    final raw = _todayRecord!['Checkin'].toString();
    return raw.split('.')[0];
  }

  String get checkoutTime {
    if (!sudahCheckout) return '--:--';
    final raw = _todayRecord!['CheckOut'].toString();
    final parts = raw.split(' ');
    if (parts.length < 2) return raw;
    return parts[1].split('.')[0];
  }

  Future<void> fetchToday(String token) async {
    _isLoading = true;
    notifyListeners();
    try {
      final result = await _service.fetchToday(token);
      if (result['success'] == true) {
        final data = result['data'] as List?;
        _todayRecord = (data != null && data.isNotEmpty)
            ? data[0] as Map<String, dynamic>
            : null;
      }
    } catch (e) {
      debugPrint('AbsensiProvider fetchToday error: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }
}