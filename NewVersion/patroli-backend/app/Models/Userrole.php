<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Userrole extends Model
{
    protected $table = 'userrole';
    public $timestamps = false;
    protected $primaryKey = 'userid';

    protected $fillable = [
        'userid',
        'roleid',
    ];
}
