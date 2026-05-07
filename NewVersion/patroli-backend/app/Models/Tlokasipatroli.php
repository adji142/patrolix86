<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tlokasipatroli extends Model
{
    protected $table = 'tlokasipatroli';
    public $timestamps = false;

    protected $fillable = [
        'NamaArea',
        'AlamatArea',
        'Keterangan',
        'RecordOwnerID',
        'Latitude',
        'Longitude',
        'Radius',
    ];
}