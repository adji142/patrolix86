import 'dart:async';

import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:mobilepatrol/general/session.dart';
import 'package:mobilepatrol/models/dailyactivity.dart';
import 'package:mobilepatrol/page/inputdailyactivity.dart';

class DailyActivity extends StatefulWidget {
  final session sess;
  const DailyActivity(this.sess, {super.key});

  @override
  _DailyActivityState createState() => _DailyActivityState();
}

class _DailyActivityState extends State<DailyActivity> {
  final TextEditingController _TglAwal = TextEditingController();
  final TextEditingController _TglAkhir = TextEditingController();

  String _formatedTglAwal = "";
  String _formatedTglAkhir = "";

  String _RawTglAwal = "";
  String _RawTglAkhir = "";

  DateTime selectedTglAwal = DateTime.now();
  DateTime selectedTglAkhir = DateTime.now();

  List? _dateGuestLog;

  _selectTglAwal(BuildContext context) async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: DateTime.now(),
      firstDate: DateTime(2000),
      lastDate: DateTime(2025),
    );
    if (picked != null && picked != selectedTglAwal) {
      setState(() {
        selectedTglAwal = picked.toLocal();
        //print(selectedDate);
        _TglAwal.text = selectedTglAwal.toString();
      });
    }
  }

  _selectTglAkhir(BuildContext context) async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: DateTime.now(),
      firstDate: DateTime(2000),
      lastDate: DateTime(2025),
    );
    if (picked != null && picked != selectedTglAkhir) {
      setState(() {
        selectedTglAkhir = picked.toLocal();
        //print(selectedDate);
        _TglAkhir.text = selectedTglAkhir.toString();
      });
    }
  }

  Future<Map> _getData(String TglAwal, String TglAkhir) async {
    Map oParam() {
      return {
        "TglAwal": TglAwal,
        "TglAkhir": TglAkhir,
        "KodeLokasi": widget.sess.LocationID.toString(),
        "RecordOwnerID": widget.sess.RecordOwnerID,
        "KodeKaryawan" : widget.sess.KodeUser
      };
    }

    return Mod_DailyActivity(widget.sess, Parameter: oParam()).Read();
  }

  _fetchData(String TglAwal, String TglAkhir) async {
    var temp = await _getData(TglAwal, TglAkhir);
    // print(temp["data"]);
    _dateGuestLog = temp["data"];
    setState(() {
      
    });
  }

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
    _TglAwal.text = DateFormat('dd-MM-yyyy').format(DateTime.now());
    _TglAkhir.text = DateFormat('dd-MM-yyyy').format(DateTime.now());

    _formatedTglAwal = DateFormat('yyyy-MM-dd').format(DateTime.now());
    _formatedTglAkhir = DateFormat('yyyy-MM-dd').format(DateTime.now());
    _fetchData(_formatedTglAwal,_formatedTglAkhir);
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
          "Daily Activity",
          style: TextStyle(color: Colors.white),
        ),
        backgroundColor: Theme.of(context).primaryColor,
      ),
      floatingActionButton: FloatingActionButton(
        backgroundColor: Theme.of(context).primaryColor,
        onPressed: () async{
          await Navigator.push(context,MaterialPageRoute(builder: (context) => InputDailyActivity(widget.sess))).then((value) {
            _fetchData(_formatedTglAwal,_formatedTglAkhir);
          });
        },
        child: const Icon(
          Icons.add,
          color: Colors.white,
        ),
      ),
      body: Column(
        mainAxisAlignment: MainAxisAlignment.start,
        children: [
          Padding(
            padding: EdgeInsets.only(
                left: widget.sess.width * 2,
                right: widget.sess.width * 2,
                top: widget.sess.width * 5),
            child: SizedBox(
              width: double.infinity,
              height: widget.sess.hight * 15,
              // color: Colors.amber,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  SizedBox(
                    width: widget.sess.width * 45,
                    height: widget.sess.hight * 15,
                    // color: Colors.black,
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.start,
                      children: [
                        const Text(
                          "Tanggal Awal :",
                        ),
                        SizedBox(
                          height: widget.sess.hight * 2,
                        ),
                        TextField(
                          controller: _TglAwal,
                          decoration: InputDecoration(
                            icon: Icon(Icons.calendar_today,
                                size: widget.sess.hight * 4,
                                color: Theme.of(context).primaryColor),
                            labelText: "dd/mm/yyyy",
                            labelStyle: TextStyle(
                              color: Theme.of(context).primaryColor,
                              fontSize: widget.sess.hight * 2,
                            ),
                          ),
                          textInputAction: TextInputAction.next,
                          readOnly: true,
                          onTap: () async {
                            DateTime? pickDate = await showDatePicker(
                              context: context,
                              initialDate: DateTime.now(),
                              firstDate: DateTime(1945),
                              lastDate: DateTime(2100),
                            );

                            if (pickDate != null) {
                              setState(() {
                                _formatedTglAwal =
                                    DateFormat('yyyy-MM-dd').format(pickDate);
                                _RawTglAwal =
                                    DateFormat('dd-MM-yyyy').format(pickDate);

                                _TglAwal.text = _RawTglAwal;
                              });
                            }
                          },
                        )
                      ],
                    ),
                  ),
                  SizedBox(
                    width: widget.sess.width * 2,
                  ),
                  SizedBox(
                      width: widget.sess.width * 45,
                      height: widget.sess.hight * 15,
                      // color: Colors.black,
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.start,
                        children: [
                          const Text(
                            "Tanggal Akhir :",
                          ),
                          SizedBox(
                            height: widget.sess.hight * 2,
                          ),
                          TextField(
                            controller: _TglAkhir,
                            decoration: InputDecoration(
                              icon: Icon(Icons.calendar_today,
                                  size: widget.sess.hight * 4,
                                  color: Theme.of(context).primaryColor),
                              labelText: "dd/mm/yyyy",
                              labelStyle: TextStyle(
                                color: Theme.of(context).primaryColor,
                                fontSize: widget.sess.hight * 2,
                              ),
                            ),
                            textInputAction: TextInputAction.next,
                            readOnly: true,
                            onTap: () async {
                              DateTime? pickDate = await showDatePicker(
                                context: context,
                                initialDate: DateTime.now(),
                                firstDate: DateTime(1945),
                                lastDate: DateTime(2100),
                              );

                              if (pickDate != null) {
                                setState(() {
                                  _formatedTglAkhir =
                                      DateFormat('yyyy-MM-dd').format(pickDate);
                                  _RawTglAkhir =
                                      DateFormat('dd-MM-yyyy').format(pickDate);

                                  _TglAkhir.text = _RawTglAkhir;
                                });
                              }
                            },
                          )
                        ],
                      ))
                ],
              ),
            ),
          ),
          Padding(
            padding: EdgeInsets.only(
                left: widget.sess.width * 2,
                right: widget.sess.width * 2,
                top: widget.sess.width * 1),
            child: SizedBox(
              width: double.infinity,
              height: widget.sess.hight * 5,
              // color: Colors.amberAccent,
              child: ElevatedButton(
                style: ButtonStyle(
                    shape: MaterialStateProperty.all<RoundedRectangleBorder>(
                        RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(18.0),
                    )),
                    backgroundColor: MaterialStateProperty.all(
                        Theme.of(context).primaryColor)),
                onPressed: () {
                  _fetchData(_formatedTglAwal,_formatedTglAkhir);
                },
                child: const Text(
                  "Refresh Data",
                  style: TextStyle(color: Colors.white),
                ),
              ),
            ),
          ),
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
                height: widget.sess.hight * 63,
                // color: Colors.amber,
                child: ListView.builder(
                    itemCount: _dateGuestLog == null ? 0 : _dateGuestLog!.length,
                    itemBuilder: (context, index) {
                      return Card(
                        child: ListTile(
                          title: Text(
                            _dateGuestLog![index]["NamaKaryawan"] ,
                            style: TextStyle(
                              fontSize: widget.sess.width * 5,
                              fontFamily: "Arial",
                              fontWeight: FontWeight.bold
                            ),
                          ),
                          subtitle: Text("Tanggal : ${DateFormat('dd-MM-yy').format(DateTime.parse(_dateGuestLog![index]["Tanggal"] ))}"),
                          leading: CircleAvatar(
                            child: Text((index + 1).toString()),
                          ),
                          onTap: () async{
                            await Navigator.push(context,MaterialPageRoute(builder: (context) => InputDailyActivity(widget.sess, iddata: int.parse(_dateGuestLog![index]["id"]),))).then((value) {
                              _fetchData(_formatedTglAwal,_formatedTglAkhir);
                            });
                          },
                        ),
                      );
                    }),
              ),
            ),
          )
        ],
      ),
    );
  }
}
