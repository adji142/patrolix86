<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLokasi extends Model
{
    protected $table = 'user_lokasi';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'location_id',
        'RecordOwnerID',
    ];

    public function lokasi()
    {
        return $this->belongsTo(Tlokasipatroli::class, 'location_id');
    }
}