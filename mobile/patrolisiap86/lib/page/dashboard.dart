import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:intl/intl.dart';
import 'package:mobilepatrol/general/session.dart';
import 'package:mobilepatrol/main.dart';
import 'package:mobilepatrol/models/patroli.dart';
import 'package:flutter_barcode_scanner/flutter_barcode_scanner.dart';
import 'package:mobilepatrol/page/FormCheckIn.dart';
import 'package:awesome_notifications/awesome_notifications.dart';
import 'package:mobilepatrol/shared/sharedprefrence.dart';

class Dashboard extends StatefulWidget {
  final session? sess;
  final int ? interval;
  Dashboard(this.sess, {this.interval});

  @override
  _dashboardState createState() => _dashboardState();
}


class _dashboardState extends State<Dashboard> {
  DateTime? _tanggal;
  // String _kodeShift;
  Map _data = {};
  String _penyelesaian = "0 %";
  String _jumlahCP = "0 Check Point";

  String _scanBarcode = 'Unknown';

  final f = NumberFormat("##0");
  static ReceivedAction? initialAction;
  //Time Change
  //---------------------------------------------------------------------------
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

  @override
  void initState() {
    Timer.periodic(Duration(seconds: 1), (Timer t) => _timeChange());
    // _fetchData();
    super.initState();
  }

  @override
  void dispose(){
    super.dispose();
  }
  static Future<void> createNewNotification(int x, String StartPatroli) async {
    // bool isAllowed = await AwesomeNotifications().isNotificationAllowed();
    // if (!isAllowed) isAllowed = await displayNotificationRationale();
    // if (!isAllowed) return;
    String localTimeZone = await AwesomeNotifications().getLocalTimeZoneIdentifier();
    // print(localTimeZone);
    var listTime = StartPatroli.split(":");
    await AwesomeNotifications().createNotification(
        content: NotificationContent(
            id: -1, // -1 is replaced by a random number
            channelKey: 'alerts',
            title: 'Informasi Patroli',
            body: "Sudah Waktunya Patroli Lagi.!!!",
            notificationLayout: NotificationLayout.Messaging,
            payload: {'notificationId': '1234567890'}),
            schedule: NotificationCalendar.fromDate(
              // date: DateTime(DateTime.now().year,DateTime.now().month,DateTime.now().day,int.parse(listTime[0]),int.parse(listTime[1]), int.parse(listTime[2])).add(Duration(minutes: x == null ? 0 : x)),
              date: DateTime.now().add(Duration(minutes: x == null ? 0 : x)),
              repeats: true,
              preciseAlarm: true,
              allowWhileIdle: true
            ),
          );
  }
  Widget build(BuildContext context) {
    Map oParam() {
      return {
        "KodeCheckPoint": "",
        "KodeLokasi": this.widget.sess!.LocationID.toString(),
        "RecordOwnerID": this.widget.sess!.RecordOwnerID,
        "TanggalPatroli": _tanggal.toString(),
        "KodeKaryawan": this.widget.sess!.KodeUser
      };
    }

    return Scaffold(
        appBar: AppBar(
          title: Text("Patroli Siap x86"),
          actions: [
            Padding(
              padding: EdgeInsets.only(right: this.widget.sess!.width * 5),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  GestureDetector(
                    child: Text(
                      "Logout",
                      style: TextStyle(
                        fontSize: this.widget.sess!.width * 4,
                        fontWeight: FontWeight.bold,
                        fontFamily: "Arial"
                      ),
                    ),
                    onTap: () async{
                      // print("data tabed");
                      // await awesomeNotification;
                      SharedPreference().removeKey("accountInfo");
                      Navigator.pushReplacement(context,MaterialPageRoute(builder: (context) => MyApp()));
                    },
                  )
                ],
              ),
            )
          ],
        ),
        floatingActionButton: FloatingActionButton(
          child: Icon(Icons.add),
          onPressed: () async {
            // Handle Notification
            Map oParamLokasi(){
              return {
                'id' : this.widget.sess!.LocationID.toString(),
                'RecordOwnerID':this.widget.sess!.RecordOwnerID,
              };
            }

            var x = Mod_Patroli(this.widget.sess, Parameter: oParamLokasi()).readLokasi().then((value) async {
              int interval = 0;

              // int.parse(value["data"][0]["IntervalPatroli"]) * 60;
              if(value["data"][0]["IntervalType"] == "DAY"){
                interval = value["data"][0]["IntervalPatroli"] * 1440;
              }
              else if(value["data"][0]["IntervalType"] == "HOUR"){
                interval = value["data"][0]["IntervalPatroli"] * 60;
              }
              else if(value["data"][0]["IntervalType"] == "MINUTE"){
                interval = value["data"][0]["IntervalPatroli"];
              }
              createNewNotification(interval,value["data"][0]["StartPatroli"]);
            });
            var data = await barcodeScan().then((value) async {
              Map oParamx() {
                return {
                  "KodeCheckPoint": value,
                  "KodeLokasi": this.widget.sess!.LocationID.toString(),
                  "RecordOwnerID": this.widget.sess!.RecordOwnerID,
                  "TanggalPatroli": _tanggal.toString(),
                  "KodeKaryawan": this.widget.sess!.KodeUser
                };
              }

              var xData =
                  await Mod_Patroli(this.widget.sess, Parameter: oParamx())
                      .getPatroliList()
                      .then((valueData) {
                if (valueData["data"].length > 0) {
                  Navigator.push(
                          context,
                          MaterialPageRoute(
                              builder: (context) => FormCheckIn(
                                  this.widget.sess!,
                                  value,
                                  valueData["data"][0]["NamaCheckPoint"])))
                      .then((value) {
                    setState(() {});
                  });
                }
              });
            });
          },
        ),
        floatingActionButtonLocation: FloatingActionButtonLocation.centerDocked,
        bottomNavigationBar: BottomAppBar(
            shape: CircularNotchedRectangle(),
            color: Theme.of(context).primaryColor,
            child: ListTile(
                dense: true,
                title: Text(_jumlahCP, style: TextStyle(color: Colors.white)))),
        body: Column(
          mainAxisAlignment: MainAxisAlignment.start,
          children: [
            Padding(
              padding: EdgeInsets.all(this.widget.sess!.width * 2),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Container(
                    width: this.widget.sess!.width * 50,
                    height: this.widget.sess!.hight * 10,
                    child: ListTile(
                      leading: CircleAvatar(
                        child: Icon(Icons.person),
                      ),
                      title: Text(
                        this.widget.sess!.KodeUser,
                        style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.bold,
                            color: Theme.of(context).primaryColor),
                      ),
                      subtitle: Text(this.widget.sess!.NamaUser),
                    ),
                  ),
                  _tanggal == null
                      ? Container()
                      : Container(
                          width: this.widget.sess!.width * 40,
                          height: this.widget.sess!.hight * 10,
                          child: Container(
                            width: this.widget.sess!.width * 30,
                            height: this.widget.sess!.hight * 10,
                            decoration: new BoxDecoration(
                              borderRadius: new BorderRadius.circular(10.0),
                              color: Colors.yellow.shade600,
                            ),
                            child: Center(
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  SizedBox(height: 1),
                                  Text(DateFormat("hh:mm:ss").format(_tanggal!),
                                      style: TextStyle(
                                          fontSize: 16,
                                          color: Colors.red,
                                          fontWeight: FontWeight.bold)),
                                ],
                              ),
                            ),
                          ))
                ],
              ),
            ),
            Padding(
              padding: EdgeInsets.only(
                left: this.widget.sess!.width * 2,
                right: this.widget.sess!.width * 2,
                bottom: this.widget.sess!.width * 2,
              ),
              child: Container(
                width: double.infinity,
                height: this.widget.sess!.hight * 10,
                // color: Colors.black,
                child: Card(
                  shape: RoundedRectangleBorder(
                    side: BorderSide(color: Colors.white54, width: 1),
                    borderRadius: BorderRadius.only(
                        bottomLeft: Radius.circular(30),
                        topRight: Radius.circular(30)),
                  ),
                  elevation: 0,
                  color: Theme.of(context).primaryColorLight,
                  child: ListTile(
                    leading: Icon(
                      Icons.access_alarm_sharp,
                      color: Theme.of(context).primaryColor,
                      size: 32,
                    ),
                    title: Text("Progres Patroli",
                        style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                            color: Theme.of(context).primaryColor)),
                    trailing: Text(
                      _penyelesaian,
                      style: TextStyle(
                          color: Theme.of(context).primaryColor,
                          fontSize: 20,
                          fontWeight: FontWeight.bold),
                    ),
                  ),
                ),
              ),
            ),
            Padding(
              padding: EdgeInsets.only(
                left: this.widget.sess!.width * 2,
                right: this.widget.sess!.width * 2,
                bottom: this.widget.sess!.width * 2,
              ),
              child: Container(
                width: double.infinity,
                height: this.widget.sess!.width * 90,
                // color: Colors.black,
                // child: _listData(_data.length > 0 ? _data["data"] : []),
                child: FutureBuilder(
                    future: Mod_Patroli(this.widget.sess, Parameter: oParam())
                        .getPatroliList(),
                    builder: (context, snapshsot) {
                      if (snapshsot.hasData) {
                        _jumlahCP = snapshsot.data!["data"].length.toString() +
                            " Check Point";
                        _penyelesaian =
                            snapshsot.data!["Penyelesaian"].toString() + " %";

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
    if (list.length == 0) {
      return Container();
    } else {
      return Padding(
          padding: EdgeInsets.all(10),
          child: RefreshIndicator(
            onRefresh: () => _refreshData(),
            child: ListView.builder(
              shrinkWrap: true,
              physics: ClampingScrollPhysics(),
              itemCount: list == null ? 0 : list.length,
              itemBuilder: (context, index) {
                return ListTile(
                  leading: list[index]["sts"].toString() == "1"
                      ? Icon(
                          Icons.check_box_outlined,
                          color: Colors.green,
                        )
                      : Icon(Icons.check_box_outline_blank_outlined),
                  title: Text(
                    list[index]["NamaCheckPoint"].toString(),
                    style: TextStyle(color: Colors.green),
                  ),
                  subtitle: Text(list[index]["Keterangan"]),
                  trailing: Chip(
                    label: Text(list[index]["JumlahCheckin"].toString()),
                    backgroundColor: Colors.green,
                  ),
                );
              },
            ),
          ));
    }
  }

  Future _refreshData() async {
    Completer<Null> completer = Completer<Null>();
    Future.delayed(Duration(seconds: 1)).then((_) {
      completer.complete();
      setState(() {});
    });
    return completer.future;
  }
}
