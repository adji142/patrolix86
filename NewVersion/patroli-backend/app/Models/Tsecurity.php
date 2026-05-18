<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tsecurity extends Model
{
    protected $table = 'tsecurity';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'NIK';
    protected $keyType = 'string';

    protected $fillable = [
        'NIK',
        'NamaSecurity',
        'JoinDate',
        'LocationID',
        'Status',
        'RecordOwnerID',
        'tempEncrypt',
        'Shift',
        'Image',
    ];

    public function lokasi()
    {
        return $this->belongsTo(Tlokasipatroli::class, 'LocationID');
    }

    public function getImageAttribute($value)
    {
        if (is_null($value) || $value === '') {
            return 'person.png';
        }
        return $value;
    }
}