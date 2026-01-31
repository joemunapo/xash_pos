<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsReceived extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'branch_id',
        'purchase_order_id',
        'supplier_id',
        'user_id',
        'grn_number',
        'received_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'received_date' => 'date',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_COMPLETED = 'completed';

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoodsReceivedItem::class);
    }

    public static function generateGRNNumber($companyId)
    {
        $year = date('Y');
        $month = date('m');
        $prefix = "GRN-{$year}{$month}-";
        
        $lastGRN = self::where('company_id', $companyId)
            ->where('grn_number', 'like', "{$prefix}%")
            ->orderBy('grn_number', 'desc')
            ->first();

        if ($lastGRN) {
            $lastNumber = (int) substr($lastGRN->grn_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
