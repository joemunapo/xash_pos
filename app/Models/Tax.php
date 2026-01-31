<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Tax extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'rate',
        'start_date',
        'end_date',
        'is_default',
        'is_active',
        'description',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Check if the tax is currently applicable (within date range and active)
     */
    public function isCurrentlyApplicable(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $today = Carbon::today();

        // Check start date
        if ($this->start_date && $today->lt($this->start_date)) {
            return false;
        }

        // Check end date
        if ($this->end_date && $today->gt($this->end_date)) {
            return false;
        }

        return true;
    }

    /**
     * Get all currently applicable taxes for a company
     */
    public static function getApplicableTaxes(int $companyId)
    {
        $today = Carbon::today();

        return static::where('company_id', $companyId)
            ->where('is_active', true)
            ->where(function ($query) use ($today) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $today);
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $today);
            })
            ->get();
    }

    /**
     * Get the default tax for a company
     */
    public static function getDefaultTax(int $companyId)
    {
        return static::where('company_id', $companyId)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }
}
