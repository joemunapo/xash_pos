<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
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

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the current exchange rate for a currency pair
     */
    public static function getCurrentRate(int $tenantId, string $fromCurrency, string $toCurrency): ?float
    {
        if ($fromCurrency === $toCurrency) {
            return 1.0;
        }

        $rate = self::where('tenant_id', $tenantId)
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
    public static function convert(int $tenantId, float $amount, string $fromCurrency, string $toCurrency): ?float
    {
        $rate = self::getCurrentRate($tenantId, $fromCurrency, $toCurrency);

        if ($rate === null) {
            return null;
        }

        return round($amount * $rate, 2);
    }
}
