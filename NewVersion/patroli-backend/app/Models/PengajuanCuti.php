<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanCuti extends Model
{
    protected $table = 'pengajuan_cuti';
    public $timestamps = false;

    protected $fillable = [
        'RecordOwnerID',
        'KodeKaryawan',
        'TglCutiAwal',
        'TglCutiAkhir',
        'TglPencatatan',
        'KeteranganCuti',
        'KategoriCuti',
        'Approval',
        'ApprovedBy',
        'ApprovedOn',
        'CatatanApproval',
        'CreatedOn',
        'UpdatedOn',
    ];

    public function fotos()
    {
        return $this->hasMany(PengajuanCutiFoto::class, 'pengajuan_cuti_id');
    }
}