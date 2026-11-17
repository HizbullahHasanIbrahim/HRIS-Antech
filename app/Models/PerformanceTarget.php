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
        'status',
        'appraiser_id',
        'submitted_at',
        'approved_at',
        'final_score',
        'evaluation_summary',
        'version',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'final_score' => 'decimal:2',
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