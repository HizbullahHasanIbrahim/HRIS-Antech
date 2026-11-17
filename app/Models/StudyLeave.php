<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudyLeave extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'study_leaves';

    protected $fillable = [
        'employee_id',
        'degree_level',
        'institution_name',
        'major',
        'start_date',
        'expected_end_date',
        'actual_end_date',
        'status',
        'sk_number',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expected_end_date' => 'date',
        'actual_end_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}