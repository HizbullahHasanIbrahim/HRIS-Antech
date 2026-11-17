<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkScheduleDay extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'work_schedule_days';

    protected $fillable = [
        'schedule_id',
        'day_of_week',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'is_work_day',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_work_day' => 'boolean',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(WorkSchedule::class, 'schedule_id');
    }
}