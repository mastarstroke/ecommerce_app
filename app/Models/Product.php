<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'short_description',
        'price', 'compare_price', 'stock_quantity', 'sku', 'images',
        'attributes', 'is_featured', 'is_active'
    ];

    protected $casts = [
        'images' => 'array',
        'attributes' => 'array',
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'is_featured' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getImageAttribute(): ?string
    {
        return $this->images[0] ?? null;
    }

    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }

    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }
}