<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductUnit extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'abbreviation',
        'quantity',
        'selling_price',
        'cost_price',
        'barcode',
        'is_default',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'selling_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'is_default' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the effective selling price for this unit.
     * If no custom price is set, calculate from base product price.
     */
    public function getEffectiveSellingPrice(): float
    {
        if ($this->selling_price !== null) {
            return (float) $this->selling_price;
        }

        return (float) ($this->product->selling_price * $this->quantity);
    }

    /**
     * Get the effective cost price for this unit.
     * If no custom cost is set, calculate from base product cost.
     */
    public function getEffectiveCostPrice(): float
    {
        if ($this->cost_price !== null) {
            return (float) $this->cost_price;
        }

        return (float) ($this->product->cost_price * $this->quantity);
    }
}
