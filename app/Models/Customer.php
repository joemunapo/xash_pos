<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'first_name',
        'last_name',
        'phone',
        'email',
        'address',
        'city',
        'date_of_birth',
        'gender',
        'loyalty_tier',
        'loyalty_points',
        'qr_code',
        'metadata',
        'is_active',
        'member_since',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'loyalty_points' => 'decimal:2',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'member_since' => 'datetime',
    ];

    protected $appends = ['full_name'];

    // tenant() relationship is provided by BelongsToTenant trait

    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    // Add loyalty points
    public function earnPoints(float $points, ?int $branchId = null, ?string $reference = null, ?string $description = null)
    {
        $this->loyalty_points += $points;
        $this->save();

        $this->loyaltyTransactions()->create([
            'branch_id' => $branchId,
            'type' => 'earn',
            'points' => $points,
            'balance_after' => $this->loyalty_points,
            'reference' => $reference,
            'description' => $description,
        ]);

        $this->updateLoyaltyTier();
    }

    // Redeem loyalty points
    public function redeemPoints(float $points, ?int $branchId = null, ?string $reference = null, ?string $description = null)
    {
        if ($this->loyalty_points < $points) {
            throw new \Exception('Insufficient loyalty points');
        }

        $this->loyalty_points -= $points;
        $this->save();

        $this->loyaltyTransactions()->create([
            'branch_id' => $branchId,
            'type' => 'redeem',
            'points' => -$points,
            'balance_after' => $this->loyalty_points,
            'reference' => $reference,
            'description' => $description,
        ]);
    }

    // Update loyalty tier based on points
    protected function updateLoyaltyTier()
    {
        if ($this->loyalty_points >= 10000) {
            $this->loyalty_tier = 'platinum';
        } elseif ($this->loyalty_points >= 5000) {
            $this->loyalty_tier = 'gold';
        } elseif ($this->loyalty_points >= 1000) {
            $this->loyalty_tier = 'silver';
        } else {
            $this->loyalty_tier = 'bronze';
        }
        $this->save();
    }
}
