import 'package:awesome_notifications/awesome_notifications.dart';
import 'package:flutter/material.dart';
import 'package:mobilepatrol/general/session.dart';
import 'package:mobilepatrol/page/dashboard.dart';
import 'package:mobilepatrol/page/login.dart';
import 'package:mobilepatrol/shared/sharedprefrence.dart';
import 'package:shared_preferences/shared_preferences.dart';
Future<void> main() async{
  WidgetsFlutterBinding.ensureInitialized();
  await NotificationController.initializeLocalNotifications();
  runApp(MyApp());
}

class NotificationController {
    static Future<void> initializeLocalNotifications() async {
    await AwesomeNotifications().initialize(
        null, //'resource://drawable/res_app_icon',//
        [
          NotificationChannel(
              channelKey: 'alerts',
              channelName: 'Alerts',
              channelDescription: 'Notification tests as alerts',
              playSound: true,
              onlyAlertOnce: true,
              groupAlertBehavior: GroupAlertBehavior.Children,
              importance: NotificationImportance.High,
              defaultPrivacy: NotificationPrivacy.Private,
              defaultColor: Colors.deepPurple,
              ledColor: Colors.deepPurple)
        ],
        debug: true);
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
              date: DateTime(DateTime.now().year,DateTime.now().month,DateTime.now().day,int.parse(listTime[0]),int.parse(listTime[1]), int.parse(listTime[2])).add(Duration(minutes: x == null ? 0 : x)),
              repeats: true,
              preciseAlarm: true,
              allowWhileIdle: true
            ),
          );
  }
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
