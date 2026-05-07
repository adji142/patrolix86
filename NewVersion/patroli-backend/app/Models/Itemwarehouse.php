<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Itemwarehouse extends Model
{
    protected $table = 'itemwarehouse';
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = [
        'KodeItem',
        'KodeGudang',
        'Qty',
        'RecordOwnerID',
        'created_at',
        'updated_at',
    ];
}
