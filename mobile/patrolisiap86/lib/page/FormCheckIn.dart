import 'dart:convert';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import 'package:patrolisiap86/general/session.dart';
import 'package:image_picker/image_picker.dart';

class FormCheckIn extends StatefulWidget {
  final session sess;
  final String KodeCheckPoint;
  final String NamaCheckPoint;
  FormCheckIn(this.sess, this.KodeCheckPoint, this.NamaCheckPoint);

  @override
  _FormCheckInState createState() => _FormCheckInState();
}

class _FormCheckInState extends State<FormCheckIn> {
  final ImagePicker _picker = ImagePicker();

  DateTime? _tanggal;
  File? imageFile;
  List? pathext;
  String? extentionPath;
  String image64 = "";
  Position? _currentPosition;

  // Position _posisi = new Position();
  TextEditingController _catatan = TextEditingController();

  _openCamera(BuildContext context) async {
    final ImagePicker _picker = ImagePicker();

    await _picker
        .pickImage(source: ImageSource.camera, maxHeight: 600)
        .then((value) {
      setState(() {
        imageFile = File(value!.path);
        pathext = imageFile!.uri.toString().split("/");
        extentionPath = pathext![pathext!.length - 1].toString();

        if (imageFile != null) {
          final bites = imageFile!.readAsBytesSync();
          image64 = base64Encode(bites);
        }
      });
      // Navigator.of(context).pop();
    });
  }

  Future<bool> _handleLocationPermission() async {
    bool serviceEnabled;
    LocationPermission permission;

    serviceEnabled = await Geolocator.isLocationServiceEnabled();
    if (!serviceEnabled) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text(
              'Location services are disabled. Please enable the services')));
      return false;
    }
    permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
      if (permission == LocationPermission.denied) {
        ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Location permissions are denied')));
        return false;
      }
    }
    if (permission == LocationPermission.deniedForever) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text(
              'Location permissions are permanently denied, we cannot request permissions.')));
      return false;
    }
    return true;
  }

  Future<void> _getCurrentPosition() async {
    final hasPermission = await _handleLocationPermission();
    if (!hasPermission) return;
    await Geolocator.getCurrentPosition(desiredAccuracy: LocationAccuracy.high)
        .then((Position position) {
      setState(() => _currentPosition = position);
    }).catchError((e) {
      debugPrint(e);
    });
  }

  void _getLocation() async {
    Position position = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high);
  }

  void _timeChange() {
    final DateTime now = DateTime.now();
    final String temp = DateFormat('jm').format(now).trim();
    final ampm = temp.substring(temp.length - 2, temp.length);

    int shift;

    if (ampm == "AM") {
      // AM
      //------------------------------------------------------------------------
      if (now.hour < DateTime.utc(1900, 1, 1, 7, 0, 0).hour) {
        shift = 3;
      } else {
        shift = 1;
      }
      //------------------------------------------------------------------------
    } else {
      // PM
      //------------------------------------------------------------------------
      if (now.hour < DateTime.utc(1900, 1, 1, 15, 0, 0).hour) {
        shift = 1;
      } else if (now.hour >= DateTime.utc(1900, 1, 1, 15, 0, 0).hour &&
          now.hour < DateTime.utc(1900, 1, 1, 23, 0, 0).hour) {
        shift = 2;
      } else {
        shift = 3;
      }
      //------------------------------------------------------------------------
    }

    setState(() {
      // _kodeShift = shift.toString();
      _tanggal = now;
    });
  }

  @override
  void initState() {
    _timeChange();
    _catatan.text = "";
    _getCurrentPosition();
    super.initState();
  }

  @override
  void dispose() {
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text("Patroli")),
      body: Padding(
        padding: EdgeInsets.all(this.widget.sess.width * 2),
        child: _tanggal == null
            ? Container()
            : ListView(
                children: [
                  //User Section
                  //------------------------------------------------------------------
                  Card(
                    color: Theme.of(context).primaryColorLight,
                    child: ListTile(
                      leading: CircleAvatar(
                        child: Icon(Icons.person),
                      ),
                      title: Text(widget.sess.NamaUser,
                          style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              color: Theme.of(context).primaryColor)),
                      subtitle: Text(
                          DateFormat("dd/MM/yyyy hh:mm:ss").format(_tanggal!),
                          style:
                              TextStyle(fontSize: 12, color: Colors.black54)),
                    ),
                  ),
                  ListTile(
                    leading: Icon(Icons.qr_code),
                    title: Text(
                      "Check Point",
                      style: TextStyle(color: Theme.of(context).primaryColor),
                    ),
                    subtitle: Text(
                      widget.NamaCheckPoint,
                      style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: Colors.red),
                    ),
                  ),

                  ListTile(
                    leading: Icon(Icons.location_pin),
                    title: Text(
                      "Lokasi",
                      style: TextStyle(color: Theme.of(context).primaryColor),
                    ),
                    subtitle: _currentPosition == null
                        ? Text("Baca Informasi Lokasi...")
                        : Text(
                            "${_currentPosition!.latitude}, ${_currentPosition!.longitude}",
                            style: TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.bold,
                                color: Colors.black),
                          ),
                    trailing: IconButton(
                      icon: Icon(Icons.refresh),
                      onPressed: () {
                        // _currentPosition = Position();
                        setState(() {});
                        // _getLocation();
                        _getCurrentPosition();
                        setState(() {});
                      },
                    ),
                  ),

                  ListTile(
                    leading: Icon(Icons.photo),
                    title: Text(
                      "Photo",
                      style: TextStyle(color: Theme.of(context).primaryColor),
                    ),
                    subtitle: imageFile == null
                        ? Container(
                            child: Text("Ambil Photo"),
                          )
                        : Card(
                            child: Padding(
                            padding: EdgeInsets.all(this.widget.sess.width * 2),
                            child: Image.file(File(imageFile!.path)),
                          )),
                    trailing: IconButton(
                      icon: Icon(Icons.camera_alt),
                      onPressed: () {
                        _openCamera(context);
                      },
                    ),
                  ),

                  ListTile(
                    leading: Icon(Icons.subject),
                    title: TextField(
                      controller: _catatan,
                      minLines: 4,
                      maxLines: 15,
                      decoration: InputDecoration(
                        border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(10)),
                      ),
                      onChanged: (_) {
                        setState(() {});
                      },
                    ),
                  ),
                ],
              ),
      ),
    );
  }
}