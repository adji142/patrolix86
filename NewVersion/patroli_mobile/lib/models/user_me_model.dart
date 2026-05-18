class LokasiPatroliModel {
  final int id;
  final String namaArea;
  final String? alamatArea;
  final String? keterangan;
  final String? latitude;
  final String? longitude;
  final String? radius;
  final int allowJadwalKerja;

  LokasiPatroliModel({
    required this.id,
    required this.namaArea,
    this.alamatArea,
    this.keterangan,
    this.latitude,
    this.longitude,
    this.radius,
    this.allowJadwalKerja = 0,
  });

  factory LokasiPatroliModel.fromJson(Map<String, dynamic> json) =>
      LokasiPatroliModel(
        id: json['id'] ?? 0,
        namaArea: json['NamaArea'] ?? '',
        alamatArea: json['AlamatArea'],
        keterangan: json['Keterangan'],
        latitude: json['Latitude']?.toString(),
        longitude: json['Longitude']?.toString(),
        radius: json['Radius']?.toString(),
        allowJadwalKerja: (json['AllowJadwalKerja'] ?? 0) as int,
      );

  Map<String, dynamic> toJson() => {
        'id': id,
        'NamaArea': namaArea,
        'AlamatArea': alamatArea,
        'Keterangan': keterangan,
        'Latitude': latitude,
        'Longitude': longitude,
        'Radius': radius,
        'AllowJadwalKerja': allowJadwalKerja,
      };
}

class RoleModel {
  final int id;
  final String rolename;

  RoleModel({required this.id, required this.rolename});

  factory RoleModel.fromJson(Map<String, dynamic> json) => RoleModel(
        id: json['id'] ?? 0,
        rolename: json['rolename'] ?? '',
      );

  Map<String, dynamic> toJson() => {'id': id, 'rolename': rolename};
}

class SecurityModel {
  final String nik;
  final String namaSecurity;
  final String? joinDate;
  final int locationID;
  final int status;
  final String recordOwnerID;
  final int shift;
  final String? image;

  SecurityModel({
    required this.nik,
    required this.namaSecurity,
    this.joinDate,
    required this.locationID,
    required this.status,
    required this.recordOwnerID,
    required this.shift,
    this.image,
  });

  factory SecurityModel.fromJson(Map<String, dynamic> json) => SecurityModel(
        nik: json['NIK'] ?? '',
        namaSecurity: json['NamaSecurity'] ?? '',
        joinDate: json['JoinDate'],
        locationID: json['LocationID'] ?? 0,
        status: json['Status'] ?? 0,
        recordOwnerID: json['RecordOwnerID'] ?? '',
        shift: json['Shift'] ?? 0,
        image: json['Image'],
      );

  Map<String, dynamic> toJson() => {
        'NIK': nik,
        'NamaSecurity': namaSecurity,
        'JoinDate': joinDate,
        'LocationID': locationID,
        'Status': status,
        'RecordOwnerID': recordOwnerID,
        'Shift': shift,
        'Image': image,
      };
}

class ActiveShiftDetailsModel {
  final String namaShift;
  final String mulaiBekerja;
  final String selesaiBekerja;

  ActiveShiftDetailsModel({
    required this.namaShift,
    required this.mulaiBekerja,
    required this.selesaiBekerja,
  });

  factory ActiveShiftDetailsModel.fromJson(Map<String, dynamic> json) =>
      ActiveShiftDetailsModel(
        namaShift: json['NamaShift'] ?? '',
        mulaiBekerja: json['MulaiBekerja'] ?? '',
        selesaiBekerja: json['SelesaiBekerja'] ?? '',
      );

  Map<String, dynamic> toJson() => {
        'NamaShift': namaShift,
        'MulaiBekerja': mulaiBekerja,
        'SelesaiBekerja': selesaiBekerja,
      };
}

class UserMeModel {
  final bool success;
  final int uniqueId;
  final String username;
  final String namaUser;
  final String? email;
  final String? phone;
  final String recordOwnerID;
  final String locationID;
  final String? hakAkses;
  final RoleModel? role;
  final String? namaPartner;
  final String? icon;
  final int allowFaceRecognition;
  final int? allowMobile;
  final List<dynamic> menus;
  final SecurityModel? security;
  final String? fotoSecurity;
  final int? shift;
  final int isGantiHari;
  final ActiveShiftDetailsModel? activeShiftDetails;
  final LokasiPatroliModel? lokasiPatroli;

  UserMeModel({
    required this.success,
    required this.uniqueId,
    required this.username,
    required this.namaUser,
    this.email,
    this.phone,
    required this.recordOwnerID,
    required this.locationID,
    this.hakAkses,
    this.role,
    this.namaPartner,
    this.icon,
    required this.allowFaceRecognition,
    this.allowMobile,
    required this.menus,
    this.security,
    this.fotoSecurity,
    this.shift,
    required this.isGantiHari,
    this.activeShiftDetails,
    this.lokasiPatroli,
  });

  factory UserMeModel.fromJson(Map<String, dynamic> json) => UserMeModel(
        success: json['success'] ?? false,
        uniqueId: json['unique_id'] ?? 0,
        username: json['username'] ?? '',
        namaUser: json['NamaUser'] ?? '',
        email: json['email'],
        phone: json['phone'],
        recordOwnerID: json['RecordOwnerID'] ?? '',
        locationID: json['LocationID']?.toString() ?? '',
        hakAkses: json['HakAkses'],
        role: json['role'] != null
            ? RoleModel.fromJson(json['role'] as Map<String, dynamic>)
            : null,
        namaPartner: json['NamaPartner'],
        icon: json['icon'],
        allowFaceRecognition: json['AllowFaceRecognition'] ?? 0,
        allowMobile: json['AllowMobile'],
        menus: json['menus'] ?? [],
        security: json['security'] != null
            ? SecurityModel.fromJson(json['security'] as Map<String, dynamic>)
            : null,
        fotoSecurity: json['FotoSecurity'],
        shift: json['Shift'],
        isGantiHari: json['isGantiHari'] ?? 0,
        activeShiftDetails: json['ActiveShiftDetails'] != null
            ? ActiveShiftDetailsModel.fromJson(
                json['ActiveShiftDetails'] as Map<String, dynamic>)
            : null,
        lokasiPatroli: json['lokasiPatroli'] != null
            ? LokasiPatroliModel.fromJson(
                json['lokasiPatroli'] as Map<String, dynamic>)
            : null,
      );

  Map<String, dynamic> toJson() => {
        'success': success,
        'unique_id': uniqueId,
        'username': username,
        'NamaUser': namaUser,
        'email': email,
        'phone': phone,
        'RecordOwnerID': recordOwnerID,
        'LocationID': locationID,
        'HakAkses': hakAkses,
        'role': role?.toJson(),
        'NamaPartner': namaPartner,
        'icon': icon,
        'AllowFaceRecognition': allowFaceRecognition,
        'AllowMobile': allowMobile,
        'menus': menus,
        'security': security?.toJson(),
        'FotoSecurity': fotoSecurity,
        'Shift': shift,
        'isGantiHari': isGantiHari,
        'ActiveShiftDetails': activeShiftDetails?.toJson(),
        'lokasiPatroli': lokasiPatroli?.toJson(),
      };

  UserMeModel copyWith({
    bool? success,
    int? uniqueId,
    String? username,
    String? namaUser,
    String? email,
    String? phone,
    String? recordOwnerID,
    String? locationID,
    String? hakAkses,
    RoleModel? role,
    String? namaPartner,
    String? icon,
    int? allowFaceRecognition,
    int? allowMobile,
    List<dynamic>? menus,
    SecurityModel? security,
    String? fotoSecurity,
    int? shift,
    int? isGantiHari,
    ActiveShiftDetailsModel? activeShiftDetails,
    LokasiPatroliModel? lokasiPatroli,
  }) {
    return UserMeModel(
      success: success ?? this.success,
      uniqueId: uniqueId ?? this.uniqueId,
      username: username ?? this.username,
      namaUser: namaUser ?? this.namaUser,
      email: email ?? this.email,
      phone: phone ?? this.phone,
      recordOwnerID: recordOwnerID ?? this.recordOwnerID,
      locationID: locationID ?? this.locationID,
      hakAkses: hakAkses ?? this.hakAkses,
      role: role ?? this.role,
      namaPartner: namaPartner ?? this.namaPartner,
      icon: icon ?? this.icon,
      allowFaceRecognition: allowFaceRecognition ?? this.allowFaceRecognition,
      allowMobile: allowMobile ?? this.allowMobile,
      menus: menus ?? this.menus,
      security: security ?? this.security,
      fotoSecurity: fotoSecurity ?? this.fotoSecurity,
      shift: shift ?? this.shift,
      isGantiHari: isGantiHari ?? this.isGantiHari,
      activeShiftDetails: activeShiftDetails ?? this.activeShiftDetails,
      lokasiPatroli: lokasiPatroli ?? this.lokasiPatroli,
    );
  }
}