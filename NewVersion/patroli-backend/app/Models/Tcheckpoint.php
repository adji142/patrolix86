<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tcheckpoint extends Model
{
    protected $table = 'tcheckpoint';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'KodeCheckPoint';
    protected $keyType = 'string';

    protected $fillable = [
        'KodeCheckPoint',
        'NamaCheckPoint',
        'Keterangan',
        'LocationID',
        'RecordOwnerID',
    ];

    public function lokasi()
    {
        return $this->belongsTo(Tlokasipatroli::class, 'LocationID');
    }
}