<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'work_schedules';

    protected $fillable = [
        'name',
        'schedule_type',
        'effective_start',
        'effective_end',
        'is_active',
    ];

    protected $casts = [
        'effective_start' => 'date',
        'effective_end' => 'date',
        'is_active' => 'boolean',
    ];

    public function days(): HasMany
    {
        return $this->hasMany(WorkScheduleDay::class, 'schedule_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
