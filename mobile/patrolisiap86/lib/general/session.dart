class session {
  // String server = "http://patroli.aissystem.org/"; // Production
  // String server = "http://192.168.43.20:8080/patrolisiap86/"; // Development 1
  //String server = "http://192.168.1.66:8080/patrolisiap86/"; // Development 2
  String server = "http://192.168.10.66:8080/patrolisiap86/"; // Development 3
  int idUser = -1;
  String KodeUser = "";
  String NamaUser = "";
  String Email = "";
  String Token = "";
  String RecordOwnerID = "";
  int LocationID = -1;
  int roleID = -1;
  double hight = 0;
  double width = 0;
  String orientation = "";
  int interval = 0;
  String shift = "";
  int isGantiHari = 0;
  List jadwalShift = [];
}
