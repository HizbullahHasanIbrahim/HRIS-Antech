<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerformanceTarget extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'performance_targets';

    protected $fillable = [
        'employee_id',
        'period_label',
        'period_start',
        'period_end',
        'appraiser_id',
        'status',
        'final_score',
        'rating',
        'approved_at',
        'version',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'final_score' => 'decimal:2',
        'approved_at' => 'datetime',
        'version' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function appraiser(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'appraiser_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PerformanceItem::class, 'target_id');
    }
}
