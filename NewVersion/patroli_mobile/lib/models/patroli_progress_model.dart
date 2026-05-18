class PatroliProgressModel {
  final int totalCheckpoint;
  final int terlewati;
  final double persentase;
  final String tanggal;

  PatroliProgressModel({
    required this.totalCheckpoint,
    required this.terlewati,
    required this.persentase,
    required this.tanggal,
  });

  factory PatroliProgressModel.fromJson(Map<String, dynamic> json) {
    return PatroliProgressModel(
      totalCheckpoint: json['total_checkpoint'] ?? 0,
      terlewati: json['terlewati'] ?? 0,
      persentase: (json['persentase'] ?? 0).toDouble(),
      tanggal: json['tanggal'] ?? '',
    );
  }
}

class PatroliHistoryItem {
  final int id;
  final String kodeCheckPoint;
  final String? namaCheckPoint;
  final String jam;
  final String tanggalJam;
  final String? koordinat;
  final String? catatan;

  PatroliHistoryItem({
    required this.id,
    required this.kodeCheckPoint,
    this.namaCheckPoint,
    required this.jam,
    required this.tanggalJam,
    this.koordinat,
    this.catatan,
  });

  factory PatroliHistoryItem.fromJson(Map<String, dynamic> json) {
    return PatroliHistoryItem(
      id: json['id'] ?? 0,
      kodeCheckPoint: json['KodeCheckPoint'] ?? '',
      namaCheckPoint: json['NamaCheckPoint'],
      jam: json['Jam'] ?? '',
      tanggalJam: json['TanggalJam'] ?? '',
      koordinat: json['Koordinat'],
      catatan: json['Catatan'],
    );
  }
}