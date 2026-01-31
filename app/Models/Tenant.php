<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'trading_name',
        'logo',
        'registration_number',
        'vat_number',
        'address',
        'phone',
        'email',
        'website',
        'default_currency',
        'fiscal_year_start',
        'settings',
        'is_active',
        'subscription_status',
        'trial_ends_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'trial_ends_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tenant) {
            if (empty($tenant->slug)) {
                $tenant->slug = Str::slug($tenant->name);
            }

            // Set trial end date to 14 days from now
            if (empty($tenant->trial_ends_at)) {
                $tenant->trial_ends_at = now()->addDays(14);
            }
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->latest();
    }

    /**
     * Check if tenant is on trial
     */
    public function isOnTrial(): bool
    {
        return $this->subscription_status === 'trial' 
            && $this->trial_ends_at 
            && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if tenant subscription is active
     */
    public function hasActiveSubscription(): bool
    {
        if ($this->isOnTrial()) {
            return true;
        }

        return $this->activeSubscription()->exists();
    }

    /**
     * Check if tenant can access the system
     */
    public function canAccess(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->subscription_status === 'suspended' || $this->subscription_status === 'cancelled') {
            return false;
        }

        return $this->hasActiveSubscription();
    }

    /**
     * Suspend tenant
     */
    public function suspend(): void
    {
        $this->update([
            'subscription_status' => 'suspended',
            'is_active' => false,
        ]);
    }

    /**
     * Activate tenant
     */
    public function activate(): void
    {
        $this->update([
            'subscription_status' => 'active',
            'is_active' => true,
        ]);
    }

    /**
     * Get current subscription limits
     */
    public function getSubscriptionLimits(): array
    {
        $subscription = $this->activeSubscription;

        if ($subscription) {
            return [
                'max_users' => $subscription->max_users,
                'max_branches' => $subscription->max_branches,
                'max_products' => $subscription->max_products,
            ];
        }

        // Default trial limits
        return [
            'max_users' => 3,
            'max_branches' => 1,
            'max_products' => 50,
        ];
    }
}
