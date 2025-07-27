<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PizzaSize extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price_multiplier',
    ];

    protected $casts = [
        'price_multiplier' => 'decimal:2',
    ];
}
