<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'barcode',
        'price_modifier',
        'is_active',
    ];

    protected $casts = [
        'price_modifier' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Calculate final price including modifier
    public function getFinalPriceAttribute()
    {
        return $this->product->selling_price + $this->price_modifier;
    }
}
