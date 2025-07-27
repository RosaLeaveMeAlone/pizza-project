<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Order extends Model
{
    protected $fillable = [
        'cart_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'total',
        'payment_method',
        'status',
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_product')
            ->withPivot('quantity', 'pizza_size_id', 'unit_price')
            ->withTimestamps();
    }
}
