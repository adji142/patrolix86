import 'dart:convert';
import 'dart:io';
import 'dart:math';
import 'dart:typed_data';
import 'package:flutter/foundation.dart';
import 'package:image/image.dart' as img;
import 'package:tflite_flutter/tflite_flutter.dart';
import 'package:google_mlkit_face_detection/google_mlkit_face_detection.dart';

class FaceRecognitionService {
  Interpreter? _interpreter;
  int _inputSize = 112;
  int _outputSize = 192;
  dynamic _inputType;
  static const String modelPath = 'assets/models/mobilefacenet.tflite';

  Future<void> init() async {
    try {
      debugPrint("FaceRecognitionService: Loading model from $modelPath...");
      _interpreter = await Interpreter.fromAsset(modelPath);

      // Explicitly allocate tensors
      _interpreter!.allocateTensors();

      // Dynamically detect shapes
      var inputTensor = _interpreter!.getInputTensors().first;
      var outputTensor = _interpreter!.getOutputTensors().first;

      _inputSize = inputTensor.shape[1];
      _outputSize = outputTensor.shape[1];
      _inputType = inputTensor.type;

      debugPrint("FaceRecognitionService: Model loaded successfully.");
      debugPrint(" - Input Shape: ${inputTensor.shape}");
      debugPrint(" - Input Type: ${inputTensor.type}");
      debugPrint(" - Output Shape: ${outputTensor.shape}");
      debugPrint(" - Output Type: ${outputTensor.type}");
    } catch (e) {
      debugPrint("FaceRecognitionService Error in init: $e");
    }
  }

  // Generate embedding from a file and a detected face
  Future<List<double>?> getEmbedding(File imageFile, Face face) async {
    if (_interpreter == null) await init();
    if (_interpreter == null) {
      debugPrint("FaceRecognitionService Error: Interpreter is null");
      return null;
    }

    try {
      // 1. Load and Crop Image
      final bytes = await imageFile.readAsBytes();
      img.Image? fullImage = img.decodeImage(bytes);
      if (fullImage == null) {
        debugPrint("FaceRecognitionService Error: Failed to decode image");
        return null;
      }

      // 2. Warp face to canonical 112x112 using similarity transform (eye anchors)
      img.Image resizedFace = _warpFaceToCanonical(fullImage, face);

      // 3. Normalization
      var input;
      if (_inputType.toString().toLowerCase().contains('float32')) {
        input = _imageToByteListFloat32(resizedFace);
      } else {
        // Handle quantized models if necessary (Uint8)
        input = _imageToByteListUint8(resizedFace);
      }

      debugPrint("FaceRecognitionService: Running inference...");
      debugPrint(" - Input data length: ${input is List ? input.length : "flat list"}");

      // 4. Run Inference
      var output = List.filled(1 * _outputSize, 0.0).reshape([1, _outputSize]);
      _interpreter!.run(input, output);

      return List<double>.from(output[0]);
    } catch (e) {
      debugPrint("FaceRecognitionService Error in getEmbedding: $e");
      return null;
    }
  }

  // Preprocess for Image from session (Uint8List)
  Future<List<double>?> getEmbeddingFromBytes(Uint8List imageBytes, Face face) async {
    if (_interpreter == null) await init();
    if (_interpreter == null) return null;

    try {
      img.Image? fullImage = img.decodeImage(imageBytes);
      if (fullImage == null) return null;

      img.Image resizedFace = _warpFaceToCanonical(fullImage, face);

      var input;
      if (_inputType.toString().toLowerCase().contains('float32')) {
        input = _imageToByteListFloat32(resizedFace);
      } else {
        input = _imageToByteListUint8(resizedFace);
      }

      var output = List.filled(1 * _outputSize, 0.0).reshape([1, _outputSize]);
      _interpreter!.run(input, output);

      return List<double>.from(output[0]);
    } catch (e) {
      debugPrint("FaceRecognitionService Error in getEmbeddingFromBytes: $e");
      return null;
    }
  }

  // Similarity transform: warp face langsung ke 112x112 kanonik berdasarkan posisi mata.
  // Metode ini robust terhadap pitch (angle atas/bawah), yaw, dan roll karena posisi mata
  // selalu dipetakan ke koordinat kanonik MobileFaceNet, terlepas dari sudut kamera.
  img.Image _warpFaceToCanonical(img.Image fullImage, Face face) {
    final leftEye = face.landmarks[FaceLandmarkType.leftEye];
    final rightEye = face.landmarks[FaceLandmarkType.rightEye];

    if (leftEye == null || rightEye == null) {
      debugPrint("FaceRecognitionService: No eye landmarks, fallback to bounding box crop");
      return _basicCrop(fullImage, face);
    }

    // Posisi mata di gambar asli
    double slx = leftEye.position.x.toDouble();
    double sly = leftEye.position.y.toDouble();
    double srx = rightEye.position.x.toDouble();
    double sry = rightEye.position.y.toDouble();

    double eyeDist = sqrt((srx - slx) * (srx - slx) + (sry - sly) * (sry - sly));
    if (eyeDist < 10) {
      debugPrint("FaceRecognitionService: Eye distance too small ($eyeDist), fallback to bounding box");
      return _basicCrop(fullImage, face);
    }

    // Posisi mata kanonik MobileFaceNet untuk output 112x112
    const double dlx = 38.29, dly = 51.70;
    const double drx = 73.53, dry = 51.50;

    // Hitung koefisien similarity transform (rotasi + skala seragam + translasi)
    double scDx = srx - slx, scDy = sry - sly;
    double dcDx = drx - dlx, dcDy = dry - dly;
    double norm2 = scDx * scDx + scDy * scDy;

    double a = (dcDx * scDx + dcDy * scDy) / norm2;
    double b = (dcDy * scDx - dcDx * scDy) / norm2;
    double tx = dlx - a * slx + b * sly;
    double ty = dly - b * slx - a * sly;

    // Inverse transform untuk pixel remap (dst → src)
    double det = a * a + b * b;
    double aInv = a / det;
    double bInv = b / det;
    double txInv = -(a * tx + b * ty) / det;
    double tyInv = (b * tx - a * ty) / det;

    int outSize = _inputSize;
    img.Image output = img.Image(width: outSize, height: outSize);
    int imgW = fullImage.width;
    int imgH = fullImage.height;

    for (int y = 0; y < outSize; y++) {
      for (int x = 0; x < outSize; x++) {
        double srcX = aInv * x + bInv * y + txInv;
        double srcY = -bInv * x + aInv * y + tyInv;

        int x0 = srcX.floor();
        int y0 = srcY.floor();
        int x1 = x0 + 1;
        int y1 = y0 + 1;

        if (x0 >= 0 && x1 < imgW && y0 >= 0 && y1 < imgH) {
          double fx = srcX - x0;
          double fy = srcY - y0;
          var p00 = fullImage.getPixel(x0, y0);
          var p10 = fullImage.getPixel(x1, y0);
          var p01 = fullImage.getPixel(x0, y1);
          var p11 = fullImage.getPixel(x1, y1);
          double r = p00.r * (1-fx)*(1-fy) + p10.r * fx*(1-fy) + p01.r * (1-fx)*fy + p11.r * fx*fy;
          double g = p00.g * (1-fx)*(1-fy) + p10.g * fx*(1-fy) + p01.g * (1-fx)*fy + p11.g * fx*fy;
          double bv = p00.b * (1-fx)*(1-fy) + p10.b * fx*(1-fy) + p01.b * (1-fx)*fy + p11.b * fx*fy;
          output.setPixelRgb(x, y, r.round(), g.round(), bv.round());
        } else if (x0 >= 0 && x0 < imgW && y0 >= 0 && y0 < imgH) {
          var p = fullImage.getPixel(x0, y0);
          output.setPixelRgb(x, y, p.r.toInt(), p.g.toInt(), p.b.toInt());
        }
      }
    }

    debugPrint("FaceRecognitionService: Canonical warp done (a=$a, b=$b, tx=$tx, ty=$ty)");
    return output;
  }

  img.Image _basicCrop(img.Image fullImage, Face face) {
    int padX = (face.boundingBox.width * 0.3).toInt();
    int padY = (face.boundingBox.height * 0.3).toInt();
    int x = max(0, face.boundingBox.left.toInt() - padX);
    int y = max(0, face.boundingBox.top.toInt() - padY);
    int w = min(face.boundingBox.width.toInt() + padX * 2, fullImage.width - x);
    int h = min(face.boundingBox.height.toInt() + padY * 2, fullImage.height - y);
    img.Image cropped = img.copyCrop(fullImage, x: x, y: y, width: w, height: h);
    return img.copyResize(cropped, width: _inputSize, height: _inputSize);
  }

  dynamic _imageToByteListFloat32(img.Image image) {
    var convertedBytes = Float32List(1 * _inputSize * _inputSize * 3);
    var buffer = Float32List.view(convertedBytes.buffer);
    int pixelIndex = 0;

    for (var i = 0; i < _inputSize; i++) {
      for (var j = 0; j < _inputSize; j++) {
        var pixel = image.getPixel(j, i);
        // Standard normalization for MobileFaceNet to [-1, 1]
        buffer[pixelIndex++] = (pixel.r - 127.5) / 127.5;
        buffer[pixelIndex++] = (pixel.g - 127.5) / 127.5;
        buffer[pixelIndex++] = (pixel.b - 127.5) / 127.5;
      }
    }
    return convertedBytes.reshape([1, _inputSize, _inputSize, 3]);
  }

  dynamic _imageToByteListUint8(img.Image image) {
    var convertedBytes = Uint8List(1 * _inputSize * _inputSize * 3);
    int pixelIndex = 0;

    for (var i = 0; i < _inputSize; i++) {
      for (var j = 0; j < _inputSize; j++) {
        var pixel = image.getPixel(j, i);
        convertedBytes[pixelIndex++] = pixel.r.toInt();
        convertedBytes[pixelIndex++] = pixel.g.toInt();
        convertedBytes[pixelIndex++] = pixel.b.toInt();
      }
    }
    return convertedBytes.reshape([1, _inputSize, _inputSize, 3]) as dynamic;
  }

  // Euclidean Distance or Cosine Similarity
  double compare(List<double> emb1, List<double> emb2) {
    return _cosineSimilarity(emb1, emb2);
  }

  double _cosineSimilarity(List<double> v1, List<double> v2) {
    double dotProduct = 0.0;
    double norm1 = 0.0;
    double norm2 = 0.0;
    for (int i = 0; i < v1.length; i++) {
      dotProduct += v1[i] * v2[i];
      norm1 += v1[i] * v1[i];
      norm2 += v2[i] * v2[i];
    }
    return dotProduct / (sqrt(norm1) * sqrt(norm2));
  }

  void dispose() {
    _interpreter?.close();
  }
}