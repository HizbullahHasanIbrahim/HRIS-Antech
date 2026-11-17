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
        'description',
        'unit',
        'target_value',
        'realization_value',
        'weight',
        'score',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'realization_value' => 'decimal:2',
        'weight' => 'decimal:2',
        'score' => 'decimal:2',
    ];

    public function target(): BelongsTo
    {
        return $this->belongsTo(PerformanceTarget::class, 'target_id');
    }
}
