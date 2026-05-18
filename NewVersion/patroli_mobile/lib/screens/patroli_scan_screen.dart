import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:patroli_mobile/screens/patroli_form_screen.dart';

class PatroliScanScreen extends StatefulWidget {
  final String token;
  final int? shift;
  final int? shiftJadwal;

  const PatroliScanScreen({
    super.key,
    required this.token,
    this.shift,
    this.shiftJadwal,
  });

  @override
  State<PatroliScanScreen> createState() => _PatroliScanScreenState();
}

class _PatroliScanScreenState extends State<PatroliScanScreen> {
  final MobileScannerController _controller = MobileScannerController();
  bool _hasScanned = false;

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _onDetect(BarcodeCapture capture) {
    if (_hasScanned) return;
    final barcode = capture.barcodes.firstOrNull;
    final rawValue = barcode?.rawValue;
    if (rawValue == null || rawValue.isEmpty) return;

    _hasScanned = true;
    _controller.stop();

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => PatroliFormScreen(
          token: widget.token,
          kodeCheckPoint: rawValue,
          shift: widget.shift,
          shiftJadwal: widget.shiftJadwal,
        ),
      ),
    ).then((_) {
      if (mounted) {
        setState(() => _hasScanned = false);
        _controller.start();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        backgroundColor: const Color(0xFF0D2B5E),
        foregroundColor: Colors.white,
        title: const Text(
          'Scan QR Checkpoint',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
        ),
      ),
      body: Stack(
        children: [
          MobileScanner(
            controller: _controller,
            onDetect: _onDetect,
          ),
          _buildOverlay(context),
        ],
      ),
    );
  }

  Widget _buildOverlay(BuildContext context) {
    final size = MediaQuery.of(context).size;
    final frameSize = size.width * 0.65;

    return Stack(
      children: [
        // Gelap di luar frame
        ColorFiltered(
          colorFilter: ColorFilter.mode(
            Colors.black.withValues(alpha: 0.55),
            BlendMode.srcOut,
          ),
          child: Stack(
            children: [
              Container(
                decoration: const BoxDecoration(
                  color: Colors.black,
                  backgroundBlendMode: BlendMode.dstOut,
                ),
              ),
              Center(
                child: Container(
                  width: frameSize,
                  height: frameSize,
                  decoration: BoxDecoration(
                    color: Colors.red,
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
              ),
            ],
          ),
        ),

        // Border frame dengan sudut
        Center(
          child: SizedBox(
            width: frameSize,
            height: frameSize,
            child: Stack(
              children: [
                _corner(0, 0, true, true),
                _corner(0, null, true, false),
                _corner(null, 0, false, true),
                _corner(null, null, false, false),
              ],
            ),
          ),
        ),

        // Teks panduan
        Positioned(
          bottom: 100,
          left: 0,
          right: 0,
          child: Text(
            'Arahkan kamera ke QR Code checkpoint',
            textAlign: TextAlign.center,
            style: TextStyle(
              color: Colors.white.withValues(alpha: 0.9),
              fontSize: 14,
              fontWeight: FontWeight.w500,
            ),
          ),
        ),
      ],
    );
  }

  Widget _corner(
    double? top,
    double? bottom,
    bool isLeft,
    bool isTop,
  ) {
    const size = 24.0;
    const thick = 4.0;
    return Positioned(
      top: top,
      bottom: bottom,
      left: isLeft ? 0 : null,
      right: isLeft ? null : 0,
      child: SizedBox(
        width: size,
        height: size,
        child: CustomPaint(
          painter: _CornerPainter(
            isTop: isTop,
            isLeft: isLeft,
            thickness: thick,
          ),
        ),
      ),
    );
  }
}

class _CornerPainter extends CustomPainter {
  final bool isTop;
  final bool isLeft;
  final double thickness;

  _CornerPainter({
    required this.isTop,
    required this.isLeft,
    required this.thickness,
  });

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = Colors.white
      ..strokeWidth = thickness
      ..style = PaintingStyle.stroke
      ..strokeCap = StrokeCap.round;

    final path = Path();
    if (isTop && isLeft) {
      path.moveTo(0, size.height);
      path.lineTo(0, 0);
      path.lineTo(size.width, 0);
    } else if (isTop && !isLeft) {
      path.moveTo(0, 0);
      path.lineTo(size.width, 0);
      path.lineTo(size.width, size.height);
    } else if (!isTop && isLeft) {
      path.moveTo(0, 0);
      path.lineTo(0, size.height);
      path.lineTo(size.width, size.height);
    } else {
      path.moveTo(0, size.height);
      path.lineTo(size.width, size.height);
      path.lineTo(size.width, 0);
    }
    canvas.drawPath(path, paint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}