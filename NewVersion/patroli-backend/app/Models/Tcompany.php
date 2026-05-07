<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tcompany extends Model
{
    protected $table = 'tcompany';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'KodePartner';
    protected $keyType = 'string';

    protected $fillable = [
        'KodePartner',
        'NamaPartner',
        'AlamatTagihan',
        'NoTlp',
        'NoHP',
        'NIKPIC',
        'NamaPIC',
        'CreatedOn',
        'CreatedBy',
        'LastUpdatedOn',
        'LastUpdatedBy',
        'tempStore',
        'icon',
        'StartSubs',
        'EndSubs',
        'ExtraDays',
        'AllowMobile',
        'AllowDashboard',
        'AllowFaceRecognition',
    ];
}
