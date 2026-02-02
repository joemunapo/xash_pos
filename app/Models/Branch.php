<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'address',
        'city',
        'phone',
        'email',
        'receipt_header',
        'receipt_footer',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    // tenant() relationship is provided by BelongsToTenant trait

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'branch_user')
            ->withPivot(['role', 'is_primary'])
            ->withTimestamps();
    }

    public function managers(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'manager');
    }

    public function cashiers(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'cashier');
    }

    public function stockists(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'stockist');
    }

    public function stock(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function productPrices(): HasMany
    {
        return $this->hasMany(BranchProductPrice::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(BranchSchedule::class)->orderBy('day_of_week');
    }

    /**
     * Get schedule for a specific day
     */
    public function getScheduleForDay(int $dayOfWeek): ?BranchSchedule
    {
        return $this->schedules()->where('day_of_week', $dayOfWeek)->first();
    }

    /**
     * Check if branch is open on a specific day
     */
    public function isOpenOnDay(int $dayOfWeek): bool
    {
        $schedule = $this->getScheduleForDay($dayOfWeek);

        return $schedule && ! $schedule->is_closed;
    }
}
