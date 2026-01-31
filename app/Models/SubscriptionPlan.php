<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_monthly',
        'price_yearly',
        'max_users',
        'max_branches',
        'max_products',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the price for a given billing cycle
     */
    public function getPrice(string $billingCycle = 'monthly'): float
    {
        return $billingCycle === 'yearly' ? $this->price_yearly : $this->price_monthly;
    }

    /**
     * Check if a feature is included
     */
    public function hasFeature(string $feature): bool
    {
        if (!$this->features) {
            return false;
        }

        return in_array($feature, $this->features);
    }

    /**
     * Scope to get active plans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
