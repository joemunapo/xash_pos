<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceivedItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'goods_received_id',
        'product_id',
        'quantity_ordered',
        'quantity_received',
        'unit_price',
    ];

    protected $casts = [
        'quantity_ordered' => 'decimal:3',
        'quantity_received' => 'decimal:3',
        'unit_price' => 'decimal:2',
    ];

    public function goodsReceived(): BelongsTo
    {
        return $this->belongsTo(GoodsReceived::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
