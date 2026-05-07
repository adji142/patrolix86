<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permissionrole extends Model
{
    protected $table = 'permissionrole';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = [
        'roleid',
        'permissionid',
    ];
}
