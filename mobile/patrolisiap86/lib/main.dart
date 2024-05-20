import 'dart:math';

import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:mobilepatrol/general/notification_service.dart';
// import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:mobilepatrol/general/session.dart';
import 'package:mobilepatrol/models/patroli.dart';
import 'package:mobilepatrol/models/shift.dart';
import 'package:mobilepatrol/page/dashboard.dart';
import 'package:mobilepatrol/page/login.dart';
import 'package:mobilepatrol/page/sos.dart';
import 'package:mobilepatrol/page/sosCallback.dart';
import 'package:mobilepatrol/shared/sharedprefrence.dart';
import 'package:mobilepatrol/test/comparefaces.dart';
import 'package:mobilepatrol/test/face_detector_view.dart';
import 'package:shared_preferences/shared_preferences.dart';

var notifdata;
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  print('Handling a background message ${message.messageId}');
}

final FlutterLocalNotificationsPlugin flutterLocalNotificationsPlugin =
    FlutterLocalNotificationsPlugin();

Future<void> init() async {
  const AndroidInitializationSettings androidInitializationSettings =
      AndroidInitializationSettings('app_icon');

  const InitializationSettings initializationSettings =
      InitializationSettings(android: androidInitializationSettings);
  await flutterLocalNotificationsPlugin.initialize(initializationSettings);
}

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();

  await Firebase.initializeApp();
  FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);

  // await FirebaseMessaging.instance.subscribeToTopic("SOSTopic");

  await FirebaseMessaging.instance.setForegroundNotificationPresentationOptions(
    alert: true,
    badge: true,
    sound: true,
  );

  NotificationSettings setting =
      await FirebaseMessaging.instance.requestPermission(
    alert: true,
    announcement: false,
    badge: true,
    carPlay: false,
    criticalAlert: false,
    provisional: false,
    sound: true,
  );

  print('User granted permission: ${setting.authorizationStatus}');

  
  runApp(const MyApp());
}

class MyApp extends StatefulWidget {
  static final GlobalKey<NavigatorState> navigatorKey =
      GlobalKey<NavigatorState>();

  const MyApp({super.key});
  @override
  _MainState createState() => _MainState();
}

class _MainState extends State<MyApp> {
  session sess = session();
  var kodeuser = "";
  String _Server = "";

  Future<String> _getData() async {
    var temp = await SharedPreference().getString("Server");
    return temp;
  }

  _fetchData() async {
    var temp = await _getData().then((value) {
      setState(() {
        // _data = value;
        _Server = value;
      });
    });
  }

  @override
  void initState() {
    _fetchData();
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
        primaryColor: const Color(0xFF125389),
        indicatorColor: isDarkMode ? const Color(0xFF0E1D36) : const Color(0xFF226f54),
        hintColor: isDarkMode ? const Color(0xFF280C0B) : const Color(0xff133762),
        highlightColor: isDarkMode ? const Color(0xFF372901) : const Color(0xff133762),
        hoverColor: isDarkMode ? const Color(0xFF3A3A3B) : const Color(0xff133762),
        focusColor: isDarkMode ? const Color(0xFF0B2512) : const Color(0xff133762),
        disabledColor: Colors.grey,
        cardColor: isDarkMode ? const Color(0xFF151515) : Colors.white,
        // canvasColor: Color(0xFF000031),
        brightness: isDarkMode ? Brightness.dark : Brightness.light,
        buttonTheme: Theme.of(context).buttonTheme.copyWith(
            colorScheme: isDarkMode ? const ColorScheme.dark() : const ColorScheme.light()),
        appBarTheme: const AppBarTheme(
          elevation: 0,
        ),
        // dividerColor: Colors.transparent, colorScheme: ColorScheme(background: isDarkMode ? Colors.black : const Color(0xFFF1F5FB))
      );
    }

    return MaterialApp(
        title: 'Patroli Siap x86',
        theme: themeData(false, context),
        home: FutureBuilder(
            future: SharedPreference().getString("accountInfo"),
            builder: (context, snapshot) {
              if (snapshot.hasData && snapshot.data != "") {
                var xData = snapshot.data!.split("|");
                sess.idUser = int.parse(xData[0]);
                sess.NamaUser = xData[1];
                sess.KodeUser = xData[2];
                sess.RecordOwnerID = xData[3];
                sess.LocationID = int.parse(xData[4]);
                sess.shift = xData[5].toString();
                sess.isGantiHari = int.parse(xData[6]);
                sess.NamaPartner = xData[7];
                sess.icon = xData[8];
                // sess.server = _Server;

                // print(SharedPreference().getString("Server"));

                Map oParamShift() {
                  return {
                    'RecordOwnerID': sess.RecordOwnerID.toString(),
                    'LocationID': sess.LocationID.toString()
                  };
                }


                var oShfit = Mod_Shift(sess, oParamShift());

                try {
                  oShfit.getShift().then((value) {
                    if (value["success"].toString() == "true") {
                      sess.jadwalShift = value["data"];
                    }
                  });
                } catch (e) {
                  print(e);
                }

                Map oParamLokasi() {
                  return {
                    'id': sess.LocationID.toString(),
                    'RecordOwnerID': sess.RecordOwnerID,
                  };
                }

                var x = Mod_Patroli(sess, Parameter: oParamLokasi())
                    .readLokasi()
                    .then((value) async {
                  int interval = 0;

                  // int.parse(value["data"][0]["IntervalPatroli"]) * 60;
                  if (value["data"][0]["IntervalType"] == "DAY") {
                    interval =
                        int.parse(value["data"][0]["IntervalPatroli"]) * 1440;
                  } else if (value["data"][0]["IntervalType"] == "HOUR") {
                    interval =
                        int.parse(value["data"][0]["IntervalPatroli"]) * 60;
                  } else if (value["data"][0]["IntervalType"] == "MINUTE") {
                    interval = int.parse(value["data"][0]["IntervalPatroli"]);
                  }
                });
                return Dashboard(sess);
              } else {
                return LoginMobilePotrait(sess);
              }
            }
          )
          
        );
  }
}
