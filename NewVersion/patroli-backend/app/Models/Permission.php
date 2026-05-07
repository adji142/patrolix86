<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permission';
    public $timestamps = false;

    protected $fillable = [
        'permissionname',
        'link',
        'ico',
        'menusubmenu',
        'multilevel',
        'separator',
        'order',
        'status',
        'AllowMobile',
        'MobileRoute',
        'MobileLogo',
    ];
}
