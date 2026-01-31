<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'branch_id',
        'user_id',
        'customer_id',
        'receipt_number',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'amount_paid',
        'change_amount',
        'payment_method',
        'payment_reference',
        'status',
        'notes',
        'completed_at',
        'currency',
        'exchange_rate',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'exchange_rate' => 'decimal:6',
            'completed_at' => 'datetime',
        ];
    }

    const STATUS_PENDING = 'pending';

    const STATUS_COMPLETED = 'completed';

    const STATUS_CANCELLED = 'cancelled';

    const STATUS_REFUNDED = 'refunded';

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }

    /**
     * Generate a unique receipt number
     */
    public static function generateReceiptNumber(int $branchId): string
    {
        $prefix = 'RCP';
        $date = now()->format('Ymd');
        $lastSale = self::where('branch_id', $branchId)
            ->whereDate('created_at', now()->toDateString())
            ->orderByDesc('id')
            ->first();

        $sequence = $lastSale ? (int) substr($lastSale->receipt_number, -4) + 1 : 1;

        return sprintf('%s-%s-%d-%04d', $prefix, $date, $branchId, $sequence);
    }

    /**
     * Calculate totals from items
     */
    public function calculateTotals(): void
    {
        $this->subtotal = $this->items->sum('line_total');
        $this->tax_amount = $this->items->sum('tax_amount');
        $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;
    }
}
