<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'day_of_week',
        'opening_time',
        'closing_time',
        'is_closed',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_closed' => 'boolean',
    ];

    /**
     * Day of week constants
     */
    public const SUNDAY = 0;
    public const MONDAY = 1;
    public const TUESDAY = 2;
    public const WEDNESDAY = 3;
    public const THURSDAY = 4;
    public const FRIDAY = 5;
    public const SATURDAY = 6;

    /**
     * Get all day names
     */
    public static function getDayNames(): array
    {
        return [
            self::SUNDAY => 'Sunday',
            self::MONDAY => 'Monday',
            self::TUESDAY => 'Tuesday',
            self::WEDNESDAY => 'Wednesday',
            self::THURSDAY => 'Thursday',
            self::FRIDAY => 'Friday',
            self::SATURDAY => 'Saturday',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the day name for this schedule
     */
    public function getDayNameAttribute(): string
    {
        return self::getDayNames()[$this->day_of_week] ?? 'Unknown';
    }

    /**
     * Get formatted opening hours
     */
    public function getFormattedHoursAttribute(): string
    {
        if ($this->is_closed) {
            return 'Closed';
        }

        if (!$this->opening_time || !$this->closing_time) {
            return 'Not set';
        }

        return date('g:i A', strtotime($this->opening_time)) . ' - ' . date('g:i A', strtotime($this->closing_time));
    }

    /**
     * Create default schedule for a branch (all days open 8am-6pm)
     */
    public static function createDefaultSchedule(int $branchId): void
    {
        foreach (range(0, 6) as $day) {
            static::create([
                'branch_id' => $branchId,
                'day_of_week' => $day,
                'opening_time' => '08:00',
                'closing_time' => '18:00',
                'is_closed' => false,
            ]);
        }
    }
}
