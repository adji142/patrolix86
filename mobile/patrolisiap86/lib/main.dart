import 'package:awesome_notifications/awesome_notifications.dart';
import 'package:flutter/material.dart';
import 'package:mobilepatrol/general/notification_service.dart';
// import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:mobilepatrol/general/session.dart';
import 'package:mobilepatrol/models/patroli.dart';
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
  static ReceivedAction? initialAction;
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

  static Future<bool> displayNotificationRationale(BuildContext context) async {
    bool userAuthorized = false;
    // BuildContext context = MyApp.navigatorKey.currentContext!;
    await showDialog(
        context: context,
        builder: (BuildContext ctx) {
          return AlertDialog(
            title: Text('Get Notified!',
                style: Theme.of(context).textTheme.titleLarge),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: Image.asset(
                        'assets/animated-bell.gif',
                        height: MediaQuery.of(context).size.height * 0.3,
                        fit: BoxFit.fitWidth,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 20),
                const Text(
                    'Izinkan aplikasi untuk memberi notifikasi!'),
              ],
            ),
            actions: [
              TextButton(
                  onPressed: () {
                    Navigator.of(ctx).pop();
                  },
                  child: Text(
                    'Deny',
                    style: Theme.of(context)
                        .textTheme
                        .titleLarge
                        ?.copyWith(color: Colors.red),
                  )),
              TextButton(
                  onPressed: () async {
                    userAuthorized = true;
                    Navigator.of(ctx).pop();
                  },
                  child: Text(
                    'Allow',
                    style: Theme.of(context)
                        .textTheme
                        .titleLarge
                        ?.copyWith(color: Colors.deepPurple),
                  )),
            ],
          );
        });
    return userAuthorized &&
        await AwesomeNotifications().requestPermissionToSendNotifications();
  }

  static Future<void> createNewNotification(int x, String StartPatroli, BuildContext context) async {
    bool isAllowed = await AwesomeNotifications().isNotificationAllowed();
    if (!isAllowed) isAllowed = await displayNotificationRationale(context);
    if (!isAllowed) return;
    String localTimeZone = await AwesomeNotifications().getLocalTimeZoneIdentifier();
    // print(localTimeZone);
    var listTime = StartPatroli.split(":");
    print("Hit the planet : "+ x.toString());
    await AwesomeNotifications().createNotification(
        content: NotificationContent(
            id: -1, // -1 is replaced by a random number
            channelKey: 'alerts',
            title: 'Informasi Patroli',
            body: "Sudah Waktunya Patroli Lagi.!!!",
            notificationLayout: NotificationLayout.Messaging,
            payload: {'notificationId': '1234567890'}),
            // schedule: NotificationCalendar.fromDate(
            //   // date: DateTime(DateTime.now().year,DateTime.now().month,DateTime.now().day,int.parse(listTime[0]),int.parse(listTime[1]), 0).add(Duration(minutes: x == null ? 0 : x)),
            //   // minutes: x == null ? 0 : x
            //   date: DateTime.now().add(Duration(seconds: 10)),
            //   repeats: true,
            //   preciseAlarm: true,
            //   allowWhileIdle: true
            // ),
            schedule: NotificationCalendar(
              second: 10,
              timeZone: localTimeZone,
              preciseAlarm: true,
              repeats: true
            )
          );
  }
  
}

class MyApp extends StatefulWidget {
  static final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();
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

            Map oParamLokasi(){
              return {
                'id' : sess.LocationID.toString(),
                'RecordOwnerID':sess.RecordOwnerID,
              };
            }
            var x = Mod_Patroli(sess, Parameter: oParamLokasi()).readLokasi().then((value) async {
              int interval = 0;

              // int.parse(value["data"][0]["IntervalPatroli"]) * 60;
              if(value["data"][0]["IntervalType"] == "DAY"){
                interval = int.parse(value["data"][0]["IntervalPatroli"]) * 1440;
              }
              else if(value["data"][0]["IntervalType"] == "HOUR"){
                interval = int.parse(value["data"][0]["IntervalPatroli"]) * 60;
              }
              else if(value["data"][0]["IntervalType"] == "MINUTE"){
                interval = int.parse(value["data"][0]["IntervalPatroli"]);
              }
              NotificationController.createNewNotification(interval,value["data"][0]["StartPatroli"], context);
            });
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
