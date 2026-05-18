import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:patroli_mobile/models/user_me_model.dart';
import 'package:patroli_mobile/services/auth_service.dart';

enum AuthStatus { initial, loading, authenticated, unauthenticated }

class AuthProvider extends ChangeNotifier {
  static const String _tokenKey = 'auth_token';
  static const String _meKey = 'user_me';

  final AuthService _authService = AuthService();

  AuthStatus _status = AuthStatus.initial;
  UserMeModel? _meUser;
  String? _token;
  String? _errorMessage;
  bool _isRefreshing = false;

  AuthStatus get status => _status;
  UserMeModel? get meUser => _meUser;
  String? get token => _token;
  String? get errorMessage => _errorMessage;
  bool get isRefreshing => _isRefreshing;
  bool get isAuthenticated => _status == AuthStatus.authenticated;

  AuthProvider() {
    _loadFromStorage();
  }

  Future<void> _loadFromStorage() async {
    final prefs = await SharedPreferences.getInstance();
    final storedToken = prefs.getString(_tokenKey);
    final storedMe = prefs.getString(_meKey);

    if (storedToken != null && storedMe != null) {
      try {
        _token = storedToken;
        _meUser = UserMeModel.fromJson(jsonDecode(storedMe));
        _status = AuthStatus.authenticated;
      } catch (_) {
        await _clearStorage(prefs);
        _status = AuthStatus.unauthenticated;
      }
    } else {
      _status = AuthStatus.unauthenticated;
    }
    notifyListeners();
  }

  // 1. POST /auth/login → simpan token
  // 2. GET /auth/me → simpan data lengkap
  // Loading indicator aktif selama kedua proses ini
  Future<bool> login({
    required String recordOwnerID,
    required String username,
    required String password,
  }) async {
    _status = AuthStatus.loading;
    _errorMessage = null;
    notifyListeners();

    try {
      final loginResult = await _authService.login(
        recordOwnerID: recordOwnerID,
        username: username,
        password: password,
      );

      _token = loginResult.token;

      final meUser = await _authService.fetchMe(_token!);
      _meUser = meUser;

      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_tokenKey, _token!);
      await prefs.setString(_meKey, jsonEncode(meUser.toJson()));

      _status = AuthStatus.authenticated;
      notifyListeners();

      // Trigger sequential background loading of security image and schedule preview
      loadSecurityAndSchedule();

      return true;
    } catch (e) {
      _status = AuthStatus.unauthenticated;
      _errorMessage = e.toString().replaceFirst('Exception: ', '');
      notifyListeners();
      return false;
    }
  }

  // Dipanggil saat dashboard dibuka dengan data dari storage —
  // refresh data terbaru dari server tanpa mengusik status auth.
  Future<void> refreshMe() async {
    if (_token == null || _isRefreshing) return;

    _isRefreshing = true;
    notifyListeners();

    try {
      final meUser = await _authService.fetchMe(_token!);
      _meUser = meUser;

      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_meKey, jsonEncode(meUser.toJson()));

      // Refresh security and schedule in background
      await loadSecurityAndSchedule();
    } catch (_) {
      // refresh gagal → tetap pakai data cache, tidak logout
    } finally {
      _isRefreshing = false;
      notifyListeners();
    }
  }

  // Asynchronously loads heavy security profile photo and schedule calculations in the background
  Future<void> loadSecurityAndSchedule() async {
    if (_token == null) return;

    try {
      final secRes = await _authService.fetchSecurity(_token!);
      if (secRes['success'] == true) {
        final secJson = secRes['security'];
        final fotoSec = secRes['FotoSecurity'] as String?;
        final securityModel = secJson != null
            ? SecurityModel.fromJson(secJson as Map<String, dynamic>)
            : null;

        _meUser = _meUser?.copyWith(
          security: securityModel,
          fotoSecurity: fotoSec,
        );
        notifyListeners();

        final prefs = await SharedPreferences.getInstance();
        await prefs.setString(_meKey, jsonEncode(_meUser?.toJson()));
      }
    } catch (e) {
      debugPrint('Error loading security in background: $e');
    }

    try {
      final schedRes = await _authService.fetchSchedule(_token!);
      if (schedRes['success'] == true) {
        final activeShiftJson = schedRes['ActiveShiftDetails'];
        final activeShiftDetails = activeShiftJson != null
            ? ActiveShiftDetailsModel.fromJson(activeShiftJson as Map<String, dynamic>)
            : null;

        _meUser = _meUser?.copyWith(
          shift: schedRes['Shift'] as int?,
          isGantiHari: schedRes['isGantiHari'] as int? ?? 0,
          activeShiftDetails: activeShiftDetails,
        );
        notifyListeners();

        final prefs = await SharedPreferences.getInstance();
        await prefs.setString(_meKey, jsonEncode(_meUser?.toJson()));
      }
    } catch (e) {
      debugPrint('Error loading schedule in background: $e');
    }
  }

  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await _clearStorage(prefs);
    _token = null;
    _meUser = null;
    _status = AuthStatus.unauthenticated;
    notifyListeners();
  }

  Future<void> _clearStorage(SharedPreferences prefs) async {
    await prefs.remove(_tokenKey);
    await prefs.remove(_meKey);
  }
}