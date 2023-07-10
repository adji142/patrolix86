import 'package:http/http.dart' as http;
import 'dart:convert';

import 'package:patrolisiap86/general/session.dart';

class Mod_Patroli {
  session? sess;
  Map? Parameter;

  Mod_Patroli(this.sess, {this.Parameter});

  Future<Map> getPatroliList() async{
    var url = Uri.parse(sess!.server+"APIReadPatroli");
    final response = await http.post(url,body: this.Parameter);
    return json.decode(response.body);
  }
}