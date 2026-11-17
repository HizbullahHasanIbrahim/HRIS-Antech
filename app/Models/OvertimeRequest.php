<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OvertimeRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'overtime_requests';

    protected $fillable = [
        'employee_id',
        'overtime_date',
        'start_time',
        'end_time',
        'duration_hours',
        'reason',
        'status',
        'approver_id',
        'submitted_at',
        'decided_at',
        'decision_note',
        'version',
    ];

    protected $casts = [
        'overtime_date' => 'date',
        'duration_hours' => 'decimal:2',
        'submitted_at' => 'datetime',
        'decided_at' => 'datetime',
        'version' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approver_id');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'diajukan');
    }
}