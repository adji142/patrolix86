<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanIzinFoto extends Model
{
    protected $table = 'pengajuan_izin_foto';
    public $timestamps = false;

    protected $fillable = [
        'pengajuan_izin_id',
        'FileName',
    ];

    public function pengajuanIzin()
    {
        return $this->belongsTo(PengajuanIzin::class, 'pengajuan_izin_id');
    }
}