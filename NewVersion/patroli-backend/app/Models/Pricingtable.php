<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pricingtable extends Model
{
    protected $table = 'pricingtable';
    public $timestamps = false;

    protected $fillable = [
        'PricingName',
        'NormalPrice',
        'DiscPrice',
        'DiscPercent',
        'PriceTerm',
        'Status',
    ];
}
