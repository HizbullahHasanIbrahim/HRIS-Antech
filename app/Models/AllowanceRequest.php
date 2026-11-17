<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AllowanceRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'allowance_requests';

    protected $fillable = [
        'employee_id',
        'allowance_type_id',
        'period_label',
        'amount',
        'status',
        'approver_id',
        'submitted_at',
        'decided_at',
        'decision_note',
        'version',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'submitted_at' => 'datetime',
        'decided_at' => 'datetime',
        'version' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function allowanceType(): BelongsTo
    {
        return $this->belongsTo(AllowanceType::class, 'allowance_type_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approver_id');
    }
}