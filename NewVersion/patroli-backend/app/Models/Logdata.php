<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logdata extends Model
{
    protected $table = 'logdata';
    public $timestamps = false;

    protected $fillable = [
        'LogDate',
        'Event',
        'IPAddress',
        'RecordOwnerID',
        'retValue',
    ];
}
