<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalKerja extends Model
{
    protected $table = 'jadwalkerja';

    protected $fillable = [
        'KodeKaryawan',
        'Tanggal',
        'Tgl',
        'Bulan',
        'Tahun',
        'shiftid',
        'StatusKehadiran',
        'KeteranganStatusKehadiran',
        'RecordOwnerID',
    ];

    public function shift()
    {
        return $this->belongsTo(Tshift::class, 'shiftid');
    }

    public function security()
    {
        return $this->belongsTo(Tsecurity::class, 'KodeKaryawan', 'NIK');
    }
}