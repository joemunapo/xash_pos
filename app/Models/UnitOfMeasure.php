<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitOfMeasure extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'abbreviation',
        'category',
        'is_base_unit',
        'allow_decimal',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_base_unit' => 'boolean',
            'allow_decimal' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function conversionsFrom(): HasMany
    {
        return $this->hasMany(UnitConversion::class, 'from_unit_id');
    }

    public function conversionsTo(): HasMany
    {
        return $this->hasMany(UnitConversion::class, 'to_unit_id');
    }

    /**
     * Get all possible conversions for this unit.
     */
    public function getConversions(): array
    {
        $conversions = [];

        foreach ($this->conversionsFrom as $conversion) {
            $conversions[] = [
                'to_unit' => $conversion->toUnit,
                'factor' => $conversion->conversion_factor,
            ];
        }

        return $conversions;
    }

    /**
     * Convert a value from this unit to another unit.
     */
    public function convertTo(float $value, UnitOfMeasure $toUnit): ?float
    {
        $conversion = $this->conversionsFrom()
            ->where('to_unit_id', $toUnit->id)
            ->first();

        if ($conversion) {
            return $value * $conversion->conversion_factor;
        }

        // Try reverse conversion
        $reverseConversion = $this->conversionsTo()
            ->where('from_unit_id', $toUnit->id)
            ->first();

        if ($reverseConversion) {
            return $value / $reverseConversion->conversion_factor;
        }

        return null;
    }
}
