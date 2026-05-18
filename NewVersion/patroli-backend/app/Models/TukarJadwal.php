<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TukarJadwal extends Model
{
    protected $table = 'tukar_jadwal';

    protected $fillable = [
        'RecordOwnerID',
        'KodeKaryawan',
        'TanggalTukar',
        'TglPencatatan',
        'Keterangan',
        'Approval',
        'ApprovedBy',
        'ApprovedOn',
        'idJadwalAwal',
        'idJadwalBaru',
        'TargetShiftID',
        'IsToOff',
    ];

    public function targetShift()
    {
        return $this->belongsTo(Tshift::class, 'TargetShiftID');
    }

    public function fotos()
    {
        return $this->hasMany(TukarJadwalFoto::class, 'tukar_jadwal_id');
    }

    public function jadwalAwal()
    {
        return $this->belongsTo(JadwalKerja::class, 'idJadwalAwal');
    }

    public function jadwalBaru()
    {
        return $this->belongsTo(JadwalKerja::class, 'idJadwalBaru');
    }

    public function security()
    {
        return $this->belongsTo(Tsecurity::class, 'KodeKaryawan', 'NIK');
    }
}
