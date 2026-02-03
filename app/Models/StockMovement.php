<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'to_branch_id',
        'product_id',
        'variant_id',
        'batch_id',
        'user_id',
        'type',
        'quantity',
        'balance_after',
        'reference',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'balance_after' => 'decimal:3',
    ];

    // Movement types
    const TYPE_PURCHASE = 'purchase';

    const TYPE_SALE = 'sale';

    const TYPE_ADJUSTMENT = 'adjustment';

    const TYPE_TRANSFER_IN = 'transfer_in';

    const TYPE_TRANSFER_OUT = 'transfer_out';

    const TYPE_DAMAGE = 'damage';

    const TYPE_RETURN = 'return';

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
