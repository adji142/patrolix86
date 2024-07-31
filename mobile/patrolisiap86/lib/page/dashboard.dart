import 'dart:async';
import 'dart:math';

import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:intl/intl.dart';
import 'package:mobilepatrol/general/dialog.dart';
import 'package:mobilepatrol/general/notification_service.dart';
import 'package:mobilepatrol/general/session.dart';
import 'package:mobilepatrol/main.dart';
import 'package:mobilepatrol/models/LocalDatabaseHelper.dart';
import 'package:mobilepatrol/models/patroli.dart';
import 'package:flutter_barcode_scanner/flutter_barcode_scanner.dart';
import 'package:mobilepatrol/models/sos.dart';
import 'package:mobilepatrol/page/FormCheckIn.dart';
import 'package:mobilepatrol/page/absensi.dart';
import 'package:mobilepatrol/page/dailyactivity.dart';
import 'package:mobilepatrol/page/guestlog.dart';
import 'package:mobilepatrol/page/sos.dart';
import 'package:mobilepatrol/page/sosList.dart';
import 'package:mobilepatrol/shared/sharedprefrence.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:uuid/uuid.dart';

class Dashboard extends StatefulWidget {
  final session? sess;
  final int? interval;
  const Dashboard(this.sess, {super.key, this.interval});

  @override
  _dashboardState createState() => _dashboardState();
}

class PushNotification {
  PushNotification({
    this.title,
    this.body,
  });
  String? title;
  String? body;
}

class _dashboardState extends State<Dashboard> {
  DateTime? _tanggal;
  String _kodeShift = "";
  int _idShift = -1;

  final Map _data = {};
  String _penyelesaian = "0 %";
  String _jumlahCP = "0 Check Point";

  final String _scanBarcode = 'Unknown';

  final f = NumberFormat("##0");

  bool _inSOS = false;

  bool _offlineMode = false;
  // Notifikasi
  late final FirebaseMessaging _messaging;
  int _notificationCount = 15;

  //Time Change
  //---------------------------------------------------------------------------
  DateTime? _timeChange(DateTime now) {
    // final DateTime now = DateTime.now();
    final String temp = DateFormat('jm').format(now).trim();
    final ampm = temp.substring(temp.length - 2, temp.length);

    String xShift = "";
    int shift = -1;

    // print(now.hour);
    print(widget.sess!.jadwalShift);
    if (widget.sess!.jadwalShift.isNotEmpty) {
      // print(this.widget.sess!.jadwalShift);
      for (var i = 0; i < widget.sess!.jadwalShift.length; i++) {
        var mulai = widget
            .sess!
            .jadwalShift[i]["MulaiBekerja"]
            .toString()
            .split(":");
        var selesai = widget
            .sess!
            .jadwalShift[i]["SelesaiBekerja"]
            .toString()
            .split(":");

        if (mulai.isNotEmpty) {
          DateTime jamMulai = DateTime.utc(
              now.year,
              now.month,
              now.day,
              int.parse(mulai[0]),
              int.parse(mulai[1]),
              int.parse(mulai[0].split(".")[0]));
          DateTime jamSelesai = DateTime.utc(
              now.year,
              now.month,
              widget.sess!.isGantiHari == 1 ? now.day + 1 : now.day,
              int.parse(selesai[0]),
              int.parse(selesai[1]),
              int.parse(selesai[0].split(".")[0]));

          print(jamMulai);

          if (now.isAfter(jamMulai.toLocal()) &&
              now.isBefore(jamSelesai.toLocal())) {
            xShift = widget
                .sess!
                .jadwalShift[i]["NamaShift"]
                .toString()
                .toUpperCase();
            shift = int.parse(widget.sess!.jadwalShift[i]["id"]);
          }

          // if (DateTime.utc(1900, 1, 1, int.parse(mulai[0]), int.parse(mulai[1]), int.parse(mulai[0].split(".")[0])).hour <= now.hour && DateTime.utc(1900, 1, 1, int.parse(selesai[0]), int.parse(selesai[1]), int.parse(selesai[0].split(".")[0])).hour >= now.hour) {
          //   xShift = this.widget.sess!.jadwalShift[i]["NamaShift"].toString();
          //   shift = int.parse(this.widget.sess!.jadwalShift[i]["id"]);
          //   // 22 < 07 && 22 > 15 -> false
          //   // 22 < 15 && 22 > 23 ->
          //   // 23 < 23 && 22 > 07
          // }
        }
      }
    }

    // if (ampm == "AM") {
    //   // AM
    //   //------------------------------------------------------------------------
    //   if (now.hour < DateTime.utc(1900, 1, 1, 7, 0, 0).hour) {
    //     shift = 3;
    //   } else {
    //     shift = 1;
    //   }
    //   //------------------------------------------------------------------------
    // } else {
    //   // PM
    //   //------------------------------------------------------------------------
    //   if (now.hour < DateTime.utc(1900, 1, 1, 15, 0, 0).hour) {
    //     shift = 1;
    //   } else if (now.hour >= DateTime.utc(1900, 1, 1, 15, 0, 0).hour &&
    //       now.hour < DateTime.utc(1900, 1, 1, 23, 0, 0).hour) {
    //     shift = 2;
    //   } else {
    //     shift = 3;
    //   }
    //   //------------------------------------------------------------------------
    // }
    // setState(() {
    //   // print(xShift);
    //   _kodeShift = xShift;
    //   _idShift = shift;
    //   _tanggal = now;
    // });
    return _tanggal;
  }
  //---------------------------------------------------------------------------

  Future<String> barcodeScan() async {
    String barcodeScanRes;
    // Platform messages may fail, so we use a try/catch PlatformException.
    try {
      barcodeScanRes = await FlutterBarcodeScanner.scanBarcode(
          '#ff6666', 'Cancel', true, ScanMode.QR);
      // print(barcodeScanRes);
      // return barcodeScanRes;
    } on PlatformException {
      barcodeScanRes = 'Failed to get platform version.';
    }
    // if (!mounted) return;
    // setState(() {
    //   _scanBarcode = barcodeScanRes;
    // });
    return barcodeScanRes;
  }

  void generateNotif() {
    Map oParamLokasi() {
      return {
        'id': widget.sess!.LocationID.toString(),
        'RecordOwnerID': widget.sess!.RecordOwnerID,
      };
    }

    var x = Mod_Patroli(widget.sess, Parameter: oParamLokasi())
        .readLokasi()
        .then((value) async {
      int interval = 0;

      // int.parse(value["data"][0]["IntervalPatroli"]) * 60;
      if (value["data"][0]["IntervalType"] == "DAY") {
        interval = int.parse(value["data"][0]["IntervalPatroli"]) * 1440;
      } else if (value["data"][0]["IntervalType"] == "HOUR") {
        interval = int.parse(value["data"][0]["IntervalPatroli"]) * 60;
      } else if (value["data"][0]["IntervalType"] == "MINUTE") {
        interval = int.parse(value["data"][0]["IntervalPatroli"]);
      }
    });
  }

  Future<void> assingTopic() async{
    await Firebase.initializeApp();
    await FirebaseMessaging.instance.subscribeToTopic("SOSTopic${widget.sess!.LocationID}");
  }

  Future<void> _requestPermission() async{
    var oLocation = await Permission.location.status;
    var oCamera = await Permission.camera.status;

    if (oLocation.isDenied) {
      if (await Permission.location.request().isGranted) {
        print("Location granted");
      }
      else if(await Permission.location.isPermanentlyDenied){
        openAppSettings();
      }
    }

    if (oCamera.isDenied) {
      if (await Permission.camera.request().isGranted) {
        print("Camera granted");
      }
      else if(await Permission.camera.isPermanentlyDenied){
        openAppSettings();
      }
    }
  }

  @override
  void initState() {
    // Timer.periodic(Duration(seconds: 1), (Timer t) => _timeChange());
    // _fetchData();
    // generateNotif();
    // checkForInitialMessage();
    assingTopic();

    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      print("Ini Message Lho");
      // print(message.notification?.title);
      var rng = Random();
      String? title = message.notification!.title.toString();
      String? body = message.notification!.body.toString();
      String? payload = message.data["ID"];
      // notifdata = message.data;
      if(message.data["type"] == "notif"){
        _inSOS = true;
      }
      NotificationService().init(widget.sess!,rng.nextInt(100), title, body, payload.toString(), context);
    });
    super.initState();
    _requestPermission();
  }

  @override
  void dispose() {
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    Map oParam() {
      return {
        "KodeCheckPoint": "",
        "KodeLokasi": widget.sess!.LocationID.toString(),
        "RecordOwnerID": widget.sess!.RecordOwnerID,
        "TanggalPatroli": _tanggal.toString(),
        "KodeKaryawan": widget.sess!.KodeUser
      };
    }
    // print(this.widget.sess!.server + "/Assets/images/profile/" + this.widget.sess!.RecordOwnerID + ".jpeg");
    return Scaffold(
        drawer: Drawer(
          // backgroundColor: Colors.white,
          child: ListView(
            children: [
              Container(
                width: double.infinity,
                height: widget.sess!.hight * 20,
                color: Colors.cyan,
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    Padding(
                      padding: EdgeInsets.only(
                        left: widget.sess!.width * 2
                      ),
                      child: SizedBox(
                        width: widget.sess!.width * 15,
                        height: widget.sess!.width * 15,
                        // color: Colors.black,
                        child: widget.sess!.icon == "" ? const Icon(Icons.person) : Image.network(
                          "${widget.sess!.server}/Assets/images/profile/${widget.sess!.icon}",
                          width: widget.sess!.width * 10,
                          height: widget.sess!.width * 10,
                        ),
                      ),
                    ),
                    SizedBox(
                      width: widget.sess!.width *55,
                      height: widget.sess!.hight *20,
                      // color: Colors.black,
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Padding(
                            padding: EdgeInsets.all(widget.sess!.width * 2),
                            child: Text(
                              "This Program Licenced to :",
                              style: TextStyle(
                                fontSize: widget.sess!.width * 3
                              ),
                            ),
                          ),
                          Padding(
                            padding: EdgeInsets.only(
                              left: widget.sess!.width * 2
                            ),
                            child: Text(
                              widget.sess!.NamaPartner,
                              style: TextStyle(
                                fontSize: widget.sess!.width * 4,
                                color: Colors.white
                              ),
                            ),
                          ),
                          Padding(
                            padding: EdgeInsets.all(widget.sess!.width * 2),
                            child: Text(
                              "App Version : ${widget.sess!.appVersion}",
                            ),
                          )
                        ],
                      ),
                    )
                  ],
                ),
              ),
              Padding(
                padding: EdgeInsets.only(
                  top: widget.sess!.width * 2,
                  left: widget.sess!.width * 2,
                  right: widget.sess!.width * 2
                ),
                child: SizedBox(
                  width: double.infinity,
                  height: widget.sess!.hight * 5,
                  child: GestureDetector(
                    child: const Card(
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                        children: [
                          Icon(Icons.person_add_sharp),
                          Text(
                            "Pencatatan Tamu"
                          ),
                          Icon(Icons.arrow_forward_rounded),
                        ],
                      ),
                    ),
                    onTap: (){
                      Navigator.push(context,MaterialPageRoute(builder: (context) => GuestLog(widget.sess!)));
                    },
                  ),
                ),
              ),
              Padding(
                padding: EdgeInsets.only(
                  top: widget.sess!.width * 2,
                  left: widget.sess!.width * 2,
                  right: widget.sess!.width * 2
                ),
                child: SizedBox(
                  width: double.infinity,
                  height: widget.sess!.hight * 5,
                  child: GestureDetector(
                    child: const Card(
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                        children: [
                          Icon(Icons.calendar_month),
                          Text(
                            "Daily Activity"
                          ),
                          Icon(Icons.arrow_forward_rounded),
                        ],
                      ),
                    ),
                    onTap: (){
                      Navigator.push(context,MaterialPageRoute(builder: (context) => DailyActivity(widget.sess!)));
                    },
                  ),
                ),
              ),
              Padding(
                padding: EdgeInsets.only(
                  top: widget.sess!.width * 2,
                  left: widget.sess!.width * 2,
                  right: widget.sess!.width * 2
                ),
                child: SizedBox(
                  width: double.infinity,
                  height: widget.sess!.hight * 5,
                  child: GestureDetector(
                    child: Card(
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                        children: [
                          const Text(
                            "Offline Mode"
                          ),
                          // Icon(Icons.arrow_forward_rounded),
                          Switch(
                            value: _offlineMode, 
                            onChanged: (value)async{
                              _offlineMode = value;
                              if (_offlineMode) {
                                await Mod_Patroli(widget.sess, Parameter: oParam()).getPatroliList().then((value) {
                                  if(value['data'].length > 0){
                                    Map<String, String> oColumn() {
                                      return {
                                        "KodeCheckPoint" : "TEXT",
                                        "NamaCheckPoint" : "TEXT",
                                        "RecordOwnerID" : "TEXT",
                                        "JumlahCheckin" : "integer"
                                      };
                                    }
                                    var dbx = LocalDatabaseHelper("tcheckpoint", oColumn());
                                    dbx.database.then((dbxvalue) async {
                                      if (dbxvalue.isOpen) {
                                        dbxvalue.execute("DELETE FROM tcheckpoint");
                                        for (var i = 0; i < value['data'].length; i++) {
                                          Map<String,dynamic> oParam(){
                                            return{
                                              "KodeCheckPoint" : value['data'][i]["KodeCheckPoint"],
                                              "NamaCheckPoint" : value['data'][i]["NamaCheckPoint"],
                                              "RecordOwnerID" : widget.sess!.RecordOwnerID,
                                              "JumlahCheckin" : value['data'][i]["JumlahCheckin"]
                                            };
                                          }

                                          dbx.insertData(dbxvalue, oParam());
                                        }
                                        List<Map<String, dynamic>> oData = await dbx.getData(dbxvalue);
                                        print(oData);
                                      }
                                    });

                                    Map<String, String> oColumnPatrol() {
                                      return {
                                        "RecordOwnerID" : "TEXT",
                                        "KodeCheckPoint" : "TEXT",
                                        "LocationID" : "integer",
                                        "TanggalPatroli" : "DATETIME",
                                        "KodeKaryawan" : "TEXT",
                                        "Koordinat" : "TEXT",
                                        "Image" : "TEXT",
                                        "Catatan" : "TEXT"
                                      };
                                    }
                                    var dbxPatrol = LocalDatabaseHelper("patroli", oColumnPatrol());
                                  }
                                });
                              }
                              setState(() {
                                
                              });
                            }
                          )
                        ],
                      ),
                    ),
                    onTap: (){
                      // Navigator.push(context,MaterialPageRoute(builder: (context) => DailyActivity(this.widget.sess!)));
                    },
                  ),
                ),
              ),
              Padding(
                padding: EdgeInsets.only(
                  top: widget.sess!.width * 2,
                  left: widget.sess!.width * 2,
                  right: widget.sess!.width * 2
                ),
                child: SizedBox(
                  width: double.infinity,
                  height: widget.sess!.hight * 5,
                  child: GestureDetector(
                    child: const Card(
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                        children: [
                          Icon(Icons.logout),
                          Text(
                            "Logout"
                          ),
                          Icon(Icons.arrow_forward_rounded),
                        ],
                      ),
                    ),
                    onTap: (){
                      SharedPreference().removeKey("accountInfo");
                      Navigator.pushReplacement(context, MaterialPageRoute(builder: (context) => const MyApp()));
                    },
                  ),
                ),
              ),
            ],
          ),
        ),
        appBar: AppBar(
          backgroundColor: Theme.of(context).primaryColor,
          title: Text(
            "Amman Digital Patroli",
            style: TextStyle(
              fontSize: widget.sess!.width * 4,
              color: Colors.white
            ),
          ),
          // title: Row(
          //   mainAxisAlignment: MainAxisAlignment.start,
          //   children: [
          //     Container(
          //       width: this.widget.sess!.width * 10,
          //       height: this.widget.sess!.width * 10,
          //       // color: Colors.black,
          //       child: this.widget.sess!.icon == "" ? Icon(Icons.person) : Image.network(
          //         this.widget.sess!.server + "/Assets/images/profile/" + this.widget.sess!.icon,
          //         width: this.widget.sess!.width * 10,
          //         height: this.widget.sess!.width * 10,
          //       ),
          //     ),
          //     Padding(
          //       padding: EdgeInsets.only(
          //         left: this.widget.sess!.width * 2
          //       ),
          //       child: Text(
          //         this.widget.sess!.NamaPartner,
          //         style: TextStyle(
          //           fontSize: this.widget.sess!.width * 4,
          //           color: Colors.white
          //         ),
          //       ),
          //     ),
          //   ],
          // ),
          actions: [
            Stack(
              children: [
                IconButton(
                  icon: Icon(Icons.notifications, color: Colors.white,size: 32,),
                  onPressed: (){
                    Navigator.push(context,MaterialPageRoute(builder: (context) => SOSList(widget.sess!)));
                  }, 
                ),
                Positioned(
                  right: 10,
                  top: 11,
                  child: Container(
                    padding: EdgeInsets.all(2),
                    decoration: BoxDecoration(
                      color: Colors.red,borderRadius: BorderRadius.circular(6)
                    ),
                    constraints: BoxConstraints(
                      minWidth: 14,
                      minHeight: 14
                    ),
                    child: FutureBuilder(
                      future: Mod_SOS(this.widget.sess, {"RecordOwnerID": this.widget.sess!.RecordOwnerID, "KodeLokasi":this.widget.sess!.LocationID.toString(), "isRead" :"0"}).getData(), 
                      builder: (context, snapshot){
                        if (snapshot.hasData) {
                          if (snapshot.data!["data"].length > 0) {
                            return Text(
                              snapshot.data!["data"].length > 99 ? "99+" : snapshot.data!["data"].length.toString(),
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 12
                              ),
                              textAlign: TextAlign.center,
                            );
                          }
                          else{
                            return Text(
                              "0",
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 12
                              ),
                              textAlign: TextAlign.center,
                            );
                          }
                        }
                        else{
                          return Text(
                            "0",
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 12
                            ),
                            textAlign: TextAlign.center,
                          );
                        }
                      }
                    ) ,
                  )
                )
              ],
            )
          ],
        ),
        floatingActionButton: FloatingActionButton(
          backgroundColor: Theme.of(context).primaryColor,
          onPressed: () async {
            if (await Permission.location.isGranted) {
              var data = await barcodeScan().then((value) async {
                Map oParamx() {
                  return {
                    "KodeCheckPoint": value,
                    "KodeLokasi": widget.sess!.LocationID.toString(),
                    "RecordOwnerID": widget.sess!.RecordOwnerID,
                    "TanggalPatroli": _tanggal.toString(),
                    "KodeKaryawan": widget.sess!.KodeUser
                  };
                }

                var xData = await Mod_Patroli(widget.sess, Parameter: oParamx()).getPatroliList().then((valueData) {
                  if (valueData["data"].length > 0) {
                    Navigator.push(
                            context,
                            MaterialPageRoute(
                                builder: (context) => FormCheckIn(
                                    widget.sess!,
                                    value,
                                    valueData["data"][0]["NamaCheckPoint"])))
                        .then((value) {
                      setState(() {});
                    });
                  }
                });
              });
            }
            else{
              await messageBox(context: context, title: "Permission Error", message: "Akses ke Lokasi GPS dibutuhkan");
            }
            // Handle Notification
          },
          child: const Icon(
            Icons.add,
            color: Colors.white,
          ),
        ),
        floatingActionButtonLocation: FloatingActionButtonLocation.centerDocked,
        bottomNavigationBar: BottomAppBar(
            shape: const CircularNotchedRectangle(),
            color: Theme.of(context).primaryColor,
            child: ListTile(
                dense: true,
                title: Text(_jumlahCP, style: const TextStyle(color: Colors.white)))),
        body: Column(
          mainAxisAlignment: MainAxisAlignment.start,
          children: [
            Padding(
              padding: EdgeInsets.only(
                  bottom: widget.sess!.width * 2,
                  top: widget.sess!.width * 2),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  SizedBox(
                    width: widget.sess!.width * 50,
                    height: widget.sess!.hight * 10,
                    child: ListTile(
                      leading: const CircleAvatar(
                        child: Icon(Icons.person),
                      ),
                      title: Text(
                        widget.sess!.KodeUser,
                        style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.bold,
                            color: Theme.of(context).primaryColor),
                      ),
                      subtitle: Text(widget.sess!.NamaUser),
                    ),
                  ),
                  SizedBox(
                    width: widget.sess!.width * 40,
                    height: widget.sess!.hight * 10,
                    child: Container(
                      width: widget.sess!.width * 30,
                      height: widget.sess!.hight * 10,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(10.0),
                        color: const Color(0xFFfecb54),
                      ),
                      child: Center(
                        child: StreamBuilder(
                            stream: Stream.periodic(const Duration(seconds: 1)),
                            builder: (context, snapshot) {
                              // final DateTime now = DateTime(2023,07,27,09,59,00).toLocal();
                              final DateTime now = DateTime.now().toLocal();
                              final String temp =
                                  DateFormat('jm').format(now).trim();
                              final ampm =
                                  temp.substring(temp.length - 2, temp.length);

                              String xShift = "";
                              int shift = -1;
                              if (widget.sess!.jadwalShift.isNotEmpty) {
                                // print(this.widget.sess!.jadwalShift);
                                for (var i = 0;
                                    i < widget.sess!.jadwalShift.length;
                                    i++) {
                                  var mulai = widget
                                      .sess!
                                      .jadwalShift[i]["MulaiBekerja"]
                                      .toString()
                                      .split(":");
                                  var selesai = widget
                                      .sess!
                                      .jadwalShift[i]["SelesaiBekerja"]
                                      .toString()
                                      .split(":");
                                  var isGantiHari = int.parse(widget
                                      .sess!
                                      .jadwalShift[i]["GantiHari"]);
                                  // var isGantiHari = this.widget.sess!.isGantiHari;
                                  if (mulai.isNotEmpty) {
                                    DateTime defTime = DateTime(now.year,
                                            now.month, now.day, 0, 0, 1)
                                        .toLocal();

                                    // print(defTime);
                                    DateTime jamMulai = DateTime(
                                        now.year,
                                        now.month,
                                        now.day,
                                        int.parse(mulai[0]),
                                        int.parse(mulai[1]),
                                        int.parse(mulai[0].split(".")[0]));
                                    // DateTime jamSelesai = DateTime.utc(now.year, now.month, isGantiHari == 1 ? now.day +1 : now.day , int.parse(selesai[0]), int.parse(selesai[1]), int.parse(selesai[0].split(".")[0]));
                                    DateTime jamSelesai = DateTime(
                                        now.year,
                                        now.month,
                                        now.day,
                                        int.parse(selesai[0]),
                                        int.parse(selesai[1]),
                                        int.parse(selesai[0].split(".")[0]));

                                    // print(defTime.isAfter(now));
                                    if (isGantiHari == 1 &&
                                        now.isAfter(defTime) &&
                                        now.isBefore(jamSelesai)) {
                                      print("IN");
                                      jamMulai = DateTime(
                                          now.year,
                                          now.month,
                                          now.day - 1,
                                          int.parse(mulai[0]),
                                          int.parse(mulai[1]),
                                          int.parse(mulai[0].split(".")[0]));
                                    } else {
                                      jamSelesai = DateTime(
                                          now.year,
                                          now.month,
                                          now.day + 1,
                                          int.parse(selesai[0]),
                                          int.parse(selesai[1]),
                                          int.parse(selesai[0].split(".")[0]));
                                    }

                                    // print(now.isAfter(jamMulai));
                                    // print(now.isBefore(jamSelesai));

                                    // if(now.isAfter(jamMulai) && now.isBefore(jamSelesai)){
                                    //   xShift = this.widget.sess!.jadwalShift[i]["NamaShift"].toString();
                                    //   shift = int.parse(this.widget.sess!.jadwalShift[i]["id"]);
                                    // }
                                    // else if(now.isBefore(jamMulai) && now.isAfter(jamSelesai) && xShift == ""){
                                    //   xShift = this.widget.sess!.jadwalShift[i]["NamaShift"].toString();
                                    //   shift = int.parse(this.widget.sess!.jadwalShift[i]["id"]);
                                    // }
                                    // print(isGantiHari);
                                    // print("Shift : " + this.widget.sess!.jadwalShift[i]["NamaShift"].toString() + now.toString() + " > " + jamMulai.toString()+" = " + now.toLocal().isAfter(jamMulai.toLocal()).toString());
                                    // print("Shift : " + this.widget.sess!.jadwalShift[i]["NamaShift"].toString() + now.toString() + " < " + jamSelesai.toString()+" = " + now.toLocal().isBefore(jamSelesai.toLocal()).toString());
                                    // print(now.toLocal().isAfter(jamMulai.toLocal()));

                                    if (now
                                            .toLocal()
                                            .isAfter(jamMulai.toLocal()) &&
                                        now
                                            .toLocal()
                                            .isBefore(jamSelesai.toLocal())) {
                                      xShift = widget
                                          .sess!
                                          .jadwalShift[i]["NamaShift"]
                                          .toString()
                                          .toUpperCase();
                                      shift = int.parse(widget
                                          .sess!
                                          .jadwalShift[i]["id"]);
                                    }
                                    // print(now.isAfter(jamMulai.toLocal()).toString() + "+" + now.isBefore(jamSelesai.toLocal()).toString());
                                  }
                                }
                              }
                              print(xShift);
                              _kodeShift = xShift;
                              _idShift = shift;
                              _tanggal = now;
                              // Widget

                              return now == null
                                  ? Container()
                                  : Column(
                                      mainAxisAlignment:
                                          MainAxisAlignment.center,
                                      children: [
                                        const SizedBox(height: 1),
                                        Text(DateFormat("HH:mm:ss").format(now),
                                            style: TextStyle(
                                                fontSize:
                                                    widget.sess!.width *
                                                        5.5,
                                                color: const Color(0xFFbd2a27),
                                                fontWeight: FontWeight.bold)),
                                        Text(
                                          "Shift : $_kodeShift",
                                          style: TextStyle(
                                              fontSize:
                                                  widget.sess!.width * 4,
                                              color: Theme.of(context)
                                                  .primaryColor,
                                              fontWeight: FontWeight.bold),
                                        )
                                      ],
                                    );
                            }
                          ),
                      ),
                    ),
                  )
                ],
              ),
            ),
            Padding(
              padding: EdgeInsets.only(
                left: widget.sess!.width * 2,
                right: widget.sess!.width * 2,
                bottom: widget.sess!.width * 2,
              ),
              child: SizedBox(
                width: double.infinity,
                height: widget.sess!.hight * 15,
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    SizedBox(
                        width: widget.sess!.width * 45,
                        child: Container(
                          height: widget.sess!.hight * 15,
                          decoration: const BoxDecoration(
                              shape: BoxShape.circle, color: Color(0xFFbd2a27)),
                          child: TextButton(
                              onPressed: () async{
                                if(_inSOS){
                                  messageBox(context: context, title: "Informasi", message: "Pesan SOS Bisa dibuat 5 menit setelah pesan sebelumnya");
                                }
                                else{
                                  bool konfirmasi = await messageDialog(context: context, title: "SOS", message: "Kirim Pesan SOS ?");
                                  if(konfirmasi){
                                    // Push Default Content
                                    String uID = const Uuid().v4().toString();
                                    Map oParam(){
                                      return {
                                        "id" : uID,
                                        'RecordOwnerID' : widget.sess!.RecordOwnerID,
                                        'LocationID' : widget.sess!.LocationID.toString(),
                                        'KodeKaryawan' : widget.sess!.KodeUser,
                                        'Comment' : "",
                                        'Image1' : "",
                                        'Image2' : "",
                                        'Image3' : "",
                                        'Koordinat' : "",
                                        'SubmitDate' : DateTime.now().toString(),
                                        'VoiceNote' : "",
                                        "formMode" : "add"
                                      };
                                    }

                                    var save = Mod_SOS(widget.sess, oParam()).create().then((value) {
                                      Navigator.push(context,MaterialPageRoute(builder: (context) => sosMessage(widget.sess!, uID)));
                                    });
                                    // Navigator.push(context,MaterialPageRoute(builder: (context) => sosMessage(this.widget.sess!, uID)));
                                  }
                                }
                              },
                              child: Text(
                                "SOS",
                                style: TextStyle(
                                    fontFamily: "Arial",
                                    color: Colors.white,
                                    fontSize: widget.sess!.width * 10),
                              )),
                        )),
                    SizedBox(
                        width: widget.sess!.width * 45,
                        child: Container(
                          height: widget.sess!.hight * 15,
                          decoration: BoxDecoration(
                              shape: BoxShape.circle, color: Theme.of(context).primaryColor),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Padding(
                                padding: EdgeInsets.only(
                                    right: widget.sess!.width * 2,
                                    bottom: widget.sess!.hight * 1),
                                child: IconButton(
                                    onPressed: () async{
                                      if (await Permission.location.isGranted) {
                                        Navigator.push(context,MaterialPageRoute(builder: (context) => FormAbsensi(widget.sess!, _kodeShift))); 
                                      }
                                      else{
                                        await messageBox(context: context, title: "Permission Error", message: "Akses ke Lokasi GPS dibutuhkan");
                                      }
                                      // var validasi = await checkSchadule(this.widget.sess!, context).then((value) => {
                                      //   if(value){
                                      //     Navigator.push(context,MaterialPageRoute(builder: (context) => FormAbsensi(this.widget.sess!, _kodeShift)))
                                      //   }
                                      //   else{
                                      //     messageBox(context: context, title: "Validation", message: "Jadwal Belum dibuat")
                                      //   }
                                      // });
                                    },
                                    icon: Icon(
                                      Icons.timer_outlined,
                                      size: widget.sess!.width * 10,
                                      color: Colors.white,
                                    )),
                              ),
                              Padding(
                                  padding: EdgeInsets.only(
                                      bottom: widget.sess!.hight * 1.2),
                                  child: Center(
                                    child: Text(
                                      "Absensi",
                                      style: TextStyle(
                                          fontFamily: "Arial",
                                          color: Colors.white,
                                          fontSize:
                                              widget.sess!.width * 4),
                                    ),
                                  ))
                            ],
                          ),
                        ))
                  ],
                ),
              ),
            ),
            Padding(
              padding: EdgeInsets.only(
                left: widget.sess!.width * 2,
                right: widget.sess!.width * 2,
                bottom: widget.sess!.width * 2,
              ),
              child: SizedBox(
                width: double.infinity,
                height: widget.sess!.hight * 8,
                // color: Colors.black,
                child: Card(
                  shape: const RoundedRectangleBorder(
                    side: BorderSide(color: Colors.white54, width: 1),
                    borderRadius: BorderRadius.only(
                        bottomLeft: Radius.circular(30),
                        topRight: Radius.circular(30)),
                  ),
                  elevation: 0,
                  color: Theme.of(context).primaryColor,
                  child: ListTile(
                    leading: const Icon(
                      Icons.access_alarm_sharp,
                      color: Colors.white,
                      size: 32,
                    ),
                    title: const Text("Progres Patroli",
                        style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                            color: Colors.white)),
                    trailing: Text(
                      _penyelesaian,
                      style: const TextStyle(
                          color: Colors.white,
                          fontSize: 20,
                          fontWeight: FontWeight.bold),
                    ),
                  ),
                ),
              ),
            ),
            Padding(
              padding: EdgeInsets.only(
                left: widget.sess!.width * 2,
                right: widget.sess!.width * 2,
                bottom: widget.sess!.width * 2,
              ),
              child: SizedBox(
                width: double.infinity,
                height: widget.sess!.width * 80,
                // color: Colors.black,
                // child: _listData(_data.length > 0 ? _data["data"] : []),
                child: FutureBuilder(
                    future: Mod_Patroli(widget.sess, Parameter: oParam())
                        .getPatroliList(),
                    builder: (context, snapshsot) {
                      if (snapshsot.hasData) {
                        _jumlahCP = "${snapshsot.data!["data"].length} Check Point";
                        _penyelesaian =
                            "${snapshsot.data!["Penyelesaian"]} %";

                        return _listData(snapshsot.data!["data"]);
                      } else {
                        return Container();
                      }
                    }),
              ),
            )
          ],
        ));
  }

  Widget _listData(List list) {
    // print(this.widget.sess!.server);
    if (list.isEmpty) {
      return Container();
    } else {
      return Padding(
          padding: const EdgeInsets.all(10),
          child: RefreshIndicator(
            onRefresh: () => _refreshData(),
            child: ListView.builder(
              shrinkWrap: true,
              // physics: ClampingScrollPhysics(),
              itemCount: list == null ? 0 : list.length,
              itemBuilder: (context, index) {
                return ListTile(
                  leading: list[index]["sts"].toString() == "1"
                      ? Icon(
                          Icons.check_box_outlined,
                          color: Theme.of(context).primaryColor,
                        )
                      : const Icon(Icons.check_box_outline_blank_outlined),
                  title: Text(
                    list[index]["NamaCheckPoint"].toString(),
                    style: TextStyle(color: widget.sess!.textColor),
                  ),
                  subtitle: Text(
                    list[index]["Keterangan"],
                    style: TextStyle(
                      color: widget.sess!.lightTextColor
                    ),
                  ),
                  trailing: Chip(
                    label: Text(
                      list[index]["JumlahCheckin"].toString(),
                      style: const TextStyle(
                        color: Colors.white
                      ),
                    ),
                    backgroundColor: Theme.of(context).primaryColor,
                  ),
                );
              },
            ),
          ));
    }
  }

  Future _refreshData() async {
    setState(() {});
    Completer<void> completer = Completer<void>();
    Future.delayed(const Duration(seconds: 1)).then((_) {
      completer.complete();
    });
    return completer.future;
  }
}
