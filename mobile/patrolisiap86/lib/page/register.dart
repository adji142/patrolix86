import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:mobilepatrol/general/lookup.dart';
import 'package:mobilepatrol/general/session.dart';
import 'package:mobilepatrol/models/auth.dart';
import 'package:mobilepatrol/models/paymentmethod.dart';

class RegisterMobilePotrait extends StatefulWidget {
  final session? sess;
  const RegisterMobilePotrait(this.sess, {super.key});

  @override
  _RegisterMobilePotraitState createState() => _RegisterMobilePotraitState();
}

class _RegisterMobilePotraitState extends State<RegisterMobilePotrait> {
  final TextEditingController _PartnerName = TextEditingController();
  final TextEditingController _AlamatTagihan = TextEditingController();
  final TextEditingController _NoHPPIC = TextEditingController();
  final TextEditingController _EmailPIC = TextEditingController();
  final TextEditingController _NIKPic = TextEditingController();
  final TextEditingController _NamaPIC = TextEditingController();
  final TextEditingController _Password = TextEditingController();
  final TextEditingController _RePassword = TextEditingController();

  final GlobalKey<State> _keyLoader = GlobalKey<State>();

  bool _obscured = false;
  bool _obscured_re = false;

  List ? paymentMethod;
  int ? idPayment = -1;
  String ? namePayment = "";

  double ? _planAmt = 0.0;
  int ? _plantype = 0 ;
  String ? _planName = "";
  double ? _adminfeeRp = 0.0;
  double ? _adminfeePerc = 0.0;
  double ? _totalAdminfee = 0.0;

  final f = NumberFormat("#,###.00");
  
  Future<Map>_getPayment(int id) async{
    Map oParam(){
      return {
        'id'       : id.toString(),
      };
    }
    var temp = await Mod_Auth(widget.sess, Parameter:  oParam()).getPaymentMethod();
    return temp;
  }

  _fetchAbsen(int id) async{
    var temp = await _getPayment(id);
    paymentMethod = temp["data"];
    _adminfeeRp = double.parse(temp["data"][0]["BiayaAdminRp"]);
    _adminfeePerc = double.parse(temp["data"][0]["BiayaAdminPersen"]);
    if(double.parse(temp["data"][0]["BiayaAdminRp"]) != 0 ){
      
    }
    else if(double.parse(temp["data"][0]["BiayaAdminPersen"]) != 0 ){
      
    }
    // _planAmt = temp["data"][0][""]
    setState(() => {});
  }
  @override
  void initState() {
    // _Server.text = "http://patroli.aissystem.org/";
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: ListView(
        children: [
          Align(
            alignment: Alignment.topLeft,
            child: Padding(
              padding: EdgeInsets.only(left: widget.sess!.hight * 5),
              child: Image.asset(
                "assets/logo.gif",
                width: widget.sess!.width * 40,
                height: widget.sess!.hight * 35,
              ),
            ),
          ),
          Align(
            alignment: Alignment.topLeft,
            child: Padding(
              padding: EdgeInsets.only(left: widget.sess!.hight * 5),
              child: Text(
                "Daftar Untuk Jadi",
                style: TextStyle(
                  fontFamily: "Montserrat",
                  fontSize: widget.sess!.hight * 3,
                  fontWeight: FontWeight.bold,
                ),
                textAlign: TextAlign.left,
              ),
            ),
          ),
          Align(
              alignment: Alignment.topLeft,
              child: Padding(
                padding: EdgeInsets.only(left: widget.sess!.hight * 5),
                child: Text(
                  "Member Kami",
                  style: TextStyle(
                      fontFamily: "Montserrat",
                      fontSize: widget.sess!.hight * 3,
                      fontWeight: FontWeight.bold),
                ),
              )),
          Align(
              alignment: Alignment.topLeft,
              child: Padding(
                padding: EdgeInsets.only(
                    left: widget.sess!.hight * 5,
                    top: widget.sess!.hight * 2),
                child: SizedBox(
                  height: widget.sess!.hight * 0.5,
                  width: widget.sess!.width * 20,
                  child: Container(
                    color: Theme.of(context).primaryColor,
                  ),
                ),
              )),
          Align(
            alignment: Alignment.topLeft,
            child: Padding(
              padding: EdgeInsets.only(
                  left: widget.sess!.hight * 5,
                  top: widget.sess!.hight * 2),
              child: SizedBox(
                width: widget.sess!.width * 72,
                child: Center(
                  child: Padding(
                    padding:
                        EdgeInsets.only(bottom: widget.sess!.hight * 0.5),
                    child: TextField(
                      controller: _PartnerName,
                      decoration: InputDecoration(
                          icon: Icon(Icons.person,
                              size: widget.sess!.hight * 4,
                              color: Theme.of(context).primaryColor),
                          labelText: "Nama Perusahaan",
                          labelStyle: TextStyle(
                            color: Theme.of(context).primaryColor,
                            fontSize: widget.sess!.hight * 2,
                          )),
                    ),
                  ),
                ),
              ),
            ),
          ),
          Align(
            alignment: Alignment.topLeft,
            child: Padding(
              padding: EdgeInsets.only(
                  left: widget.sess!.hight * 5,
                  top: widget.sess!.hight * 0.5),
              child: SizedBox(
                width: widget.sess!.width * 72,
                child: Center(
                  child: Padding(
                    padding:
                        EdgeInsets.only(bottom: widget.sess!.hight * 2),
                    child: TextField(
                      controller: _AlamatTagihan,
                      decoration: InputDecoration(
                          icon: Icon(Icons.person,
                              size: widget.sess!.hight * 4,
                              color: Theme.of(context).primaryColor),
                          labelText: "Alamat",
                          labelStyle: TextStyle(
                            color: Theme.of(context).primaryColor,
                            fontSize: widget.sess!.hight * 2,
                          )),
                      maxLines: 3,
                    ),
                  ),
                ),
              ),
            ),
          ),
          Align(
            alignment: Alignment.topLeft,
            child: Padding(
              padding: EdgeInsets.only(
                  left: widget.sess!.hight * 5,
                  top: widget.sess!.hight * 0.5),
              child: SizedBox(
                width: widget.sess!.width * 72,
                child: Center(
                  child: Padding(
                    padding:
                        EdgeInsets.only(bottom: widget.sess!.hight * 2),
                    child: TextField(
                      controller: _NoHPPIC,
                      decoration: InputDecoration(
                          icon: Icon(Icons.person,
                              size: widget.sess!.hight * 4,
                              color: Theme.of(context).primaryColor),
                          labelText: "No. Tlp",
                          labelStyle: TextStyle(
                            color: Theme.of(context).primaryColor,
                            fontSize: widget.sess!.hight * 2,
                          )),
                    ),
                  ),
                ),
              ),
            ),
          ),
          Align(
            alignment: Alignment.topLeft,
            child: Padding(
              padding: EdgeInsets.only(
                  left: widget.sess!.hight * 5,
                  top: widget.sess!.hight * 0.5),
              child: SizedBox(
                width: widget.sess!.width * 72,
                child: Center(
                  child: Padding(
                    padding:
                        EdgeInsets.only(bottom: widget.sess!.hight * 2),
                    child: TextField(
                      controller: _NIKPic,
                      decoration: InputDecoration(
                          icon: Icon(Icons.person,
                              size: widget.sess!.hight * 4,
                              color: Theme.of(context).primaryColor),
                          labelText: "No. Induk Penduduk",
                          labelStyle: TextStyle(
                            color: Theme.of(context).primaryColor,
                            fontSize: widget.sess!.hight * 2,
                          )),
                    ),
                  ),
                ),
              ),
            ),
          ),
          Align(
            alignment: Alignment.topLeft,
            child: Padding(
                padding: EdgeInsets.only(
                    left: widget.sess!.hight * 5,
                    top: widget.sess!.hight * 2),
                child: SizedBox(
                    width: widget.sess!.width * 90,
                    child: Row(
                      children: [
                        SizedBox(
                          width: widget.sess!.width * 72,
                          child: Center(
                            child: Padding(
                              padding: EdgeInsets.only(
                                  bottom: widget.sess!.hight * 2),
                              child: TextField(
                                controller: _Password,
                                obscureText: !_obscured,
                                decoration: InputDecoration(
                                    icon: Icon(Icons.key,
                                        size: widget.sess!.hight * 4,
                                        color: Theme.of(context).primaryColor),
                                    labelText: "Password",
                                    labelStyle: TextStyle(
                                      color: Theme.of(context).primaryColor,
                                      fontSize: widget.sess!.hight * 2,
                                    )),

                                // onTap: () {
                                //   _ratio = 3.5;
                                // },
                                // onSubmitted: (_) {
                                //   _ratio = 2;
                                // },
                              ),
                            ),
                          ),
                        ),
                        GestureDetector(
                          child: Icon(
                              _obscured
                                  ? Icons.visibility_rounded
                                  : Icons.visibility_off_rounded,
                              size: widget.sess!.hight * 3,
                              color: Theme.of(context).primaryColor),
                          onTap: () {
                            setState(() {
                              _obscured = !_obscured;
                              print("Tabbed");
                            });
                          },
                        )
                      ],
                    ))),
          ),
          Align(
            alignment: Alignment.topLeft,
            child: Padding(
                padding: EdgeInsets.only(
                    left: widget.sess!.hight * 5,
                    top: widget.sess!.hight * 2),
                child: SizedBox(
                    width: widget.sess!.width * 90,
                    child: Row(
                      children: [
                        SizedBox(
                          width: widget.sess!.width * 72,
                          child: Center(
                            child: Padding(
                              padding: EdgeInsets.only(
                                  bottom: widget.sess!.hight * 2),
                              child: TextField(
                                controller: _RePassword,
                                obscureText: !_obscured_re,
                                decoration: InputDecoration(
                                    icon: Icon(Icons.key,
                                        size: widget.sess!.hight * 4,
                                        color: Theme.of(context).primaryColor),
                                    labelText: "Tulis Ulang Password",
                                    labelStyle: TextStyle(
                                      color: Theme.of(context).primaryColor,
                                      fontSize: widget.sess!.hight * 2,
                                    )),

                                // onTap: () {
                                //   _ratio = 3.5;
                                // },
                                // onSubmitted: (_) {
                                //   _ratio = 2;
                                // },
                              ),
                            ),
                          ),
                        ),
                        GestureDetector(
                          child: Icon(
                              _obscured_re
                                  ? Icons.visibility_rounded
                                  : Icons.visibility_off_rounded,
                              size: widget.sess!.hight * 3,
                              color: Theme.of(context).primaryColor),
                          onTap: () {
                            setState(() {
                              _obscured_re = !_obscured_re;
                              print("Tabbed");
                            });
                          },
                        )
                      ],
                    ))),
          ),
          Align(
            alignment: Alignment.topLeft,
            child: Padding(
              padding: EdgeInsets.only(
                left: widget.sess!.hight * 5,
                top: widget.sess!.hight * 0.5,
                right: widget.sess!.hight * 5,
              ),
              child: SizedBox(
                width: double.infinity,
                height: widget.sess!.hight * 63,
                child: ListView(
                  scrollDirection: Axis.horizontal,
                  children: [
                    _Basic(),
                    SizedBox(
                      width: widget.sess!.width * 2,
                    ),
                    _PaketLengkap(),
                    SizedBox(
                      width: widget.sess!.width * 2,
                    ),
                    _PaketHitunganBulan()
                  ],
                ),
                // color: Colors.black,
              ),
            ),
          ),
          Align(
            alignment: Alignment.topLeft,
            child: Padding(
              padding: EdgeInsets.only(
                left: widget.sess!.hight * 5,
                top: widget.sess!.hight * 0.5,
                right: widget.sess!.hight * 5,
              ),
              child: SizedBox(
                width: double.infinity,
                height: widget.sess!.hight * 10,
                // color: Colors.black,
                child: Card(
                  child: ListTile(
                    title: GestureDetector(
                      child: namePayment == "" ? Text(
                        "PILIH METODE PEMBAYARAN",
                        style: TextStyle(
                          color: Theme.of(context).primaryColor
                        ),
                      ) : Text(
                        namePayment.toString(),
                        style: TextStyle(
                          color: Theme.of(context).primaryColor
                        ),
                      ),
                      onTap: () async{
                        Map oParam(){
                          return {
                            'id' : ''
                          };
                        }
                        var result = await Navigator.push(context, MaterialPageRoute(builder: (context) => Lookup(title: "Daftar Media Pembayaran", datamodel: Mod_Payment(widget.sess, ),parameter: oParam(), )));
                        if(result != null) {
                          idPayment = int.parse(result["ID"]);
                          namePayment= result["Title"];

                          await _fetchAbsen(idPayment!);

                          setState(() {
                            if(_planAmt != 0){
                              if(_adminfeeRp != 0){
                                _totalAdminfee = _adminfeeRp;
                              }
                              else if(_adminfeePerc != 0){
                                _totalAdminfee = double.parse(_planAmt.toString()) * double.parse(_adminfeePerc.toString()) / 100;
                              }
                              else{
                                _totalAdminfee = 0;
                              }
                            }
                          });
                          
                        }
                        setState(() {
                          
                        });
                      },
                    ),
                    subtitle: namePayment == "" ? const Text("<tab untuk memilih metode pembayaran>") : const Text("-"),
                  ),
                ),
              ),
            ),
          ),

          Align(
            alignment: Alignment.topLeft,
            child: Padding(
              padding: EdgeInsets.only(
                left: widget.sess!.hight * 5,
                top: widget.sess!.hight * 2,
                right: widget.sess!.hight * 5,
                // bottom: this.widget.sess!.hight * 2,
              ),
              child: Column(
                children: [
                  const Text("Detail Transaksi Pembayaran"),
                  // SizedBox(
                  //   height: this.widget.sess!.hight * 2,
                  // ),
                  ListTile(
                    visualDensity: const VisualDensity(horizontal: 0, vertical: -4),
                    title: const Text("Plan dipilih "),
                    subtitle: _planName == "" ? const Text("-") : Text(_planName.toString()),
                    trailing: _planAmt == 0 ? const Text("Rp. 0") : Text(f.format(_planAmt)),
                  ),
                  ListTile(
                    visualDensity: const VisualDensity(horizontal: 0, vertical: -4),
                    title: const Text("Biaya Admin "),
                    subtitle: _totalAdminfee == 0 ? const Text("-") : const Text("Biaya Layanan"),
                    trailing: _totalAdminfee == 0 ? const Text("Rp. 0") : Text(f.format(_totalAdminfee)),
                  ),
                  ListTile(
                    visualDensity: const VisualDensity(horizontal: 0, vertical: -4),
                    title: const Text("Total "),
                    trailing: double.parse(_totalAdminfee.toString()) + double.parse(_planAmt.toString()) == 0 ? const Text("Rp. 0") : Text(f.format(double.parse(_totalAdminfee.toString()) + double.parse(_planAmt.toString())).toString()),
                  )
                ],
              ),
            ),
          ),
          
          Align(
            alignment: Alignment.topLeft,
            child: Padding(
              padding: EdgeInsets.only(
                left: widget.sess!.hight * 5,
                top: widget.sess!.hight * 2,
                right: widget.sess!.hight * 5,
                // bottom: this.widget.sess!.hight * 2,
              ),
              child: SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  style: ButtonStyle(
                    shape: MaterialStateProperty.all<RoundedRectangleBorder>(
                      RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(18.0),
                      ),
                    ),
                    backgroundColor:
                        MaterialStateProperty.all(Theme.of(context).primaryColor),
                  ),
                  onPressed: () {
                    
                  },
                  child: Text(
                    "Daftar Sekarang",
                    style: TextStyle(
                        fontFamily: "Montserrat",
                        fontSize: widget.sess!.hight * 2,
                        color: Colors.white),
                  ),
                ),
              )
            ),
          ),
        ],
      ),
    );
  }

  Widget _Basic() {
    return GestureDetector(
      child: SizedBox(
        width: widget.sess!.width * 50,
        height: widget.sess!.hight * 40,
        child: Card(
          color: Theme.of(context).primaryColorLight,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.start,
            children: [
              Padding(
                padding: const EdgeInsets.fromLTRB(0, 0, 0, 0),
                child: Container(
                    decoration: BoxDecoration(
                      // borderRadius: BorderRadius.circular(10),
                      color: Colors.blue[300],
                    ),
                    width: double.infinity,
                    height: widget.sess!.hight * 5,
                    child: Center(
                      child: Text(
                        "BASIC",
                        style: TextStyle(
                            fontFamily: "Arial",
                            fontSize: widget.sess!.width * 5,
                            fontWeight: FontWeight.bold,
                            color: Colors.white),
                      ),
                    )),
              ),
              SizedBox(
                height: widget.sess!.hight * 2,
              ),
              Container(
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Align(
                      alignment: Alignment.bottomRight,
                      child: Text("Rp "),
                    ),
                    Text(
                      "300.000",
                      style: TextStyle(
                          fontSize: widget.sess!.width * 7,
                          fontWeight: FontWeight.bold),
                    ),
                    const Align(
                      alignment: Alignment.topCenter,
                      child: Text(" / bln"),
                    ),
                  ],
                ),
              ),
              const Text("Unlimited User"),
              SizedBox(
                height: widget.sess!.hight * 2,
              ),
              ElevatedButton(
                style: ButtonStyle(
                  shape: MaterialStateProperty.all<RoundedRectangleBorder>(
                    RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(18.0),
                    ),
                  ),
                  backgroundColor:
                      MaterialStateProperty.all(Theme.of(context).primaryColor),
                ),
                onPressed: () {
                  setState(() {
                    _plantype = 1;
                    _planName = "Basic Subs";
                    _planAmt = 300000;
                  });
                },
                child: Text(
                  "Pilih Layanan",
                  style: TextStyle(
                      fontFamily: "Montserrat",
                      fontSize: widget.sess!.hight * 2,
                      color: Colors.white),
                ),
              ),
              SizedBox(
                height: widget.sess!.hight * 2,
              ),
              Padding(
                padding: EdgeInsets.fromLTRB(widget.sess!.width * 2, 0,
                    widget.sess!.width * 2, 0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    Icon(
                      Icons.check,
                      color: Theme.of(context).primaryColor,
                    ),
                    const Text(" Akses Web Admin")
                  ],
                ),
              ),
              Padding(
                padding: EdgeInsets.fromLTRB(widget.sess!.width * 2, 0,
                    widget.sess!.width * 2, 0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    Icon(
                      Icons.check,
                      color: Theme.of(context).primaryColor,
                    ),
                    const Text(" Akses Review Patroli")
                  ],
                ),
              ),
              Padding(
                padding: EdgeInsets.fromLTRB(widget.sess!.width * 2, 0,
                    widget.sess!.width * 2, 0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    Icon(
                      Icons.check,
                      color: Theme.of(context).primaryColor,
                    ),
                    const Text(" Multi Site")
                  ],
                ),
              ),
              Padding(
                padding: EdgeInsets.fromLTRB(widget.sess!.width * 2, 0,
                    widget.sess!.width * 2, 0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    Icon(
                      Icons.check,
                      color: Theme.of(context).primaryColor,
                    ),
                    const Text(" Free Maintain")
                  ],
                ),
              ),
              Padding(
                padding: EdgeInsets.fromLTRB(widget.sess!.width * 2, 0,
                    widget.sess!.width * 2, 0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    Icon(
                      Icons.check,
                      color: Theme.of(context).primaryColor,
                    ),
                    const Text(" Unlimited User")
                  ],
                ),
              ),
              Padding(
                padding: EdgeInsets.fromLTRB(widget.sess!.width * 2, 0,
                    widget.sess!.width * 2, 0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    Icon(
                      Icons.check,
                      color: Theme.of(context).primaryColor,
                    ),
                    const Text(" Unlimited Checkpoint")
                  ],
                ),
              ),
              Padding(
                padding: EdgeInsets.fromLTRB(widget.sess!.width * 2, 0,
                    widget.sess!.width * 2, 0),
                child: const Row(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    Icon(
                      Icons.cancel,
                      color: Colors.red,
                    ),
                    Text(" Integrasi Absensi")
                  ],
                ),
              ),
              Padding(
                padding: EdgeInsets.fromLTRB(widget.sess!.width * 2, 0,
                    widget.sess!.width * 2, 0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    SizedBox(
                      width: widget.sess!.width * 6,
                    ),
                    const Text(" Face Recognition")
                  ],
                ),
              )
            ],
          ),
        ),
      ),
    );
  }

  Widget _PaketLengkap() {
    return GestureDetector(
      child: SizedBox(
        width: widget.sess!.width * 50,
        height: widget.sess!.hight * 40,
        child: Card(
          color: Theme.of(context).primaryColorLight,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.start,
            children: [
              Padding(
                padding: const EdgeInsets.fromLTRB(0, 0, 0, 0),
                child: Container(
                    decoration: BoxDecoration(
                      // borderRadius: BorderRadius.circular(10),
                      color: Colors.blue[300],
                    ),
                    width: double.infinity,
                    height: widget.sess!.hight * 5,
                    child: Center(
                      child: Text(
                        "PAKET LENGKAP",
                        style: TextStyle(
                            fontFamily: "Arial",
                            fontSize: widget.sess!.width * 5,
                            fontWeight: FontWeight.bold,
                            color: Colors.white),
                      ),
                    )),
              ),
              SizedBox(
                height: widget.sess!.hight * 2,
              ),
              Container(
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Align(
                      alignment: Alignment.bottomRight,
                      child: Text("Rp "),
                    ),
                    Text(
                      "450.000",
                      style: TextStyle(
                          fontSize: widget.sess!.width * 7,
                          fontWeight: FontWeight.bold),
                    ),
                    const Align(
                      alignment: Alignment.topCenter,
                      child: Text(" / bln"),
                    ),
                  ],
                ),
              ),
              const Text("Unlimited User"),
              SizedBox(
                height: widget.sess!.hight * 2,
              ),
              ElevatedButton(
                style: ButtonStyle(
                  shape: MaterialStateProperty.all<RoundedRectangleBorder>(
                    RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(18.0),
                    ),
                  ),
                  backgroundColor:
                      MaterialStateProperty.all(Theme.of(context).primaryColor),
                ),
                onPressed: () {
                  setState(() {
                    _plantype = 2;
                    _planName = "Subs Paket Lengkap";
                    _planAmt = 450000;
                  });
                },
                child: Text(
                  "Pilih Layanan",
                  style: TextStyle(
                      fontFamily: "Montserrat",
                      fontSize: widget.sess!.hight * 2,
                      color: Colors.white),
                ),
              ),
              SizedBox(
                height: widget.sess!.hight * 2,
              ),
              Padding(
                padding: EdgeInsets.fromLTRB(widget.sess!.width * 2, 0,
                    widget.sess!.width * 2, 0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    Icon(
                      Icons.check,
                      color: Theme.of(context).primaryColor,
                    ),
                    const Text(" Akses Web Admin")
                  ],
                ),
              ),
              Padding(
                padding: EdgeInsets.fromLTRB(widget.sess!.width * 2, 0,
                    widget.sess!.width * 2, 0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    Icon(
                      Icons.check,
                      color: Theme.of(context).primaryColor,
                    ),
                    const Text(" Akses Review Patroli")
                  ],
                ),
              ),
              Padding(
                padding: EdgeInsets.fromLTRB(widget.sess!.width * 2, 0,
                    widget.sess!.width * 2, 0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    Icon(
                      Icons.check,
                      color: Theme.of(context).primaryColor,
                    ),
                    const Text(" Multi Site")
                  ],
                ),
              ),
              Padding(
                padding: EdgeInsets.fromLTRB(widget.sess!.width * 2, 0,
                    widget.sess!.width * 2, 0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    Icon(
                      Icons.check,
                      color: Theme.of(context).primaryColor,
                    ),
                    const Text(" Free Maintain")
                  ],
                ),
              ),
              Padding(
                padding: EdgeInsets.fromLTRB(widget.sess!.width * 2, 0,
                    widget.sess!.width * 2, 0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    Icon(
                      Icons.check,
                      color: Theme.of(context).primaryColor,
                    ),
                    const Text(" Unlimited User")
                  ],
                ),
              ),
              Padding(
                padding: EdgeInsets.fromLTRB(widget.sess!.width * 2, 0,
                    widget.sess!.width * 2, 0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    Icon(
                      Icons.check,
                      color: Theme.of(context).primaryColor,
                    ),
                    const Text(" Unlimited Checkpoint")
                  ],
                ),
              ),
              Padding(
                padding: EdgeInsets.fromLTRB(widget.sess!.width * 2, 0,
                    widget.sess!.width * 2, 0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    Icon(
                      Icons.check,
                      color: Theme.of(context).primaryColor,
                    ),
                    const Text(" Integrasi Absensi")
                  ],
                ),
              ),
              Padding(
                padding: EdgeInsets.fromLTRB(widget.sess!.width * 2, 0,
                    widget.sess!.width * 2, 0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    SizedBox(
                      width: widget.sess!.width * 6,
                    ),
                    const Text(" Face Recognition")
                  ],
                ),
              ),
              Padding(
                padding: EdgeInsets.fromLTRB(widget.sess!.width * 2, 0,
                    widget.sess!.width * 2, 0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    Icon(
                      Icons.check,
                      color: Theme.of(context).primaryColor,
                    ),
                    const Text(" Akses Laporan Absensi")
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _PaketHitunganBulan() {
    return GestureDetector(
      child: SizedBox(
        width: widget.sess!.width * 50,
        height: widget.sess!.hight * 40,
        child: Card(
          color: Theme.of(context).primaryColorLight,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.start,
            children: [
              Padding(
                padding: const EdgeInsets.fromLTRB(0, 0, 0, 0),
                child: Container(
                    decoration: BoxDecoration(
                      // borderRadius: BorderRadius.circular(10),
                      color: Colors.blue[300],
                    ),
                    width: double.infinity,
                    height: widget.sess!.hight * 5,
                    child: Center(
                      child: Text(
                        "TERLARIS",
                        style: TextStyle(
                            fontFamily: "Arial",
                            fontSize: widget.sess!.width * 5,
                            fontWeight: FontWeight.bold,
                            color: Colors.red),
                      ),
                    )),
              ),
              SizedBox(
                height: widget.sess!.hight * 1,
              ),
              Container(
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceAround,
                  children: [
                    Padding(
                      padding:
                          EdgeInsets.only(left: widget.sess!.width * 2),
                      child: Text(
                        "Rp. 450.000",
                        style: TextStyle(
                            fontFamily: "Roboto",
                            fontSize: widget.sess!.width * 4,
                            decoration: TextDecoration.lineThrough),
                      ),
                    ),
                    Padding(
                        padding:
                            EdgeInsets.only(right: widget.sess!.width * 2),
                        child: Container(
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(20),
                            color: Colors.deepPurpleAccent,
                          ),
                          width: widget.sess!.width * 20,
                          height: widget.sess!.hight * 5,
                          child: const Center(
                            child: Text(
                              "SAVE 30%",
                              style: TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.bold),
                            ),
                          ),
                        ))
                  ],
                ),
              ),
              Container(
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Align(
                      alignment: Alignment.bottomRight,
                      child: Text("Rp "),
                    ),
                    Text(
                      "315.000",
                      style: TextStyle(
                          fontSize: widget.sess!.width * 5,
                          fontWeight: FontWeight.bold),
                    ),
                    const Align(
                      alignment: Alignment.topCenter,
                      child: Text(" / bln"),
                    ),
                  ],
                ),
              ),
              SizedBox(
                height: widget.sess!.hight * 1,
              ),
              const Text("Unlimited User,"),
              const Text("Minimal Subscribe 12 Bulan"),
              SizedBox(
                height: widget.sess!.hight * 1,
              ),
              ElevatedButton(
                style: ButtonStyle(
                  shape: MaterialStateProperty.all<RoundedRectangleBorder>(
                    RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(18.0),
                    ),
                  ),
                  backgroundColor:
                      MaterialStateProperty.all(Theme.of(context).primaryColor),
                ),
                onPressed: () {
                  setState(() {
                    _plantype = 3;
                    _planName = "Best Seller Subs";
                    _planAmt = 315000;
                  });
                },
                child: Text(
                  "Pilih Layanan",
                  style: TextStyle(
                      fontFamily: "Montserrat",
                      fontSize: widget.sess!.hight * 2,
                      color: Colors.white),
                ),
              ),
              SizedBox(
                height: widget.sess!.hight * 1,
              ),
              Padding(
                padding: EdgeInsets.fromLTRB(widget.sess!.width * 2, 0,
                    widget.sess!.width * 2, 0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    Icon(
                      Icons.check,
                      color: Theme.of(context).primaryColor,
                    ),
                    const Text(" Akses Web Admin")
                  ],
                ),
              ),
              Padding(
                padding: EdgeInsets.fromLTRB(widget.sess!.width * 2, 0,
                    widget.sess!.width * 2, 0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    Icon(
                      Icons.check,
                      color: Theme.of(context).primaryColor,
                    ),
                    const Text(" Akses Review Patroli")
                  ],
                ),
              ),
              Padding(
                padding: EdgeInsets.fromLTRB(widget.sess!.width * 2, 0,
                    widget.sess!.width * 2, 0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    Icon(
                      Icons.check,
                      color: Theme.of(context).primaryColor,
                    ),
                    const Text(" Multi Site")
                  ],
                ),
              ),
              Padding(
                padding: EdgeInsets.fromLTRB(widget.sess!.width * 2, 0,
                    widget.sess!.width * 2, 0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    Icon(
                      Icons.check,
                      color: Theme.of(context).primaryColor,
                    ),
                    const Text(" Free Maintain")
                  ],
                ),
              ),
              Padding(
                padding: EdgeInsets.fromLTRB(widget.sess!.width * 2, 0,
                    widget.sess!.width * 2, 0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    Icon(
                      Icons.check,
                      color: Theme.of(context).primaryColor,
                    ),
                    const Text(" Unlimited User")
                  ],
                ),
              ),
              Padding(
                padding: EdgeInsets.fromLTRB(widget.sess!.width * 2, 0,
                    widget.sess!.width * 2, 0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    Icon(
                      Icons.check,
                      color: Theme.of(context).primaryColor,
                    ),
                    const Text(" Unlimited Checkpoint")
                  ],
                ),
              ),
              Padding(
                padding: EdgeInsets.fromLTRB(widget.sess!.width * 2, 0,
                    widget.sess!.width * 2, 0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    Icon(
                      Icons.check,
                      color: Theme.of(context).primaryColor,
                    ),
                    const Text(" Integrasi Absensi")
                  ],
                ),
              ),
              Padding(
                padding: EdgeInsets.fromLTRB(widget.sess!.width * 2, 0,
                    widget.sess!.width * 2, 0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    SizedBox(
                      width: widget.sess!.width * 6,
                    ),
                    const Text(" Face Recognition")
                  ],
                ),
              ),
              Padding(
                padding: EdgeInsets.fromLTRB(widget.sess!.width * 2, 0,
                    widget.sess!.width * 2, 0),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    Icon(
                      Icons.check,
                      color: Theme.of(context).primaryColor,
                    ),
                    const Text(" Akses Laporan Absensi")
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
