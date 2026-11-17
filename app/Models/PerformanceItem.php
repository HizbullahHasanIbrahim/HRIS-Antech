<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerformanceItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'performance_items';

    protected $fillable = [
        'target_id',
        'activity_name',
        'target_quantity',
        'actual_quantity',
        'target_quality',
        'actual_quality',
        'score',
        'weight',
    ];

    protected $casts = [
        'target_quantity' => 'integer',
        'actual_quantity' => 'integer',
        'target_quality' => 'decimal:2',
        'actual_quality' => 'decimal:2',
        'score' => 'decimal:2',
        'weight' => 'decimal:2',
    ];

    public function target(): BelongsTo
    {
        return $this->belongsTo(PerformanceTarget::class, 'target_id');
    }
}