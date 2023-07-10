import 'package:flutter/material.dart';
import 'package:patrolisiap86/general/dialog.dart';
import 'package:patrolisiap86/general/session.dart';
import 'package:patrolisiap86/models/auth.dart';
import 'package:patrolisiap86/page/dashboard.dart';
import 'package:patrolisiap86/shared/sharedprefrence.dart';

class LoginMobilePotrait extends StatefulWidget {
  final session? sess;
  LoginMobilePotrait(this.sess);

  @override
  _LoginMobilePotraitState createState() => _LoginMobilePotraitState();
}

class _LoginMobilePotraitState extends State<LoginMobilePotrait> {
  TextEditingController _PartnerCode = TextEditingController();
  TextEditingController _UserName = TextEditingController();
  TextEditingController _Password = TextEditingController();

  final GlobalKey<State> _keyLoader = new GlobalKey<State>();

  bool _obscured = false;
  bool _isReadonly = false;
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: ListView(
        // mainAxisAlignment: MainAxisAlignment.start,
        children: [
          Align(
            alignment: Alignment.topLeft,
            child: Padding(
              padding: EdgeInsets.only(left: this.widget.sess!.hight * 5),
              child: Container(
                // color: Colors.black,
                width: this.widget.sess!.width * 25,
                height: this.widget.sess!.hight * 25,
              ),
            ),
          ),
          Align(
            alignment: Alignment.topLeft,
            child: Padding(
              padding: EdgeInsets.only(left: this.widget.sess!.hight * 5),
              child: Text(
                "Masuk Sebagai",
                style: TextStyle(
                  fontFamily: "Montserrat",
                  fontSize: this.widget.sess!.hight * 3,
                  fontWeight: FontWeight.bold,
                ),
                textAlign: TextAlign.left,
              ),
            ),
          ),
          Align(
              alignment: Alignment.topLeft,
              child: Padding(
                padding: EdgeInsets.only(left: this.widget.sess!.hight * 5),
                child: Text(
                  "Member",
                  style: TextStyle(
                      fontFamily: "Montserrat",
                      fontSize: this.widget.sess!.hight * 3,
                      fontWeight: FontWeight.bold),
                ),
              )),
          Align(
              alignment: Alignment.topLeft,
              child: Padding(
                padding: EdgeInsets.only(
                    left: this.widget.sess!.hight * 5,
                    top: this.widget.sess!.hight * 2),
                child: SizedBox(
                  height: this.widget.sess!.hight * 0.5,
                  width: this.widget.sess!.width * 20,
                  child: Container(
                    color: Theme.of(context).primaryColor,
                  ),
                ),
              )),
          Align(
              alignment: Alignment.topLeft,
              child: Padding(
                  padding: EdgeInsets.only(
                      left: this.widget.sess!.hight * 5,
                      top: this.widget.sess!.hight * 2),
                  child: FutureBuilder(
                      future: SharedPreference().getString("PartnerCode"),
                      builder: (context, snapshot) {
                        _isReadonly = snapshot.data != "" ? true : false;
                        if (snapshot.hasData) {
                          _PartnerCode.text = snapshot.data.toString();
                        }
                        return Container(
                            width: this.widget.sess!.width * 90,
                            child: Row(
                              children: [
                                Container(
                                  width: this.widget.sess!.width * 72,
                                  child: Center(
                                    child: Padding(
                                      padding: EdgeInsets.only(
                                          bottom: this.widget.sess!.hight * 2),
                                      child: TextField(
                                        controller: _PartnerCode,
                                        readOnly: _isReadonly,
                                        decoration: InputDecoration(
                                            icon: Icon(Icons.handshake,
                                                size:
                                                    this.widget.sess!.hight * 4,
                                                color: Theme.of(context)
                                                    .primaryColor),
                                            labelText: "Kode Partner",
                                            labelStyle: TextStyle(
                                                fontSize:
                                                    this.widget.sess!.hight * 2,
                                                color: Theme.of(context)
                                                    .primaryColor)),

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
                                  child: Text(
                                    "Ganti",
                                    style: TextStyle(
                                        color: Theme.of(context).primaryColor,
                                        fontWeight: FontWeight.bold,
                                        fontFamily: "Montserrat",
                                        fontSize: this.widget.sess!.hight * 2),
                                  ),
                                  onTap: () async {
                                    await SharedPreference()
                                        .removeKey("PartnerCode");
                                    // print("Enable");
                                    setState(() {});
                                  },
                                )
                              ],
                            ));
                      }))),
          Align(
            alignment: Alignment.topLeft,
            child: Padding(
              padding: EdgeInsets.only(
                  left: this.widget.sess!.hight * 5,
                  top: this.widget.sess!.hight * 2),
              child: Container(
                width: this.widget.sess!.width * 72,
                child: Center(
                  child: Padding(
                    padding:
                        EdgeInsets.only(bottom: this.widget.sess!.hight * 2),
                    child: TextField(
                      controller: _UserName,
                      decoration: InputDecoration(
                          icon: Icon(Icons.person,
                              size: this.widget.sess!.hight * 4,
                              color: Theme.of(context).primaryColor),
                          labelText: "Username",
                          labelStyle: TextStyle(
                            color: Theme.of(context).primaryColor,
                            fontSize: this.widget.sess!.hight * 2,
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
            ),
          ),
          Align(
            alignment: Alignment.topLeft,
            child: Padding(
                padding: EdgeInsets.only(
                    left: this.widget.sess!.hight * 5,
                    top: this.widget.sess!.hight * 2),
                child: Container(
                    width: this.widget.sess!.width * 90,
                    child: Row(
                      children: [
                        Container(
                          width: this.widget.sess!.width * 72,
                          child: Center(
                            child: Padding(
                              padding: EdgeInsets.only(
                                  bottom: this.widget.sess!.hight * 2),
                              child: TextField(
                                controller: _Password,
                                obscureText: !_obscured,
                                decoration: InputDecoration(
                                    icon: Icon(Icons.key,
                                        size: this.widget.sess!.hight * 4,
                                        color: Theme.of(context).primaryColor),
                                    labelText: "Password",
                                    labelStyle: TextStyle(
                                      color: Theme.of(context).primaryColor,
                                      fontSize: this.widget.sess!.hight * 2,
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
                              size: this.widget.sess!.hight * 3,
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
          Padding(
            padding: EdgeInsets.only(
                top: this.widget.sess!.hight * 2,
                left: this.widget.sess!.width * 20,
                right: this.widget.sess!.width * 20),
            child: SizedBox(
              width: this.widget.sess!.width * 30,
              height: this.widget.sess!.hight * 4,
              child: ElevatedButton(
                child: Text(
                  "Login",
                  style: TextStyle(
                      fontFamily: "Montserrat",
                      fontSize: this.widget.sess!.hight * 2),
                ),
                style: ButtonStyle(
                    shape: MaterialStateProperty.all<RoundedRectangleBorder>(
                      RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(18.0),
                      ),
                    ),
                    backgroundColor: MaterialStateProperty.all(
                        Theme.of(context).primaryColor)),
                onPressed: () async{
                  // Navigator.pushReplacement(context, MaterialPageRoute(builder: (context) => DashboardViewController(sess: this.widget.sess,)));
                  const KeyData = "PartnerCode";
                  showLoadingDialog(context, _keyLoader, info: "Begin Login");
                  Map oParam() {
                    return {
                      "RecordOwnerID": _PartnerCode.text,
                      "username": _UserName.text,
                      "password": _Password.text,
                    };
                  }

                  // LoginFill(this.widget.sess, context, oParam: oParam()).logedIn();
                  SharedPreference().setString(KeyData, _PartnerCode.text);
                  var th = await Mod_Auth(this.widget.sess, Parameter: oParam()).Login();

                  if (th != null) {
                    if (th["success"].toString() == "true") {
                      this.widget.sess!.idUser = int.parse(th["unique_id"].toString());
                      this.widget.sess!.NamaUser = th["NamaUser"];
                      this.widget.sess!.KodeUser = th["username"];
                      this.widget.sess!.RecordOwnerID = th["RecordOwnerID"];
                      this.widget.sess!.LocationID = int.parse(th["LocationID"].toString());

                      var xShared = th["unique_id"] + "|" + th["NamaUser"] + "|" + th["username"] + "|" + th["RecordOwnerID"] + "|" + th["LocationID"];

                      SharedPreference().setString("accountInfo", xShared);

                      Navigator.of(context, rootNavigator: true).pop();
                      // Navigator.of(context).pop();
                      Navigator.pushReplacement(context,MaterialPageRoute(builder: (context) => Dashboard(this.widget.sess)));
                    } else {
                      Navigator.of(context, rootNavigator: true).pop();
                      await messageBox(
                          context: context,
                          title: "Gagal Login",
                          message: "Gagal Login :" + th["sError"].toString());
                    }
                  }
                },
              ),
            ),
          )
        ],
      ),
    );
  }
}