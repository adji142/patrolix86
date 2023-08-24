import 'package:flutter_local_notifications/flutter_local_notifications.dart';

class NotificationService{
  static final NotificationService _notificationService = NotificationService._internal();

  factory NotificationService(){
    return _notificationService;
  }

  NotificationService._internal();

  final FlutterLocalNotificationsPlugin flutterLocalNotificationsPlugin = FlutterLocalNotificationsPlugin();

  Future <void> init(int id, String title, String message) async{
    final AndroidInitializationSettings androidInitializationSettings = AndroidInitializationSettings('app_icon');

    final InitializationSettings initializationSettings = InitializationSettings(
      android: androidInitializationSettings
    );
    await flutterLocalNotificationsPlugin.initialize(
      initializationSettings
    );

    const AndroidNotificationDetails androidPlatformChannelSpecifics = 
    AndroidNotificationDetails(
        "channelId_1",
        "channelName",
        channelDescription: "",
        importance: Importance.max,
        // sound: UriAndroidNotificationSound("sound/alert.mp3")
        sound: RawResourceAndroidNotificationSound("alert")
    );

    const NotificationDetails platfomChanelSpesific = NotificationDetails(
      android: androidPlatformChannelSpecifics,
    );

    await flutterLocalNotificationsPlugin.show(
      id, title, message, platfomChanelSpesific
    );
  }
  

}