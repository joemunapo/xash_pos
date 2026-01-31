<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Supplier extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'city',
        'payment_terms',
        'credit_days',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // tenant() relationship is provided by BelongsToTenant trait

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_supplier')
            ->withPivot(['supplier_sku', 'cost_price', 'is_primary'])
            ->withTimestamps();
    }
}
