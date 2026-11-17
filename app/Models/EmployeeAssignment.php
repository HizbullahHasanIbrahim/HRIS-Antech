<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'employee_assignments';

    protected $fillable = [
        'employee_id',
        'unit_id',
        'position_title',
        'assignment_type',
        'start_date',
        'end_date',
        'sk_number',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'unit_id');
    }

    public function scopeUnitScope(Builder $query, ?int $unitId): Builder
    {
        return $unitId ? $query->where('unit_id', $unitId) : $query;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}