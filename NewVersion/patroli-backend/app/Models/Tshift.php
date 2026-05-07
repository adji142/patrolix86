<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tshift extends Model
{
    protected $table = 'tshift';
    public $timestamps = false;

    protected $fillable = [
        'NamaShift',
        'MulaiBekerja',
        'SelesaiBekerja',
        'IntervalPatroli',
        'IntervalType',
        'Toleransi',
        'RecordOwnerID',
        'LocationID',
        'GantiHari',
        'MulaiAbsen',
        'MaxAbsen',
    ];
}
