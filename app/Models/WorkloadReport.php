<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkloadReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'workload_reports';

    protected $fillable = [
        'employee_id',
        'academic_year',
        'semester',
        'teaching_credit',
        'research_credit',
        'service_credit',
        'support_credit',
        'status',
        'validator_id',
        'validated_at',
        'version',
        'is_current',
        'revision_reason',
    ];

    protected $casts = [
        'teaching_credit' => 'decimal:2',
        'research_credit' => 'decimal:2',
        'service_credit' => 'decimal:2',
        'support_credit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'validated_at' => 'datetime',
        'version' => 'integer',
        'is_current' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'validator_id');
    }
}
