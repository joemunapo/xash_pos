<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'name',
        'sku',
        'barcode',
        'plu_code',
        'description',
        'unit',
        'cost_price',
        'selling_price',
        'wholesale_price',
        'tax_rate',
        'is_taxable',
        'image',
        'track_stock',
        'track_expiry',
        'track_batches',
        'allow_decimal_qty',
        'reorder_level',
        'reorder_quantity',
        'is_active',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'is_taxable' => 'boolean',
        'track_stock' => 'boolean',
        'track_expiry' => 'boolean',
        'track_batches' => 'boolean',
        'allow_decimal_qty' => 'boolean',
        'is_active' => 'boolean',
    ];

    // tenant() relationship is provided by BelongsToTenant trait

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function stock(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'product_supplier')
            ->withPivot(['supplier_sku', 'cost_price', 'is_primary'])
            ->withTimestamps();
    }

    public function branchPrices(): HasMany
    {
        return $this->hasMany(BranchProductPrice::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function productUnits(): HasMany
    {
        return $this->hasMany(ProductUnit::class)->orderBy('sort_order')->orderBy('quantity');
    }

    // Get stock for a specific branch
    public function getStockForBranch($branchId)
    {
        return $this->stock()->where('branch_id', $branchId)->first();
    }

    // Get price for a specific branch (or default)
    public function getPriceForBranch($branchId)
    {
        $branchPrice = $this->branchPrices()->where('branch_id', $branchId)->first();

        return $branchPrice ? $branchPrice->selling_price : $this->selling_price;
    }
}
