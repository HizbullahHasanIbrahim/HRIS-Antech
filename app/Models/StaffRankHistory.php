<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffRankHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'staff_rank_histories';

    protected $fillable = [
        'employee_id',
        'rank_level',
        'status',
        'submitted_at',
        'approver_id',
        'decided_at',
        'effective_date',
        'sk_number',
        'sk_date',
        'decision_note',
        'version',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'decided_at' => 'datetime',
        'effective_date' => 'date',
        'sk_date' => 'date',
        'version' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approver_id');
    }
}
