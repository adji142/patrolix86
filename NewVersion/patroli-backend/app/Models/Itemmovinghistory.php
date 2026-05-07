<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Itemmovinghistory extends Model
{
    protected $table = 'itemmovinghistory';
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = [
        'KodeItem',
        'KodeGudang',
        'TglPencatatan',
        'BaseReff',
        'BaseType',
        'QtyIN',
        'QtyOut',
        'RecordOwnerID',
        'created_at',
        'updated_at',
    ];
}
