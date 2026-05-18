<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanCutiFoto extends Model
{
    protected $table = 'pengajuan_cuti_foto';
    public $timestamps = false;

    protected $fillable = [
        'pengajuan_cuti_id',
        'FileName',
    ];

    public function cuti()
    {
        return $this->belongsTo(PengajuanCuti::class, 'pengajuan_cuti_id');
    }
}