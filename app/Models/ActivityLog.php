<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'branch_id',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    // Common actions
    const ACTION_CREATED = 'created';

    const ACTION_UPDATED = 'updated';

    const ACTION_DELETED = 'deleted';

    const ACTION_LOGIN = 'login';

    const ACTION_LOGOUT = 'logout';

    const ACTION_FAILED_LOGIN = 'failed_login';

    // tenant() relationship is provided by BelongsToTenant trait

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    // Get the actual model that was modified
    public function subject()
    {
        if ($this->model_type && $this->model_id) {
            return $this->model_type::find($this->model_id);
        }

        return null;
    }

    // Static method to log an activity
    public static function log(
        string $action,
        ?int $tenantId = null,
        ?int $userId = null,
        ?int $branchId = null,
        ?string $modelType = null,
        ?int $modelId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): self {
        return self::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId ?? auth()->id(),
            'branch_id' => $branchId,
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
