<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'subscription_plan_id',
        'plan_name',
        'plan_slug',
        'price',
        'billing_cycle',
        'max_users',
        'max_branches',
        'max_products',
        'features',
        'starts_at',
        'ends_at',
        'renews_at',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'renews_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if subscription is expiring soon (within 7 days)
     */
    public function isExpiringSoon(): bool
    {
        if (!$this->ends_at) {
            return false;
        }

        return $this->ends_at->diffInDays(now()) <= 7 && $this->ends_at->isFuture();
    }

    /**
     * Cancel subscription
     */
    public function cancel(): void
    {
        $this->update([
            'status' => 'cancelled',
            'renews_at' => null,
        ]);
    }

    /**
     * Renew subscription
     */
    public function renew(): void
    {
        $duration = $this->billing_cycle === 'yearly' ? 365 : 30;

        $this->update([
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addDays($duration),
            'renews_at' => now()->addDays($duration),
        ]);
    }

    /**
     * Upgrade subscription to a new plan
     */
    public function upgradeToPlan(SubscriptionPlan $plan, string $billingCycle = 'monthly'): void
    {
        $duration = $billingCycle === 'yearly' ? 365 : 30;

        $this->update([
            'subscription_plan_id' => $plan->id,
            'plan_name' => $plan->name,
            'plan_slug' => $plan->slug,
            'price' => $plan->getPrice($billingCycle),
            'billing_cycle' => $billingCycle,
            'max_users' => $plan->max_users,
            'max_branches' => $plan->max_branches,
            'max_products' => $plan->max_products,
            'features' => $plan->features,
            'status' => 'active',
            'ends_at' => now()->addDays($duration),
            'renews_at' => now()->addDays($duration),
        ]);
    }

    /**
     * Scope to get active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            });
    }

    /**
     * Scope to get expiring subscriptions
     */
    public function scopeExpiringSoon($query, int $days = 7)
    {
        return $query->where('status', 'active')
            ->whereNotNull('ends_at')
            ->whereBetween('ends_at', [now(), now()->addDays($days)]);
    }
}
