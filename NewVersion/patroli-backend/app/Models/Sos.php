<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sos extends Model
{
    protected $table = 'sos';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'RecordOwnerID',
        'LocationID',
        'KodeKaryawan',
        'Comment',
        'Image1',
        'Image2',
        'Image3',
        'Koordinat',
        'SubmitDate',
        'VoiceNote',
        'isRead',
    ];
}
