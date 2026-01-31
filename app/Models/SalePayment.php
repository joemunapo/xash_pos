<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'payment_method',
        'amount',
        'reference',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    const METHOD_CASH = 'cash';
    const METHOD_ECOCASH = 'ecocash';
    const METHOD_SWIPE = 'swipe';
    const METHOD_MOBILE_MONEY = 'mobile_money';

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
