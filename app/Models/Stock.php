<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    use HasFactory;

    protected $table = 'stock';

    protected $fillable = [
        'branch_id',
        'product_id',
        'variant_id',
        'quantity',
        'reserved_quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'reserved_quantity' => 'decimal:3',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    // Available quantity (total - reserved)
    public function getAvailableQuantityAttribute()
    {
        return $this->quantity - $this->reserved_quantity;
    }

    // Check if stock is low
    public function getIsLowStockAttribute()
    {
        return $this->quantity <= $this->product->reorder_level;
    }

    // Check if out of stock
    public function getIsOutOfStockAttribute()
    {
        return $this->quantity <= 0;
    }
}
