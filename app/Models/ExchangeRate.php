<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'from_currency',
        'to_currency',
        'rate',
        'effective_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:6',
            'effective_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the current exchange rate for a currency pair
     */
    public static function getCurrentRate(int $companyId, string $fromCurrency, string $toCurrency): ?float
    {
        if ($fromCurrency === $toCurrency) {
            return 1.0;
        }

        $rate = self::where('company_id', $companyId)
            ->where('from_currency', $fromCurrency)
            ->where('to_currency', $toCurrency)
            ->where('is_active', true)
            ->where('effective_date', '<=', now())
            ->orderByDesc('effective_date')
            ->first();

        return $rate ? (float) $rate->rate : null;
    }

    /**
     * Convert amount from one currency to another
     */
    public static function convert(int $companyId, float $amount, string $fromCurrency, string $toCurrency): ?float
    {
        $rate = self::getCurrentRate($companyId, $fromCurrency, $toCurrency);
        
        if ($rate === null) {
            return null;
        }

        return round($amount * $rate, 2);
    }
}
