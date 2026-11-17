<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveType extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'leave_types';

    protected $fillable = [
        'type',
        'name',
        'annual_quota',
        'applies_to',
        'is_active',
    ];

    protected $casts = [
        'annual_quota' => 'integer',
        'is_active' => 'boolean',
    ];

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class, 'leave_type_id');
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'leave_type_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
