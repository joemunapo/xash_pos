<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'product_id',
        'batch_number',
        'expiry_date',
        'manufacturing_date',
        'cost_price',
        'quantity_received',
        'quantity_remaining',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'manufacturing_date' => 'date',
        'cost_price' => 'decimal:2',
        'quantity_received' => 'decimal:3',
        'quantity_remaining' => 'decimal:3',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Check if batch is expired
    public function getIsExpiredAttribute()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    // Check if batch is expiring soon (within 30 days)
    public function getIsExpiringSoonAttribute()
    {
        return $this->expiry_date && $this->expiry_date->isBetween(now(), now()->addDays(30));
    }

    // Days until expiry
    public function getDaysUntilExpiryAttribute()
    {
        if (! $this->expiry_date) {
            return null;
        }

        return now()->diffInDays($this->expiry_date, false);
    }
}
