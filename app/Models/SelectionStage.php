<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SelectionStage extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'selection_stages';

    protected $fillable = [
        'application_id',
        'stage_name',
        'score',
        'status',
        'scheduled_date',
        'assessed_by',
        'assessed_at',
        'notes',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'scheduled_date' => 'date',
        'assessed_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }
}