<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dailyactivity extends Model
{
    protected $table = 'dailyactivity';
    public $timestamps = false;

    protected $fillable = [
        'Tanggal',
        'DeskripsiAktifitas',
        'Gambar1',
        'Gambar2',
        'Gambar3',
        'CreatedOn',
        'UpdatedOn',
        'RecordOwnerID',
        'LocationID',
        'KodeKaryawan',
        'NamaKaryawan',
    ];
}
