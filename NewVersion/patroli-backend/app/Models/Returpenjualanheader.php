<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Returpenjualanheader extends Model
{
    protected $table = 'returpenjualanheader';
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = [
        'Periode',
        'NoTransaksi',
        'TglTransaksi',
        'NoReff',
        'KodeSupplier',
        'TotalTransaksi',
        'Status',
        'Keterangan',
        'Posted',
        'CreatedBy',
        'UpdatedBy',
        'RecordOwnerID',
        'created_at',
        'updated_at',
    ];
}
