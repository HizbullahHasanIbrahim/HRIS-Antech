<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveBalance extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'leave_balances';

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'year',
        'quota_days',
        'used_days',
        'version',
    ];

    protected $casts = [
        'year' => 'integer',
        'quota_days' => 'integer',
        'used_days' => 'integer',
        'version' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    /**
     * Get remaining days (computed from quota_days - used_days).
     */
    public function getRemainingDaysAttribute(): int
    {
        return $this->quota_days - $this->used_days;
    }

    /**
     * Alias for backward compatibility.
     */
    public function getRemainingAmountAttribute(): int
    {
        return $this->remaining_days;
    }

    /**
     * Alias for backward compatibility.
     */
    public function getQuotaAmountAttribute(): int
    {
        return $this->quota_days;
    }

    /**
     * Alias for backward compatibility.
     */
    public function getUsedAmountAttribute(): int
    {
        return $this->used_days;
    }
}
