<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchProductPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'product_id',
        'selling_price',
        'wholesale_price',
        'effective_from',
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'effective_from' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
