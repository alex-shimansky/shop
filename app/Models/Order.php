<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\OrderStatus;

/**
 * @OA\Schema(
 *     schema="Order",
 *     type="object",
 *     title="Order",
 *     required={"user_id", "status", "total_price", "shipping_address", "payment_method", "name", "phone"},
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=3),
 *     @OA\Property(property="status", type="string", example="pending"),
 *     @OA\Property(property="total_price", type="number", format="float", example=149.99),
 *     @OA\Property(property="shipping_address", type="string", example="123 Main St, City, Country"),
 *     @OA\Property(property="payment_method", type="string", example="paypal"),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="phone", type="string", example="+1234567890"),
 *     @OA\Property(property="payment_status", type="string", example="paid"),
 *     @OA\Property(property="response", type="string", example="{transaction_id:abc123}"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-02T14:00:00Z")
 * )
 */
class Order extends Model
{
    protected $fillable = ['user_id', 'status', 'total_price', 'shipping_address', 'payment_method', 'name', 'phone', 'response', 'payment_status'];

    protected $casts = [
        'status' => OrderStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function recalculateTotalPrice(): void
    {
        $this->loadMissing('orderItems'); // загружаем связанные записи, если ещё не загружены

        $this->total_price = $this->orderItems->sum(function ($item) {
            return $item->quantity * $item->price;
        });
        
        $this->save();
    }
}