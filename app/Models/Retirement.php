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
        'pension_type',
        'proposed_date',
        'effective_date',
        'status',
        'sk_number',
        'sk_date',
        'reason',
        'processed_by',
        'version',
    ];

    protected $casts = [
        'proposed_date' => 'date',
        'effective_date' => 'date',
        'sk_date' => 'date',
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