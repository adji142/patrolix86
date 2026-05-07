<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pascode extends Model
{
    protected $table = 'pascode';
    public $timestamps = false;

    protected $fillable = [
        'Passcode',
        'ValidTo',
        'Status',
    ];
}
