<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TukarJadwalFoto extends Model
{
    protected $table = 'tukar_jadwal_foto';

    protected $fillable = [
        'tukar_jadwal_id',
        'FileName',
    ];

    public function tukarJadwal()
    {
        return $this->belongsTo(TukarJadwal::class, 'tukar_jadwal_id');
    }
}
