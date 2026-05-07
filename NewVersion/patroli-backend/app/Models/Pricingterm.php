<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pricingterm extends Model
{
    protected $table = 'pricingterm';
    public $timestamps = false;

    protected $fillable = [
        'PriceID',
        'FiturDesc',
        'Allowed',
    ];
}
