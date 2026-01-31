<?php

namespace App\Traits;

use App\Models\Tenant;
use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    /**
     * Boot the trait
     */
    protected static function bootBelongsToTenant()
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (! $model->tenant_id) {
                $model->tenant_id = auth()->user()?->tenant_id ?? session('tenant_id');
            }
        });
    }

    /**
     * Get the tenant that owns the model
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope to a specific tenant
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}
