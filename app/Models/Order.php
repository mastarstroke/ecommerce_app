<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_id',
        'order_number',
        'subtotal',
        'tax',
        'shipping_cost',
        'total',
        'status',
        'payment_status',
        'payment_method',
        'shipping_address',
        'billing_address',
        'notes'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public static function generateOrderNumber(): string
    {
        return 'ORD-' . strtoupper(uniqid());
    }

    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'declined' => 'danger',
            'cancelled' => 'secondary'
        ];

        return $badges[$this->status] ?? 'light';
    }
}