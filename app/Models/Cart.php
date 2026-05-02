<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id'
    ];

    protected $casts = [
        'user_id' => 'integer',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity');
    }


    public function getSubtotalAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });
    }


    public function getFormattedSubtotalAttribute(): string
    {
        return '$' . number_format($this->subtotal, 2);
    }


    public function clear(): void
    {
        $this->items()->delete();
    }


    public function getSummary(): array
    {
        $items = $this->items->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'price' => $item->product->price,
                'quantity' => $item->quantity,
                'total' => $item->product->price * $item->quantity,
                'image' => $item->product->image,
                'sku' => $item->product->sku,
                'stock' => $item->product->stock_quantity
            ];
        });

        $subtotal = $items->sum('total');
        $tax = $subtotal * 0.10; // 10% tax
        $shipping = $subtotal > 100 ? 0 : 10;
        $total = $subtotal + $tax + $shipping;

        return [
            'items' => $items,
            'total_items' => $items->count(),
            'total_quantity' => $items->sum('quantity'),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping' => $shipping,
            'total' => $total,
            'cart_id' => $this->id
        ];
    }
}