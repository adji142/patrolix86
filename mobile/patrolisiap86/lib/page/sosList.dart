import 'dart:async';

import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:mobilepatrol/general/session.dart';
import 'package:mobilepatrol/models/guestlog.dart';
import 'package:mobilepatrol/models/sos.dart';
import 'package:mobilepatrol/page/inputguestlog.dart';
import 'package:mobilepatrol/page/sosCallback.dart';

class SOSList extends StatefulWidget {
  final session sess;
  const SOSList(this.sess, {super.key});

  @override
  _SOSListState createState() => _SOSListState();
}

class _SOSListState extends State<SOSList> {

  Future _refreshData() async {
    setState(() {});

    Completer<void> completer = Completer<void>();
    Future.delayed(const Duration(seconds: 1)).then((_) {
      completer.complete();
    });
    return completer.future;
  }

  @override
  void initState() {
    super.initState();
  }

  @override
  void dispose() {
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          "Daftar Pesan SOS",
          style: TextStyle(color: Colors.white),
        ),
        backgroundColor: Theme.of(context).primaryColor,
      ),
      body: Column(
        mainAxisAlignment: MainAxisAlignment.start,
        children: [
          Padding(
            padding: EdgeInsets.only(
              top: widget.sess.hight * 2,
              left: widget.sess.hight * 2,
              right: widget.sess.hight * 2,
            ),
            child: RefreshIndicator(
              onRefresh: () => _refreshData(),
              child: SizedBox(
                width: double.infinity,
                height: widget.sess.hight * 85,
                // color: Colors.amber,
                child: FutureBuilder(
                  future: Mod_SOS(this.widget.sess, {"RecordOwnerID": this.widget.sess.RecordOwnerID, "KodeLokasi":this.widget.sess.LocationID.toString()}).getData(), 
                  builder: (context, snapshot){
                    if (snapshot.hasData) {
                      return ListView.builder(
                        itemCount: snapshot.data!["data"] == null ? 0 : snapshot.data!["data"].length,
                        itemBuilder: (context, index) {
                          return Card(
                            child: ListTile(
                              title: Text(
                                snapshot.data!["data"]![index]["NamaSecurity"] + " >> " + snapshot.data!["data"]![index]["NamaArea"],
                                style: TextStyle(
                                  fontSize: widget.sess.width * 5,
                                  fontFamily: "Arial",
                                  fontWeight: snapshot.data!["data"]![index]["isRead"] == "0" ? FontWeight.bold : FontWeight.normal
                                ),
                              ),
                              subtitle: Text(snapshot.data!["data"]![index]["Comment"]),
                              leading: CircleAvatar(
                                child: Text((index + 1).toString()),
                              ),
                              trailing: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(Icons.arrow_forward_ios)
                                ],
                              ),
                              onTap: () async{
                                Navigator.push(context,MaterialPageRoute(builder: (context) => sosCallBack(widget.sess, snapshot.data!["data"]![index]["id"])));
                              },
                            ),
                          );
                        }
                      );
                    }
                    else{
                      return Container();
                    }
                  }
                )
              ),
            ),
          )
        ],
      ),
    );
  }
}
