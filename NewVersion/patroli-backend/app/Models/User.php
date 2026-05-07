<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'users';
    public $timestamps = false;

    protected $fillable = [
        'username',
        'nama',
        'password',
        'createdby',
        'createdon',
        'HakAkses',
        'token',
        'verified',
        'ip',
        'browser',
        'email',
        'phone',
        'RecordOwnerID',
        'AreaUser',
        'UserToken',
        'TempPassword',
        'LaravelPassword',
    ];

    protected $hidden = [
        'password',
        'TempPassword',
        'LaravelPassword',
        'token',
        'UserToken',
    ];

    public function userLokasi()
    {
        return $this->hasMany(UserLokasi::class, 'user_id');
    }
}
