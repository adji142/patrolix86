<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanIzin extends Model
{
    protected $table = 'pengajuan_izin';
    public $timestamps = false;

    protected $fillable = [
        'RecordOwnerID',
        'KodeKaryawan',
        'TglIzinAwal',
        'TglIzinAkhir',
        'TglPencatatan',
        'KeteranganIzin',
        'Approval',
        'ApprovedBy',
        'ApprovedOn',
        'CatatanApproval',
        'CreatedOn',
        'UpdatedOn',
    ];

    public function fotos()
    {
        return $this->hasMany(PengajuanIzinFoto::class, 'pengajuan_izin_id');
    }
}