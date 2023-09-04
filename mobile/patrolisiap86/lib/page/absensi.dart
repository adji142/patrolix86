import 'package:flutter/material.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:mobilepatrol/general/dialog.dart';
import 'package:mobilepatrol/general/session.dart';
import 'package:mobilepatrol/models/absensi.dart';
import 'package:mobilepatrol/models/shift.dart';

class FormAbsensi extends StatefulWidget {
  final session sess;
  final String shift;
  FormAbsensi(this.sess, this.shift);

  @override
  _FormAbsensi createState() => _FormAbsensi();
}

class _FormAbsensi extends State<FormAbsensi> {
  final GlobalKey<State> _keyLoader = new GlobalKey<State>();

  List ? dataJadwal= [];
  List ? dataAbsen = [];

  Future<Map>_getJadwal() async{
    String nowDate = DateTime.now().year.toString() + "-" + DateTime.now().month.toString() + "-" + DateTime.now().day.toString();
    Map oParam(){
      return {
        'TglAwal'   : dataAbsen!.length == 0 ? nowDate : dataAbsen![0]["Checkin"] ,
        'TglAkhir'  : nowDate,
        'id'        : '',
        'RecordOwnerID' : this.widget.sess.RecordOwnerID,
        'NIK'       : this.widget.sess.KodeUser
      };
    }

    print(oParam());
    // await Mod_Shift(this.widget.sess, oParam()).getJadwal().then((value) {
    //   if(value["data"].length == 0){
    //     messageBox(context: context, title: "Informasi", message: "Data Jadwal Belum dibuat");
    //     Navigator.of(context).pop();
    //   }
    // });

    var temp = await Mod_Shift(this.widget.sess, oParam()).getJadwal();

    // print(temp);
    
    return temp;
  
  }

  Future<Map>_getAbsen() async{
    String nowDate = DateTime.now().year.toString() + "-" + DateTime.now().month.toString() + "-" + DateTime.now().day.toString();
    Map oParam(){
      return {
        'KodeLokasi'    : this.widget.sess.LocationID.toString(),
        'RecordOwnerID' : this.widget.sess.RecordOwnerID,
        'KodeKaryawan'  : this.widget.sess.KodeUser,
        'Tanggal'       : nowDate,
      };
    }
    var temp = await Mod_Absensi(this.widget.sess, oParam()).Read();
    return temp;
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
    var temJadwal = await _getJadwal();
    var temAbsen = await _getAbsen();

    dataJadwal = temJadwal["data"];
    dataAbsen = temAbsen["data"];

    setState(() => {});
  }

  @override
  void initState() {
    _fetchData();
    // print(dataJadwal);
    super.initState();
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
      appBar: AppBar(title: Text("Absensi")),
      bottomNavigationBar: BottomAppBar(
        child: dataJadwal!.length > 0 ? Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Padding(
              padding: EdgeInsets.fromLTRB(this.widget.sess.width * 2,
                  this.widget.sess.width * 2, 0, this.widget.sess.width * 2),
              child: Container(
                width: this.widget.sess.width * 40,
                child: ElevatedButton(
                  child: Text("Check In"),
                  style: ButtonStyle(
                      shape: MaterialStateProperty.all<RoundedRectangleBorder>(
                          RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(18.0),
                      )),
                      backgroundColor: MaterialStateProperty.all(Colors.green)),
                  onPressed: dataAbsen!.length > 0 ? null :() {},
                ),
              ),
            ),
            Padding(
              padding: EdgeInsets.fromLTRB(
                  this.widget.sess.width * 2,
                  this.widget.sess.width * 2,
                  this.widget.sess.width * 2,
                  this.widget.sess.width * 2),
              child: Container(
                width: this.widget.sess.width * 40,
                child: ElevatedButton(
                  child: Text("Check Out"),
                  style: ButtonStyle(
                      shape: MaterialStateProperty.all<RoundedRectangleBorder>(
                          RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(18.0),
                      )),
                      backgroundColor: MaterialStateProperty.all(Colors.red)),
                  onPressed: dataAbsen!.length == 0 ? null : () {},
                ),
              ),
            )
          ],
        ) : Container(),
      ),
      body: dataJadwal!.length > 0 ? Column(
        children: [
          Padding(
            padding: EdgeInsets.all(this.widget.sess.width * 2),
            child: Container(
              width: double.infinity,
              height: this.widget.sess.hight * 11,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.start,
                children: [
                  Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      SizedBox(height: 1),
                      Container(
                          width: this.widget.sess.width * 30,
                          height: this.widget.sess.hight * 10,
                          child: Center(
                            child: Text(
                              dataJadwal!.length == 0 ? "" : "Shift : " + dataJadwal![0]["NamaShift"].toString().toUpperCase(),
                              style: TextStyle(
                                  fontSize: this.widget.sess.width * 4,
                                  color: Colors.white,
                                  fontWeight: FontWeight.bold),
                            ),
                          ),
                          decoration: new BoxDecoration(
                            borderRadius: new BorderRadius.circular(10.0),
                            color: Theme.of(context).primaryColor,
                          )),
                    ],
                  ),
                  Padding(
                      padding: EdgeInsets.all(this.widget.sess.width * 2),
                      child: Container(
                        width: this.widget.sess.width * 50,
                        height: this.widget.sess.hight * 10,
                        alignment: Alignment.topLeft,
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Align(
                              alignment: Alignment.topLeft,
                              child: Text(
                                this.widget.sess.KodeUser,
                                style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    fontSize: this.widget.sess.width * 4,
                                    color: Theme.of(context).primaryColor),
                              ),
                            ),
                            Align(
                              alignment: Alignment.topLeft,
                              child: Text(
                                this.widget.sess.NamaUser,
                                style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    fontSize: this.widget.sess.width * 5,
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
            padding: EdgeInsets.all(this.widget.sess.width * 2),
            child: Container(
              width: double.infinity,
              height: this.widget.sess.hight * 30,
              // color: Colors.amber,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.start,
                children: [
                  Container(
                    width: this.widget.sess.width * 40,
                    height: this.widget.sess.hight * 30,
                    // color: Colors.black,
                    child: dataAbsen!.length == 0 ? Image.asset("assets/portrait.png") : Image.network(this.widget.sess.server + "Assets/images/Absensi/" + dataAbsen![0]["ImageIN"]),
                  ),
                  Padding(
                    padding: EdgeInsets.only(
                        left: this.widget.sess.width * 2,
                        right: this.widget.sess.width * 2),
                    child: Container(
                        width: this.widget.sess.width * 50,
                        height: this.widget.sess.hight * 30,
                        // color: Colors.blue,
                        child: Column(
                          children: [
                            Table(
                              columnWidths: {
                                0: FlexColumnWidth(this.widget.sess.width * 25),
                                1: FlexColumnWidth(this.widget.sess.width * 2),
                                2: FlexColumnWidth(this.widget.sess.width * 35)
                              },
                              // border: TableBorder.all(color: Colors.green, width: 1.5),
                              children: [
                                TableRow(
                                  children: [
                                    Text(
                                      "NIK",
                                      style: TextStyle( fontSize: this.widget.sess.width * 4),
                                    ),
                                    Text(":",
                                      style: TextStyle( fontSize:this.widget.sess.width * 4)
                                    ),
                                    Text(this.widget.sess.KodeUser,style: TextStyle(fontSize: this.widget.sess.width * 4)
                                    )
                                  ]
                                ),
                                TableRow(children: [
                                  Text("Nama",
                                      style: TextStyle(
                                          fontSize:
                                              this.widget.sess.width * 4)),
                                  Text(":",
                                      style: TextStyle(
                                          fontSize:
                                              this.widget.sess.width * 4)),
                                  Text(this.widget.sess.NamaUser,
                                      style: TextStyle(
                                          fontSize: this.widget.sess.width * 4))
                                ]),
                                TableRow(children: [
                                  Text("Tanggal",
                                      style: TextStyle(
                                          fontSize:
                                              this.widget.sess.width * 4)),
                                  Text(":",
                                      style: TextStyle(
                                          fontSize:
                                              this.widget.sess.width * 4)),
                                  Text(dataAbsen!.length == 0 ? "" : dataAbsen![0]["Checkin"],
                                      style: TextStyle(
                                          fontSize: this.widget.sess.width * 4))
                                ]),
                                TableRow(children: [
                                  Text("Jam",
                                      style: TextStyle(
                                          fontSize:
                                              this.widget.sess.width * 4)),
                                  Text(":",
                                      style: TextStyle(
                                          fontSize:
                                              this.widget.sess.width * 4)),
                                  Text(dataAbsen!.length == 0 ? "" : dataAbsen![0]["Checkin"],
                                      style: TextStyle(
                                          fontSize: this.widget.sess.width * 4))
                                ])
                              ],
                            ),
                            Padding(
                              padding: EdgeInsets.only(
                                left: this.widget.sess.width * 2,
                                right: this.widget.sess.width * 2,
                                top: this.widget.sess.width * 2,
                              ),
                              child: Container(
                                width: this.widget.sess.width * 55,
                                height: this.widget.sess.hight * 15,
                                // color: Colors.black,
                                child: Center(
                                  child: Text(
                                    dataAbsen!.length > 0 ? "CHECKIN" : "",
                                    style: TextStyle(
                                        fontSize: this.widget.sess.width * 8,
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
            padding: EdgeInsets.all(this.widget.sess.width * 2),
            child: Container(
              width: double.infinity,
              height: this.widget.sess.hight * 30,
              // color: Colors.amber,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.start,
                children: [
                  Container(
                    width: this.widget.sess.width * 40,
                    height: this.widget.sess.hight * 30,
                    // color: Colors.black,
                    child: dataAbsen!.length == 0 ? Image.asset("assets/portrait.png") : Image.network(this.widget.sess.server + "Assets/images/Absensi/" + dataAbsen![0]["ImageOUT"]),
                  ),
                  Padding(
                    padding: EdgeInsets.only(
                        left: this.widget.sess.width * 2,
                        right: this.widget.sess.width * 2),
                    child: Container(
                        width: this.widget.sess.width * 50,
                        height: this.widget.sess.hight * 30,
                        // color: Colors.blue,
                        child: Column(
                          children: [
                            Table(
                              columnWidths: {
                                0: FlexColumnWidth(this.widget.sess.width * 25),
                                1: FlexColumnWidth(this.widget.sess.width * 2),
                                2: FlexColumnWidth(this.widget.sess.width * 35)
                              },
                              // border: TableBorder.all(color: Colors.green, width: 1.5),
                              children: [
                                TableRow(children: [
                                  Text(
                                    "NIK",
                                    style: TextStyle(
                                        fontSize: this.widget.sess.width * 4),
                                  ),
                                  Text(":",
                                      style: TextStyle(
                                          fontSize:
                                              this.widget.sess.width * 4)),
                                  Text(this.widget.sess.KodeUser,
                                      style: TextStyle(
                                          fontSize: this.widget.sess.width * 4))
                                ]),
                                TableRow(children: [
                                  Text("Nama",
                                      style: TextStyle(
                                          fontSize:
                                              this.widget.sess.width * 4)),
                                  Text(":",
                                      style: TextStyle(
                                          fontSize:
                                              this.widget.sess.width * 4)),
                                  Text(this.widget.sess.NamaUser,
                                      style: TextStyle(
                                          fontSize: this.widget.sess.width * 4))
                                ]),
                                TableRow(children: [
                                  Text("Tanggal",
                                      style: TextStyle(
                                          fontSize:
                                              this.widget.sess.width * 4)),
                                  Text(":",
                                      style: TextStyle(
                                          fontSize:
                                              this.widget.sess.width * 4)),
                                  Text(dataAbsen!.length == 0 ? "" : dataAbsen![0]["CheckOut"].toString() ,
                                      style: TextStyle(
                                          fontSize: this.widget.sess.width * 4))
                                ]),
                                TableRow(children: [
                                  Text("Jam",
                                      style: TextStyle(
                                          fontSize:
                                              this.widget.sess.width * 4)),
                                  Text(":",
                                      style: TextStyle(
                                          fontSize:
                                              this.widget.sess.width * 4)),
                                  Text(dataAbsen!.length == 0 ? "" : dataAbsen![0]["CheckOut"].toString(),
                                      style: TextStyle(
                                          fontSize: this.widget.sess.width * 4))
                                ])
                              ],
                            ),
                            Padding(
                              padding: EdgeInsets.only(
                                left: this.widget.sess.width * 2,
                                right: this.widget.sess.width * 2,
                                top: this.widget.sess.width * 2,
                              ),
                              child: Container(
                                width: this.widget.sess.width * 55,
                                height: this.widget.sess.hight * 15,
                                // color: Colors.black,
                                child: Center(
                                  child: Text(
                                    dataAbsen!.length > 0 ? "CHECKOUT" : "",
                                    style: TextStyle(
                                        fontSize: this.widget.sess.width * 8,
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
      ) : Container(
        child: Align(
          alignment: Alignment.center,
          child: Text("Karyawan Tidak ada jadwal"),
        ),
      )
    );
  }
}
