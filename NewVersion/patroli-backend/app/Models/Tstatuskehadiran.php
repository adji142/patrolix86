<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tstatuskehadiran extends Model
{
    protected $table = 'tstatuskehadiran';
    public $timestamps = false;

    protected $fillable = [
        'Nama',
    ];
}
