<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tjadwal extends Model
{
    protected $table = 'tjadwal';
    public $timestamps = false;

    protected $fillable = [
        'RecordOwnerID',
        'Tanggal',
        'NIK',
        'Jadwal',
        'StatusKehadiran',
        'Keterangan',
        'CreatedBy',
        'CreatedOn',
        'LastUpdatedBy',
        'LastUpdatedOn',
    ];
}
