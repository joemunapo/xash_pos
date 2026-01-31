<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'branch_id',
        'type',
        'points',
        'balance_after',
        'reference',
        'description',
    ];

    protected $casts = [
        'points' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    // Transaction types
    const TYPE_EARN = 'earn';

    const TYPE_REDEEM = 'redeem';

    const TYPE_ADJUST = 'adjust';

    const TYPE_EXPIRE = 'expire';

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
