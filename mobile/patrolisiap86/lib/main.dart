import 'package:flutter/material.dart';
import 'package:patrolisiap86/general/session.dart';
import 'package:patrolisiap86/page/dashboard.dart';
import 'package:patrolisiap86/page/login.dart';
import 'package:patrolisiap86/shared/sharedprefrence.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  runApp(MyApp());
}

class MyApp extends StatefulWidget {
  @override
  _MainState createState() => _MainState();
}

class _MainState extends State<MyApp> {
  session sess = new session();
  var kodeuser = "";

  @override
  void initState() {
    
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    var width = MediaQuery.of(context).size.width / 100;
    var height = MediaQuery.of(context).size.height / 100;
    
    setState(() {
      sess.hight = height;
      sess.width = width;
    });
    ThemeData themeData(bool isDarkMode, BuildContext context) {
      return ThemeData(
        primaryColor: Color(0xFF226f54),
        backgroundColor: isDarkMode ? Colors.black : Color(0xFFF1F5FB),
        indicatorColor: isDarkMode ? Color(0xFF0E1D36) : Color(0xFF226f54),
        hintColor: isDarkMode ? Color(0xFF280C0B) : Color(0xff133762),
        highlightColor: isDarkMode ? Color(0xFF372901) : Color(0xff133762),
        hoverColor: isDarkMode ? Color(0xFF3A3A3B) : Color(0xff133762),
        focusColor: isDarkMode ? Color(0xFF0B2512) : Color(0xff133762),
        disabledColor: Colors.grey,
        cardColor: isDarkMode ? Color(0xFF151515) : Colors.white,
        canvasColor: isDarkMode ? Colors.black : Colors.grey[50],
        brightness: isDarkMode ? Brightness.dark : Brightness.light,
        buttonTheme: Theme.of(context).buttonTheme.copyWith(
            colorScheme: isDarkMode ? ColorScheme.dark() : ColorScheme.light()),
        appBarTheme: AppBarTheme(
          elevation: 0,
        ),
      );
    }

    return MaterialApp(
      title: 'Patroli Siap x86',
      theme: themeData(false, context),
      home: FutureBuilder(
        future: SharedPreference().getString("accountInfo"),
        builder: (context, snapshot){
          if (snapshot.hasData && snapshot.data != ""){
            var xData = snapshot.data!.split("|");
            sess.idUser = int.parse(xData[0]);
            sess.NamaUser = xData[1];
            sess.KodeUser = xData[2];
            sess.RecordOwnerID = xData[3];
            sess.LocationID = int.parse(xData[4]);
            return Dashboard(sess);
          }
          else{
            return LoginMobilePotrait(sess);
          }
        }
      )
    );
  }
}
