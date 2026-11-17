<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Retirement extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'retirements';

    protected $fillable = [
        'employee_id',
        'retirement_age',
        'planned_date',
        'proposed_at',
        'status',
        'processed_by',
        'sk_number',
        'sk_date',
        'completed_at',
        'notes',
        'version',
    ];

    protected $casts = [
        'retirement_age' => 'integer',
        'planned_date' => 'date',
        'proposed_at' => 'datetime',
        'sk_date' => 'date',
        'completed_at' => 'datetime',
        'version' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function training(): HasOne
    {
        return $this->hasOne(Training::class, 'retirement_id');
    }
}
