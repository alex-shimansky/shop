<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'product_id', 'quantity', 'price'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted(): void
    {
        static::saved(function ($item) {
            if ($item->order) {
                $item->order->recalculateTotalPrice();
            }
        });
    
        static::deleted(function ($item) {
            if ($item->order) {
                $item->order->recalculateTotalPrice();
            }
        });
    }
    
}