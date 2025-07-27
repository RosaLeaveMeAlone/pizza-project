<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'base_price',
        'available',
        'image',
        'category_id',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'available' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function pizza(): HasOne
    {
        return $this->hasOne(Pizza::class);
    }

    public function carts(): BelongsToMany
    {
        return $this->belongsToMany(Cart::class, 'cart_product')
            ->withPivot('quantity', 'pizza_size_id')
            ->withTimestamps();
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_product')
            ->withPivot('quantity', 'pizza_size_id', 'unit_price')
            ->withTimestamps();
    }
}
