class JadwalShift {
  final int id;
  final String namaShift;
  final String mulaiBekerja;
  final String selesaiBekerja;
  final int intervalPatroli;
  final String intervalType;
  final int toleransi;
  final String recordOwnerID;
  final int locationID;
  final int gantiHari;
  final String mulaiAbsen;
  final String maxAbsen;

  JadwalShift({
    required this.id,
    required this.namaShift,
    required this.mulaiBekerja,
    required this.selesaiBekerja,
    required this.intervalPatroli,
    required this.intervalType,
    required this.toleransi,
    required this.recordOwnerID,
    required this.locationID,
    required this.gantiHari,
    required this.mulaiAbsen,
    required this.maxAbsen,
  });

  factory JadwalShift.fromJson(Map<String, dynamic> json) {
    return JadwalShift(
      id: json['id'] ?? 0,
      namaShift: json['NamaShift'] ?? '',
      mulaiBekerja: json['MulaiBekerja'] ?? '',
      selesaiBekerja: json['SelesaiBekerja'] ?? '',
      intervalPatroli: json['IntervalPatroli'] ?? 0,
      intervalType: json['IntervalType'] ?? '',
      toleransi: json['Toleransi'] ?? 0,
      recordOwnerID: json['RecordOwnerID'] ?? '',
      locationID: json['LocationID'] ?? 0,
      gantiHari: json['GantiHari'] ?? 0,
      mulaiAbsen: json['MulaiAbsen'] ?? '',
      maxAbsen: json['MaxAbsen'] ?? '',
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'NamaShift': namaShift,
        'MulaiBekerja': mulaiBekerja,
        'SelesaiBekerja': selesaiBekerja,
        'IntervalPatroli': intervalPatroli,
        'IntervalType': intervalType,
        'Toleransi': toleransi,
        'RecordOwnerID': recordOwnerID,
        'LocationID': locationID,
        'GantiHari': gantiHari,
        'MulaiAbsen': mulaiAbsen,
        'MaxAbsen': maxAbsen,
      };
}

class UserModel {
  final bool success;
  final String message;
  final String username;
  final int uniqueId;
  final String recordOwnerID;
  final String namaPartner;
  final String locationID;
  final String namaUser;
  final String icon;
  final int allowFaceRecognition;
  final int shift;
  final int isGantiHari;
  final List<JadwalShift> jadwalShift;
  final String token;
  final String fotoSecurity;

  UserModel({
    required this.success,
    required this.message,
    required this.username,
    required this.uniqueId,
    required this.recordOwnerID,
    required this.namaPartner,
    required this.locationID,
    required this.namaUser,
    required this.icon,
    required this.allowFaceRecognition,
    required this.shift,
    required this.isGantiHari,
    required this.jadwalShift,
    required this.token,
    required this.fotoSecurity,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      success: json['success'] ?? false,
      message: json['message'] ?? '',
      username: json['username'] ?? '',
      uniqueId: json['unique_id'] ?? 0,
      recordOwnerID: json['RecordOwnerID'] ?? '',
      namaPartner: json['NamaPartner'] ?? '',
      locationID: json['LocationID']?.toString() ?? '',
      namaUser: json['NamaUser'] ?? '',
      icon: json['icon'] ?? '',
      allowFaceRecognition: json['AllowFaceRecognition'] ?? 0,
      shift: json['Shift'] ?? 0,
      isGantiHari: json['isGantiHari'] ?? 0,
      jadwalShift: (json['JadwalShift'] as List<dynamic>?)
              ?.map((e) => JadwalShift.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
      token: json['token'] ?? '',
      fotoSecurity: json['FotoSecurity'] ?? '',
    );
  }

  Map<String, dynamic> toJson() => {
        'success': success,
        'message': message,
        'username': username,
        'unique_id': uniqueId,
        'RecordOwnerID': recordOwnerID,
        'NamaPartner': namaPartner,
        'LocationID': locationID,
        'NamaUser': namaUser,
        'icon': icon,
        'AllowFaceRecognition': allowFaceRecognition,
        'Shift': shift,
        'isGantiHari': isGantiHari,
        'JadwalShift': jadwalShift.map((e) => e.toJson()).toList(),
        'token': token,
        'FotoSecurity': fotoSecurity,
      };
}