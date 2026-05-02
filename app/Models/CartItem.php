<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'attributes'
    ];

    protected $casts = [
        'attributes' => 'array',
        'quantity' => 'integer'
    ];

    /**
     * Get the cart that owns the item
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the product for this cart item
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get subtotal for this item
     */
    public function getSubtotalAttribute(): float
    {
        return $this->product->price * $this->quantity;
    }

    /**
     * Get formatted subtotal
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return '$' . number_format($this->subtotal, 2);
    }

    /**
     * Increment quantity
     */
    public function incrementQuantity(int $amount = 1): void
    {
        $this->increment('quantity', $amount);
    }

    /**
     * Decrement quantity
     */
    public function decrementQuantity(int $amount = 1): void
    {
        if ($this->quantity - $amount <= 0) {
            $this->delete();
        } else {
            $this->decrement('quantity', $amount);
        }
    }
}