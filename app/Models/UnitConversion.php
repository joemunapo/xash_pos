<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitConversion extends Model
{
    protected $fillable = [
        'company_id',
        'from_unit_id',
        'to_unit_id',
        'conversion_factor',
    ];

    protected function casts(): array
    {
        return [
            'conversion_factor' => 'decimal:6',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function fromUnit(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'from_unit_id');
    }

    public function toUnit(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class, 'to_unit_id');
    }
}
