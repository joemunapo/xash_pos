<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone_number',
        'password',
        'pin',
        'avatar',
        'role',
        'is_super_admin',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'pin',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_super_admin' => 'boolean',
        ];
    }

    // Role constants
    const ROLE_SUPER_ADMIN = 'super_admin';

    const ROLE_ADMIN = 'admin';

    const ROLE_MANAGER = 'manager';

    const ROLE_CASHIER = 'cashier';

    const ROLE_STOCKIST = 'stockist';

    const ROLE_USER = 'user';

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_user')
            ->withPivot(['role', 'is_primary'])
            ->withTimestamps();
    }

    public function primaryBranch(): ?Branch
    {
        return $this->branches()->wherePivot('is_primary', true)->first();
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    // Check if user is super admin
    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin === true;
    }

    // Check if user is admin
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    // Check if user is manager
    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    // Check if user is cashier
    public function isCashier(): bool
    {
        return $this->role === self::ROLE_CASHIER;
    }

    // Check if user has access to a specific branch
    public function hasAccessToBranch(int $branchId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->branches()->where('branches.id', $branchId)->exists();
    }

    // Get role for a specific branch
    public function getRoleForBranch(int $branchId): ?string
    {
        $branch = $this->branches()->where('branches.id', $branchId)->first();

        return $branch ? $branch->pivot->role : null;
    }
}
