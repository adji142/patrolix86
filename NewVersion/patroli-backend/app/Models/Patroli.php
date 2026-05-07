<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patroli extends Model
{
    protected $table = 'patroli';
    public $timestamps = false;

    protected $fillable = [
        'RecordOwnerID',
        'KodeCheckPoint',
        'LocationID',
        'TanggalPatroli',
        'KodeKaryawan',
        'Koordinat',
        'Image',
        'Catatan',
        'Rank',
        'Shift',
        'ShiftJadwal',
    ];
}
