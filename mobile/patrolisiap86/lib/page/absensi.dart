import 'dart:convert';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:geolocator/geolocator.dart';
import 'package:image_picker/image_picker.dart';
import 'package:mobilepatrol/general/dialog.dart';
import 'package:mobilepatrol/general/session.dart';
import 'package:mobilepatrol/models/absensi.dart';
import 'package:mobilepatrol/models/shift.dart';
import 'package:flutter_face_api/face_api.dart' as Regula;

class FormAbsensi extends StatefulWidget {
  final session sess;
  final String shift;
  const FormAbsensi(this.sess, this.shift, {super.key});

  @override
  _FormAbsensi createState() => _FormAbsensi();
}

class _FormAbsensi extends State<FormAbsensi> {
  final GlobalKey<State> _keyLoader = GlobalKey<State>();

  var image1 = Regula.MatchFacesImage();
  var image2 = Regula.MatchFacesImage();

  List ? dataJadwal= [];
  List ? dataAbsen = [];
  List ? dataKaryawan = [];

  bool isMatch = false;

  Position? _currentPosition;

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
    // showLoadingDialog(context, _keyLoader, info: "Geting Current Location");

    final hasPermission = await _handleLocationPermission();
    if (!hasPermission) return;
    await Geolocator.getCurrentPosition(desiredAccuracy: LocationAccuracy.high).then((Position position) {
      // Navigator.of(context, rootNavigator: true).pop();
      setState(() => _currentPosition = position);
      // _setEnableCommand();
    }).catchError((e) {
      // Navigator.of(context, rootNavigator: true).pop();
      debugPrint(e);
    });
  }

  void _getLocation() async {
    Position position = await Geolocator.getCurrentPosition(
        desiredAccuracy: LocationAccuracy.high);
  }

  Future<Map>_getJadwal() async{
    String nowDate = "${DateTime.now().year}-${DateTime.now().month}-${DateTime.now().day}";
    Map oParam(){
      return {
        'TglAwal'   : dataAbsen!.isEmpty ? nowDate : dataAbsen![0]["Checkin"] ,
        'TglAkhir'  : nowDate,
        'id'        : '',
        'RecordOwnerID' : widget.sess.RecordOwnerID,
        'NIK'       : widget.sess.KodeUser,
        'source'    : "mobile"
      };
    }

    print(oParam());
    // await Mod_Shift(this.widget.sess, oParam()).getJadwal().then((value) {
    //   if(value["data"].length == 0){
    //     messageBox(context: context, title: "Informasi", message: "Data Jadwal Belum dibuat");
    //     Navigator.of(context).pop();
    //   }
    // });

    var temp = await Mod_Shift(widget.sess, oParam()).getJadwal();

    // print(temp);
    
    return temp;
  
  }

  Future<Map>_getAbsen() async{
    int monthLength = (DateTime.now().month.toString()).length;
    int dayLength = (DateTime.now().day.toString()).length;
    int hourLength = (DateTime.now().hour.toString()).length;
    int minLength = (DateTime.now().minute.toString()).length;
    int secLength = (DateTime.now().second.toString()).length;
    
    String nowDate = "${DateTime.now().year}-${monthLength == 1 ? "0${DateTime.now().month}" : DateTime.now().month.toString()}-${dayLength == 1 ? "0${DateTime.now().day}" : DateTime.now().day.toString()} ${hourLength == 1 ? "0${DateTime.now().hour}": DateTime.now().hour.toString()}:${minLength == 1 ? "0${DateTime.now().minute}":DateTime.now().minute.toString()}:${DateTime.now().second}";
    Map oParam(){
      return {
        'KodeLokasi'    : widget.sess.LocationID.toString(),
        'RecordOwnerID' : widget.sess.RecordOwnerID,
        'KodeKaryawan'  : widget.sess.KodeUser,
        'Tanggal'       : nowDate
      };
    }
    var temp = await Mod_Absensi(widget.sess, oParam()).Read();
    return temp;
  }
  

  Future<void> initPlatformState() async {
    Regula.FaceSDK.init().then((json) {
      var response = jsonDecode(json);
      if (!response["success"]) {
        print("Init failed: ");
        print(json);
      }
    });
  }

  _fetchJadwal() async{
    var temp = await _getJadwal();
    // print(temp["data"].length);
    dataJadwal = temp["data"];
    // setState(() => {});
  }

  _fetchAbsen() async{
    var temp = await _getAbsen();
    dataAbsen = temp["data"];
    setState(() => {});
  }

  _fetchData() async{
    var temAbsen = await _getAbsen();
    dataAbsen = temAbsen["data"];
    setState(() => {});

    var temJadwal = await _getJadwal();
    dataJadwal = temJadwal["data"];
    setState(() => {});
  }

  Future<void> matchFaces(String formType) async {
    showLoadingDialog(context, _keyLoader, info: "Begin Scaning");
    _getCurrentPosition();

    print("Processing00");
    print(image1.bitmap);
    print(image2.bitmap);

    if(formType == "in"){
        Map dataParam(){
          return {
            "RecordOwnerID" : widget.sess.RecordOwnerID,
            "LocationID"    : widget.sess.LocationID.toString(),
            "KodeKaryawan"  : widget.sess.KodeUser,
            "KoordinatIN"   : _currentPosition == null ? "" : "${_currentPosition!.latitude},${_currentPosition!.longitude}",
            "ImageIN"       : image2.bitmap,
            "Tanggal"       : "${DateTime.now().year}-${DateTime.now().month}-${DateTime.now().day}",
            "Shift"         : "",
            "Checkin"       : DateTime.now().toString(),
            "CreatedOn"     : DateTime.now().toString(),
            "formMode"      : formType
          };
        }
        print(dataParam);

        await Mod_Absensi(widget.sess, dataParam()).Create().then((value) async{
          if(value["success"]){
            Navigator.of(context, rootNavigator: true).pop();
            await messageBox(
              context: context, 
              title: "Infomasi", 
              message: "Berhasil Checkin"
            );
            Navigator.of(context).pop();
          }
          else{
            Navigator.of(context, rootNavigator: true).pop();
            await messageBox(
              context: context, 
              title: "Infomasi", 
              message: value["message"]
            );
          }
        });
      }
      else if(formType == "out"){
        print(image2.bitmap);
        Map dataParam(){
          return {
            "id"            : dataAbsen![0]["id"].toString(),
            "RecordOwnerID" : widget.sess.RecordOwnerID,
            "LocationID"    : widget.sess.LocationID.toString(),
            "KodeKaryawan"  : widget.sess.KodeUser,
            "KoordinatOUT"  : _currentPosition == null ? "" : "${_currentPosition!.latitude},${_currentPosition!.longitude}",
            "ImageOUT"      : image2.bitmap,
            "Shift"         : "",
            "CheckOut"      : DateTime.now().toString(),
            "UpdatedOn"     : DateTime.now().toString(),
            "formMode"      : formType
          };
        }

        print("id: " + dataAbsen![0]["id"].toString() + ", type :" + formType);

        await Mod_Absensi(widget.sess, dataParam()).Create().then((value) async{
          if(value["success"]){
            Navigator.of(context, rootNavigator: true).pop();
            await messageBox(
              context: context, 
              title: "Infomasi", 
              message: "Berhasil Checkout"
            );
            Navigator.of(context).pop();
          }
          else{
            Navigator.of(context, rootNavigator: true).pop();
            await messageBox(
              context: context, 
              title: "Infomasi", 
              message: value["message"]
            );
          }
        });
      }
      else{
          Navigator.of(context, rootNavigator: true).pop();
          messageBox(context: context, title: "info", message: "Invalid Form Type");
      }
    // if (image1.bitmap == null ||
    //     image1.bitmap == "" ||
    //     image2.bitmap == null ||
    //     image2.bitmap == ""){};
    // setState(() => _similarity = "Processing...");
    // var request = new Regula.MatchFacesRequest();
    // request.images = [image1, image2];
    // Regula.FaceSDK.matchFaces(jsonEncode(request)).then((value) {
    //   var response = Regula.MatchFacesResponse.fromJson(json.decode(value));
    //   print(response);
    //   Regula.FaceSDK.matchFacesSimilarityThresholdSplit(jsonEncode(response!.results), 0.9).then((str) async {
    //     var split = Regula.MatchFacesSimilarityThresholdSplit.fromJson(json.decode(str));
    //     print("printing "+ (split!.matchedFaces[0]!.similarity! * 100 > 95).toString());
    //     if(split.matchedFaces.length > 0){
    //       if(split.matchedFaces[0]!.similarity! * 100 > 95){
    //         // return true;
    //         // isMatch = true;

            

            
    //       }
    //       else{
    //         Navigator.of(context, rootNavigator: true).pop();
    //         await messageBox(
    //           context: context, 
    //           title: "Infomasi", 
    //           message: "Face not Match"
    //         );
    //       }
    //     }
    //     else{
    //       Navigator.of(context, rootNavigator: true).pop();
    //       await messageBox(
    //         context: context, 
    //         title: "Infomasi", 
    //         message: "Face not Found"
    //       );
    //     }
    //   }).onError((error, stackTrace) async{
    //     print("ini ada error : " + error.toString());
    //     Navigator.of(context, rootNavigator: true).pop();
    //     await messageBox(
    //         context: context, 
    //         title: "Infomasi", 
    //         message: "Wajah tidak ada disistem"
    //       );
    //   });
    // });
  }

  @override
  void initState() {
    _fetchData();
    // print(dataJadwal);
    super.initState();
    initPlatformState();

    const EventChannel('flutter_face_api/event/video_encoder_completion').receiveBroadcastStream().listen((event) {
      var completion = Regula.VideoEncoderCompletion.fromJson(json.decode(event))!;
      print("VideoEncoderCompletion:");
      print("    success:  ${completion.success}");
      print("    transactionId:  ${completion.transactionId}");
    });
    const EventChannel('flutter_face_api/event/onCustomButtonTappedEvent').receiveBroadcastStream().listen((event) {
      print("Pressed button with id: $event");
    });
    const EventChannel('flutter_face_api/event/livenessNotification').receiveBroadcastStream().listen((event) {
      var notification = Regula.LivenessNotification.fromJson(json.decode(event));
      print("LivenessProcessStatus: ${notification!.status}");
    });
  }

  @override
  void dispose() {
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    // if(dataJadwal!.length == 0){
    //   // print("xxxxxx");
    //   messageBox(context: context, title: "Informasi", message: "Data Jadwal Belum dibuat");
    //   Navigator.of(context).pop();
    // }
    return Scaffold(
      appBar: AppBar(
        backgroundColor: Theme.of(context).primaryColor,
        title: const Text(
          "Absensi",
          style: TextStyle(
            color: Colors.white
          ),
        ),
        actions: [
          Padding(
            padding: const EdgeInsets.all(5),
            child: Text(
              "V ${widget.sess.appVersion}",
              style: const TextStyle(
                color: Colors.white
              ),
            ),
          )
        ],
      ),
      bottomNavigationBar: BottomAppBar(
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Padding(
              padding: EdgeInsets.fromLTRB(widget.sess.width * 2,
                  widget.sess.width * 2, 0, widget.sess.width * 2),
              child: SizedBox(
                width: widget.sess.width * 40,
                child: ElevatedButton(
                  style: ButtonStyle(
                      shape: MaterialStateProperty.all<RoundedRectangleBorder>(
                          RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(18.0),
                      )),
                      backgroundColor: MaterialStateProperty.all(Colors.green)),
                  onPressed: dataAbsen!.isNotEmpty ? null :() async{
                    final ImagePicker picker = ImagePicker();

                    await picker.pickImage(
                      source: ImageSource.camera, 
                      maxHeight: 600,
                      preferredCameraDevice: CameraDevice.front
                    ).then((value) {
                      // print(value);
                      if(value != null) {
                        // var tempImage1 = base64Decode(dataJadwal![0]["Image"].toString().replaceAll("data:image/jpeg;base64,", ""));
                        // var tempImage2 = base64Decode(File(value!.path).readAsStringSync());
                        File? imageFile;
                        imageFile = File(value!.path);
                        final bites = imageFile.readAsBytesSync();
                        image2.bitmap = base64Encode(bites);
                        image2.imageType = Regula.ImageType.PRINTED;
                      
                        image1.bitmap = base64Encode(bites);
                        image1.imageType = Regula.ImageType.PRINTED;
                        

                        matchFaces("in");
                      }
                  });
                    // Regula.FaceSDK.presentFaceCaptureActivity().then((result) async {
                    //   var response = Regula.FaceCaptureResponse.fromJson(json.decode(result))!;
                    //   if (response.image != null && response.image!.bitmap != null){
                    //     showLoadingDialog(context, _keyLoader, info: "Begin Scaning");

                    //     // var img1 = Image.memory(dataJadwal![0]["Image"]);
                    //     var tempImage1 = base64Decode(dataJadwal![0]["Image"].toString().replaceAll("data:image/jpeg;base64,", ""));
                    //     var tempImage2 = base64Decode(response.image!.bitmap!.replaceAll("\n", ""));
                    //     // var img2 = Image.memory(tempImage2);

                    //     image1.bitmap = base64Encode(tempImage1);
                    //     image1.imageType = Regula.ImageType.PRINTED;
                    //     image2.bitmap = base64Encode(tempImage2);
                    //     image2.imageType = Regula.ImageType.LIVE;
                    //     setState(() {
                          
                    //     });


                    //     // print(image1.bitmap);
                    //     await matchFaces("in");

                    //   }
                    // });

                    // if(isMatch){
                    //   Navigator.of(context, rootNavigator: true).pop();
                    //   messageBox(context: context, title: "info", message: "Face Match");
                    // }
                    // else{
                    //   print("emty object");
                    // }
                  },
                  child: const Text("Check In"),
                ),
              ),
            ),
            Padding(
              padding: EdgeInsets.fromLTRB(
                  widget.sess.width * 2,
                  widget.sess.width * 2,
                  widget.sess.width * 2,
                  widget.sess.width * 2),
              child: SizedBox(
                width: widget.sess.width * 40,
                child: ElevatedButton(
                  style: ButtonStyle(
                      shape: MaterialStateProperty.all<RoundedRectangleBorder>(
                          RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(18.0),
                      )),
                      backgroundColor: MaterialStateProperty.all(Colors.red)),
                  onPressed: dataAbsen!.isEmpty ? null : dataAbsen![0]["ImageOUT"] != "" ? null : () async{
                    final ImagePicker picker = ImagePicker();

                    await picker.pickImage(
                      source: ImageSource.camera, 
                      maxHeight: 600,
                      preferredCameraDevice: CameraDevice.front
                    ).then((value) {
                      // showLoadingDialog(context, _keyLoader, info: "Begin Scaning");
                      if(value != null ){
                        // var tempImage1 = base64Decode(dataJadwal![0]["Image"].toString().replaceAll("data:image/jpeg;base64,", ""));
                        // var tempImage2 = base64Decode(File(value!.path).readAsStringSync());
                        File? imageFile;
                        imageFile = File(value!.path);
                        final bites = imageFile.readAsBytesSync();
                        image2.bitmap = base64Encode(bites);
                        image2.imageType = Regula.ImageType.PRINTED;
                      
                        image1.bitmap = base64Encode(bites);
                        image1.imageType = Regula.ImageType.PRINTED;
                        

                        matchFaces("out");
                      }
                  });
                    // Regula.FaceSDK.presentFaceCaptureActivity().then((result) async {
                    //   var response = Regula.FaceCaptureResponse.fromJson(json.decode(result))!;
                    //   if (response.image != null && response.image!.bitmap != null){
                    //     showLoadingDialog(context, _keyLoader, info: "Begin Scaning");

                    //     // var img1 = Image.memory(dataJadwal![0]["Image"]);
                    //     var tempImage1 = base64Decode(dataJadwal![0]["Image"].toString().replaceAll("data:image/jpeg;base64,", ""));
                    //     var tempImage2 = base64Decode(response.image!.bitmap!.replaceAll("\n", ""));
                    //     // var img2 = Image.memory(tempImage2);

                    //     image1.bitmap = base64Encode(tempImage1);
                    //     image1.imageType = Regula.ImageType.PRINTED;
                    //     image2.bitmap = base64Encode(tempImage2);
                    //     image2.imageType = Regula.ImageType.LIVE;
                    //     setState(() {
                          
                    //     });

                    //     await matchFaces("out");

                    //   }
                    // });
                  },
                  child: const Text("Check Out"),
                ),
              ),
            )
          ],
        )
      ),
      body: Column(
        children: [
          Padding(
            padding: EdgeInsets.all(widget.sess.width * 2),
            child: SizedBox(
              width: double.infinity,
              height: widget.sess.hight * 11,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.start,
                children: [
                  Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const SizedBox(height: 1),
                      Container(
                          width: widget.sess.width * 30,
                          height: widget.sess.hight * 10,
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(10.0),
                            color: Theme.of(context).primaryColor,
                          ),
                          child: Center(
                            child: Text(
                              dataJadwal!.isEmpty ? "" : "Shift : ${dataJadwal![0]["NamaShift"].toString().toUpperCase()}",
                              style: TextStyle(
                                  fontSize: widget.sess.width * 4,
                                  color: Colors.white,
                                  fontWeight: FontWeight.bold),
                            ),
                          )),
                    ],
                  ),
                  Padding(
                      padding: EdgeInsets.all(widget.sess.width * 2),
                      child: Container(
                        width: widget.sess.width * 50,
                        height: widget.sess.hight * 10,
                        alignment: Alignment.topLeft,
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Align(
                              alignment: Alignment.topLeft,
                              child: Text(
                                widget.sess.KodeUser,
                                style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    fontSize: widget.sess.width * 4,
                                    color: Theme.of(context).primaryColor),
                              ),
                            ),
                            Align(
                              alignment: Alignment.topLeft,
                              child: Text(
                                widget.sess.NamaUser,
                                style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    fontSize: widget.sess.width * 5,
                                    color: Theme.of(context).primaryColor),
                              ),
                            ),
                          ],
                        ),
                      ))
                ],
              ),
            ),
          ),
          Padding(
            padding: EdgeInsets.all(widget.sess.width * 2),
            child: SizedBox(
              width: double.infinity,
              height: widget.sess.hight * 30,
              // color: Colors.amber,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.start,
                children: [
                  SizedBox(
                    width: widget.sess.width * 40,
                    height: widget.sess.hight * 30,
                    // color: Colors.black,
                    child: dataAbsen!.isNotEmpty ? dataAbsen![0]["ImageIN"] == "" ? Image.asset("assets/portrait.png") : Image.network("${widget.sess.server}Assets/images/Absensi/" + dataAbsen![0]["ImageIN"]) : Image.asset("assets/portrait.png"),
                    // child: dataAbsen!.length == 0 ? Image.asset("assets/portrait.png") : Image.memory(jsonDecode(image1.bitmap.toString())),
                  ),
                  Padding(
                    padding: EdgeInsets.only(
                        left: widget.sess.width * 2,
                        right: widget.sess.width * 2),
                    child: SizedBox(
                        width: widget.sess.width * 50,
                        height: widget.sess.hight * 30,
                        // color: Colors.blue,
                        child: Column(
                          children: [
                            Table(
                              columnWidths: {
                                0: FlexColumnWidth(widget.sess.width * 25),
                                1: FlexColumnWidth(widget.sess.width * 2),
                                2: FlexColumnWidth(widget.sess.width * 35)
                              },
                              // border: TableBorder.all(color: Colors.green, width: 1.5),
                              children: [
                                TableRow(
                                  children: [
                                    Text(
                                      "NIK",
                                      style: TextStyle( fontSize: widget.sess.width * 4),
                                    ),
                                    Text(":",
                                      style: TextStyle( fontSize:widget.sess.width * 4)
                                    ),
                                    Text(widget.sess.KodeUser,style: TextStyle(fontSize: widget.sess.width * 4)
                                    )
                                  ]
                                ),
                                TableRow(children: [
                                  Text("Nama",
                                      style: TextStyle(
                                          fontSize:
                                              widget.sess.width * 4)),
                                  Text(":",
                                      style: TextStyle(
                                          fontSize:
                                              widget.sess.width * 4)),
                                  Text(widget.sess.NamaUser,
                                      style: TextStyle(
                                          fontSize: widget.sess.width * 4))
                                ]),
                                TableRow(children: [
                                  Text("Tanggal",
                                      style: TextStyle(
                                          fontSize:
                                              widget.sess.width * 4)),
                                  Text(":",
                                      style: TextStyle(
                                          fontSize:
                                              widget.sess.width * 4)),
                                  Text(dataAbsen!.isEmpty ? "" : dataAbsen![0]["Checkin"].toString().split(" ")[0],
                                      style: TextStyle(
                                          fontSize: widget.sess.width * 4))
                                ]),
                                TableRow(children: [
                                  Text("Jam",
                                      style: TextStyle(
                                          fontSize:
                                              widget.sess.width * 4)),
                                  Text(":",
                                      style: TextStyle(
                                          fontSize:
                                              widget.sess.width * 4)),
                                  Text(dataAbsen!.isEmpty ? "" : dataAbsen![0]["Checkin"].toString().split(" ")[1].split(".")[0],
                                      style: TextStyle(
                                          fontSize: widget.sess.width * 4))
                                ])
                              ],
                            ),
                            Padding(
                              padding: EdgeInsets.only(
                                left: widget.sess.width * 2,
                                right: widget.sess.width * 2,
                                top: widget.sess.width * 2,
                              ),
                              child: SizedBox(
                                width: widget.sess.width * 55,
                                height: widget.sess.hight * 10,
                                // color: Colors.black,
                                child: Center(
                                  child: Text(
                                    dataAbsen!.isNotEmpty ? "CHECKIN" : "",
                                    style: TextStyle(
                                        fontSize: widget.sess.width * 8,
                                        fontWeight: FontWeight.bold,
                                        color: Colors.green),
                                  ),
                                ),
                              ),
                            )
                          ],
                        )),
                  )
                ],
              ),
            ),
          ),
          Padding(
            padding: EdgeInsets.all(widget.sess.width * 2),
            child: SizedBox(
              width: double.infinity,
              height: widget.sess.hight * 30,
              // color: Colors.amber,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.start,
                children: [
                  SizedBox(
                    width: widget.sess.width * 40,
                    height: widget.sess.hight * 30,
                    // color: Colors.black,
                    child: dataAbsen!.isNotEmpty ? dataAbsen![0]["ImageOUT"] == "" ? Image.asset("assets/portrait.png") : Image.network("${widget.sess.server}Assets/images/Absensi/" + dataAbsen![0]["ImageOUT"]):Image.asset("assets/portrait.png") ,
                  ),
                  Padding(
                    padding: EdgeInsets.only(
                        left: widget.sess.width * 2,
                        right: widget.sess.width * 2),
                    child: SizedBox(
                        width: widget.sess.width * 50,
                        height: widget.sess.hight * 30,
                        // color: Colors.blue,
                        child: Column(
                          children: [
                            Table(
                              columnWidths: {
                                0: FlexColumnWidth(widget.sess.width * 25),
                                1: FlexColumnWidth(widget.sess.width * 2),
                                2: FlexColumnWidth(widget.sess.width * 35)
                              },
                              // border: TableBorder.all(color: Colors.green, width: 1.5),
                              children: [
                                TableRow(children: [
                                  Text(
                                    "NIK",
                                    style: TextStyle(
                                        fontSize: widget.sess.width * 4),
                                  ),
                                  Text(":",
                                      style: TextStyle(
                                          fontSize:
                                              widget.sess.width * 4)),
                                  Text(widget.sess.KodeUser,
                                      style: TextStyle(
                                          fontSize: widget.sess.width * 4))
                                ]),
                                TableRow(children: [
                                  Text("Nama",
                                      style: TextStyle(
                                          fontSize:
                                              widget.sess.width * 4)),
                                  Text(":",
                                      style: TextStyle(
                                          fontSize:
                                              widget.sess.width * 4)),
                                  Text(widget.sess.NamaUser,
                                      style: TextStyle(
                                          fontSize: widget.sess.width * 4))
                                ]),
                                TableRow(children: [
                                  Text("Tanggal",
                                      style: TextStyle(
                                          fontSize:
                                              widget.sess.width * 4)),
                                  Text(":",
                                      style: TextStyle(
                                          fontSize:
                                              widget.sess.width * 4)),
                                  Text(dataAbsen!.isNotEmpty ? dataAbsen![0]["CheckOut"].toString() == "0000-00-00 00:00:00.000000" ? "" : dataAbsen![0]["CheckOut"].toString().split(" ")[0] :"",
                                      style: TextStyle(
                                          fontSize: widget.sess.width * 4))
                                ]),
                                TableRow(children: [
                                  Text("Jam",
                                      style: TextStyle(
                                          fontSize:
                                              widget.sess.width * 4)),
                                  Text(":",
                                      style: TextStyle(
                                          fontSize:
                                              widget.sess.width * 4)),
                                  Text(dataAbsen!.isNotEmpty ? dataAbsen![0]["CheckOut"].toString() == "0000-00-00 00:00:00.000000" ? "" : dataAbsen![0]["CheckOut"].toString().split(" ")[1].split(".")[0]:"",
                                      style: TextStyle(
                                          fontSize: widget.sess.width * 4))
                                ])
                              ],
                            ),
                            Padding(
                              padding: EdgeInsets.only(
                                left: widget.sess.width * 2,
                                right: widget.sess.width * 2,
                                top: widget.sess.width * 2,
                              ),
                              child: SizedBox(
                                width: widget.sess.width * 55,
                                height: widget.sess.hight * 10,
                                // color: Colors.black,
                                child: Center(
                                  child: Text(
                                    dataAbsen!.isNotEmpty ?dataAbsen![0]["CheckOut"].toString() != "0000-00-00 00:00:00.000000" ? "CHECKOUT" : "" : "",
                                    style: TextStyle(
                                        fontSize: widget.sess.width * 8,
                                        fontWeight: FontWeight.bold,
                                        color: Colors.red),
                                  ),
                                ),
                              ),
                            )
                          ],
                        )),
                  )
                ],
              ),
            ),
          )
        ],
      )
    );
  }
}