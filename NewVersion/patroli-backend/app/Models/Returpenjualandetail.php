<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Returpenjualandetail extends Model
{
    protected $table = 'returpenjualandetail';
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = [
        'NoTransaksi',
        'BaseReff',
        'NoUrut',
        'BaseLine',
        'KodeItem',
        'Qty',
        'Satuan',
        'Harga',
        'HargaNet',
        'LineStatus',
        'KodeGudang',
        'RecordOwnerID',
        'created_at',
        'updated_at',
    ];
}
