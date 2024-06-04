import 'package:sqflite/sqflite.dart';
import 'package:path/path.dart';

class LocalDatabaseHelper {
  final String _tableName;
  final Map<String, String> _column;
  LocalDatabaseHelper(this._tableName, this._column);

  static Database? _database;
  static const _dbName = "offlinedata2.db";

  Future<Database> get database async{
    if (_database != null) return _database!;
    _database = await _initDatabase();
    return _database!;
  }

  Future<Database> _initDatabase() async{
    final path = join(await getDatabasesPath(), _dbName);
    return await openDatabase(
      path,
      version: 1,
      onCreate: ((db, version) async {
        String query = "";

        _column.forEach((key, value) { 
          query += "$key $value,";
        });

        query = query.substring(0, query.length - 1);
        // await db.execute("DROP TABLE IF EXISTS $_tableName");
        return db.execute(''' 
            CREATE TABLE $_tableName ($query)
          ''');
      })
    );

    
  }

  Future<void> insertData(Database dbx,Map<String,dynamic> oParam) async{
    // final db = await database;
    await dbx.insert(
      _tableName,
      oParam,
      conflictAlgorithm: ConflictAlgorithm.replace
    );
  }

  Future<List<Map<String, dynamic>>> getData(Database dbx) async{
    // final db = await database;
    return await dbx.query(_tableName);
  }
  
  Future <void> updateData(Database dbx,Map<String,dynamic> oParam,String whereString, List whereargs) async{
    // final db = await database;

    await dbx.update(_tableName, oParam, where: whereString, whereArgs: whereargs);
  }

  Future<void> deleteData(Database dbx) async{
    // final db = await database;
    await dbx.delete(_tableName);
  }
}
