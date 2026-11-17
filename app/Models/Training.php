<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Training extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trainings';

    protected $fillable = [
        'employee_id',
        'retirement_id',
        'title',
        'training_type',
        'organizer',
        'start_date',
        'end_date',
        'duration_hours',
        'status',
        'certificate_number',
        'certificate_path',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'duration_hours' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function retirement(): BelongsTo
    {
        return $this->belongsTo(Retirement::class, 'retirement_id');
    }
}
