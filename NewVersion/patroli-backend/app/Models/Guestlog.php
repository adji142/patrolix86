<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guestlog extends Model
{
    protected $table = 'guestlog';
    public $timestamps = false;

    protected $fillable = [
        'Tanggal',
        'TglMasuk',
        'TglKeluar',
        'ImageIn',
        'ImageOut',
        'RecordOwnerID',
        'LocationID',
        'Keterangan',
        'CreatedAt',
        'LastUpdatedAt',
        'NamaTamu',
        'NamaYangDicari',
        'Tujuan',
    ];
}
