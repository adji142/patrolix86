import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'providers/auth_provider.dart';
import 'providers/absensi_provider.dart';
import 'screens/login_screen.dart';
import 'screens/dashboard_screen.dart';

void main() {
  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => AbsensiProvider()),
      ],
      child: const AmmanPatroliApp(),
    ),
  );
}

class AmmanPatroliApp extends StatelessWidget {
  const AmmanPatroliApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Amman Patroli',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xFF0D2B5E)),
        fontFamily: 'Roboto',
      ),
      routes: {
        '/login': (_) => const LoginScreen(),
        '/dashboard': (_) => const DashboardScreen(),
      },
      home: const _SplashRouter(),
    );
  }
}

// Menunggu AuthProvider selesai baca storage, lalu redirect
class _SplashRouter extends StatefulWidget {
  const _SplashRouter();

  @override
  State<_SplashRouter> createState() => _SplashRouterState();
}

class _SplashRouterState extends State<_SplashRouter> {
  @override
  void initState() {
    super.initState();
    _listenAndRedirect();
  }

  void _listenAndRedirect() {
    final auth = context.read<AuthProvider>();

    // Kalau sudah resolved (bukan initial), langsung redirect
    if (auth.status != AuthStatus.initial) {
      _redirect(auth.status);
      return;
    }

    // Belum resolved → tunggu notifikasi dari provider
    void listener() {
      if (auth.status != AuthStatus.initial) {
        auth.removeListener(listener);
        _redirect(auth.status);
      }
    }

    auth.addListener(listener);
  }

  void _redirect(AuthStatus status) {
    if (!mounted) return;
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      if (status == AuthStatus.authenticated) {
        Navigator.pushReplacementNamed(context, '/dashboard');
      } else {
        Navigator.pushReplacementNamed(context, '/login');
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      backgroundColor: Color(0xFF0D2B5E),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            CircularProgressIndicator(color: Colors.white),
            SizedBox(height: 20),
            Text(
              'Amman Patroli',
              style: TextStyle(
                color: Colors.white70,
                fontSize: 16,
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
        ),
      ),
    );
  }
}