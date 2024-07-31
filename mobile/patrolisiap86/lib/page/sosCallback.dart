import 'dart:convert';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:image_picker/image_picker.dart';
import 'package:mobilepatrol/general/session.dart';
import 'package:mobilepatrol/models/sos.dart';
import 'package:photo_view/photo_view.dart';
import 'package:url_launcher/url_launcher.dart';

class sosCallBack extends StatefulWidget {
  final session? sess;
  final String? uID;
  const sosCallBack(this.sess, this.uID, {super.key});

  @override
  _sosCallBackState createState() => _sosCallBackState();
}

class _sosCallBackState extends State<sosCallBack> {
  final GlobalKey<State> _keyLoader = GlobalKey<State>();

  Position? _currentPosition;

  LatLng ? _center;

  late GoogleMapController mapController;


  File? imageFile_1;
  String image64_1 = "";

  File? imageFile_2;
  String image64_2 = "";

  File? imageFile_3;
  String image64_3 = "";

  List? pathext;
  String? extentionPath_1 = "";
  String? extentionPath_2 = "";
  String? extentionPath_3 = "";

  final bool _isSaved = false;

  final TextEditingController _detailPesan = TextEditingController();
  _openCamera_1(BuildContext context) async {
    final ImagePicker picker = ImagePicker();

    await picker.pickImage(source: ImageSource.camera, maxHeight: 600).then((value) {
      setState(() {
        imageFile_1 = File(value!.path);
        pathext = imageFile_1!.uri.toString().split("/");
        extentionPath_1 = pathext![pathext!.length - 1].toString();

        if (imageFile_1 != null) {
          final bites = imageFile_1!.readAsBytesSync();
          image64_1 = base64Encode(bites);
          print(image64_1);
        }
      });
      // Navigator.of(context).pop();
    });
  }

  _openCamera_2(BuildContext context) async {
    final ImagePicker picker = ImagePicker();

    await picker.pickImage(source: ImageSource.camera, maxHeight: 600).then((value) {
      setState(() {
        imageFile_2 = File(value!.path);
        pathext = imageFile_2!.uri.toString().split("/");
        extentionPath_2 = pathext![pathext!.length - 1].toString();

        if (imageFile_2 != null) {
          final bites = imageFile_2!.readAsBytesSync();
          image64_2 = base64Encode(bites);
        }
      });
      // Navigator.of(context).pop();
    });
  }
  
  _openCamera_3(BuildContext context) async {
    final ImagePicker picker = ImagePicker();

    await picker.pickImage(source: ImageSource.camera, maxHeight: 600).then((value) {
      setState(() {
        imageFile_3 = File(value!.path);
        pathext = imageFile_3!.uri.toString().split("/");
        extentionPath_3 = pathext![pathext!.length - 1].toString();

        if (imageFile_3 != null) {
          final bites = imageFile_3!.readAsBytesSync();
          image64_3 = base64Encode(bites);
        }
      });
      // Navigator.of(context).pop();
    });
  }

  void _onMapCreated(GoogleMapController controller) {
      mapController = controller;
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
    await Geolocator.getCurrentPosition(desiredAccuracy: LocationAccuracy.high).then((Position position) {
      setState(() {
        _currentPosition = position;
      });
    }).catchError((e) {
      debugPrint(e);
    });
  }
  void _launchMapsUrl(double lat, double lon) async {
    final url = 'https://www.google.com/maps/search/?api=1&query=$lat,$lon';
    if (await canLaunch(url)) {
      await launch(url);
    } else {
      throw 'Could not launch $url';
    }
  }

  @override
  void initState() {
    _getCurrentPosition();
    super.initState();
  }

  @override
  void dispose() {
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    Map oParam(){
      return {
        "id"              : widget.uID,
        'RecordOwnerID'   : widget.sess!.RecordOwnerID,
        'KodeLokasi'      : widget.sess!.LocationID.toString(),
      };
    }
    return Scaffold(
      appBar: AppBar(
        title: Text(
          "data",
        ),
        backgroundColor: Theme.of(context).primaryColor,
        foregroundColor: Colors.white,
      ),
      body: FutureBuilder(
        future: Mod_SOS(widget.sess, oParam()).read(),
        builder: (context, snapshot){
          if(snapshot.hasData){
            if(snapshot.data!["data"].length > 0){
              String _Koordinat = "-7.4949969,110.8285801";
              if (snapshot.data!["data"][0]["Koordinat"] != "") {
                _Koordinat = snapshot.data!["data"][0]["Koordinat"];
              }
              List<String> position = _Koordinat.toString().split(",");
              _detailPesan.text = snapshot.data!["data"][0]["Comment"];
              print("${widget.sess!.server}Assets/images/SOS/"+snapshot.data!["data"][0]["Image1"]);
              return SizedBox(
                width: double.infinity,
                height: widget.sess!.hight * 100,
                child: ListView(
                  // mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    SizedBox(
                      width: double.infinity,
                      height: widget.sess!.hight * 30,
                      // color:  Colors.amber,
                      child: _currentPosition != null ? 
                      GoogleMap(
                        onMapCreated: _onMapCreated,
                        initialCameraPosition: CameraPosition(
                          target: LatLng(double.parse(position[0]), double.parse(position[1])),
                          zoom: 18,
                        ),
                        markers: {
                          Marker(
                            markerId: const MarkerId("SOS Value"),
                            position: LatLng(double.parse(position[0]), double.parse(position[1])),
                            infoWindow: InfoWindow(
                              title: "SOS",
                              snippet: "Klik untuk membuka di Google Maps",
                              onTap: (){
                                _launchMapsUrl(double.parse(position[0]), double.parse(position[1]));
                              }
                            )
                          )
                        }
                      ):Container(),
                    ),
                    SizedBox(
                      width: double.infinity,
                      height: widget.sess!.hight * 3,
                      child: const Center(
                        child: Text("Detail Gambar"),
                      ),
                    ),
                    SizedBox(
                      width: double.infinity,
                      height: widget.sess!.hight * 25,
                      // color: Colors.black,
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Padding(
                            padding: EdgeInsets.all(widget.sess!.width * 1),
                            child: GestureDetector(
                              onTap: snapshot.data!["data"][0]["Image1"] == "" ? null : (){
                                // _openCamera_1(context);
                                PhotoView(
                                  imageProvider: NetworkImage("${widget.sess!.server}Assets/images/SOS/"+snapshot.data!["data"][0]["Image1"])
                                );
                              },
                              child: SizedBox(
                                width: widget.sess!.width * 30,
                                height: widget.sess!.hight * 25,
                                child: Card(
                                  child: snapshot.data!["data"][0]["Image1"] != "" 
                                    ? Image.network("${widget.sess!.server}Assets/images/SOS/"+snapshot.data!["data"][0]["Image1"]) 
                                    : const Center(
                                        child: Text("No Image"),
                                      )
                                ),
                              ),
                            ),
                          ),
                          Padding(
                            padding: EdgeInsets.all(widget.sess!.width * 1),
                            child: GestureDetector(
                              onTap: snapshot.data!["data"][0]["Image2"] == "" ? null : (){
                                // _openCamera_2(context);
                                PhotoView(
                                  imageProvider: NetworkImage("${widget.sess!.server}Assets/images/SOS/"+snapshot.data!["data"][0]["Image2"])
                                );
                              },
                              child: SizedBox(
                                width: widget.sess!.width * 30,
                                height: widget.sess!.hight * 25,
                                child: Card(
                                  child: snapshot.data!["data"][0]["Image2"] != "" 
                                    ? Image.network("${widget.sess!.server}Assets/images/SOS/"+snapshot.data!["data"][0]["Image2"]) 
                                    : const Center(
                                        child: Text("No Image"),
                                      )
                                ),
                              ),
                            )
                          ),
                          Padding(
                            padding: EdgeInsets.all(widget.sess!.width * 1),
                            child: GestureDetector(
                              onTap: snapshot.data!["data"][0]["Image3"] == "" ? null : (){
                                // _openCamera_3(context);
                                PhotoView(
                                  imageProvider: NetworkImage("${widget.sess!.server}Assets/images/SOS/"+snapshot.data!["data"][0]["Image3"])
                                );
                              },
                              child: SizedBox(
                                width: widget.sess!.width * 30,
                                height: widget.sess!.hight * 25,
                                child: Card(
                                  child: snapshot.data!["data"][0]["Image3"] != "" 
                                    ? Image.network("${widget.sess!.server}Assets/images/SOS/"+snapshot.data!["data"][0]["Image3"]) 
                                    : const Center(
                                        child: Text("No Image"),
                                      )
                                ),
                              ),
                            )
                          )
                        ],
                      ),
                    ),
                    SizedBox(
                      width: double.infinity,
                      height: widget.sess!.hight * 15,
                      child: Padding(
                        padding: EdgeInsets.all(widget.sess!.width * 2),
                        child: TextField(
                          // autofocus: true,
                          controller: _detailPesan,
                          textAlign: TextAlign.start,
                          textInputAction: TextInputAction.done,
                          maxLines: 3,
                          readOnly: true,
                          decoration: InputDecoration(
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                            labelText: "Detail Informasi",
                            labelStyle: TextStyle(
                              color: Theme.of(context).primaryColorDark,
                              fontWeight: FontWeight.bold,
                              // fontSize: this.widget.sess!.width * 10
                            )
                          ),
                        ),
                      ),
                    )
                  ],
                ),
              );
            }
            else{
              return Container();
            }
            
          }
          else{
            return Container();
          }
        }
      )
    );
  }
}
