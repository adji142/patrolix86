<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensi';
    public $timestamps = false;

    protected $fillable = [
        'RecordOwnerID',
        'LocationID',
        'KodeKaryawan',
        'KoordinatIN',
        'ImageIN',
        'KoordinatOUT',
        'ImageOUT',
        'Tanggal',
        'Shift',
        'Checkin',
        'CheckOut',
        'CreatedOn',
        'UpdatedOn',
    ];
}
