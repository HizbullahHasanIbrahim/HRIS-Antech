<?php

declare(strict_types=1);

namespace App\Services\Attendance;

use App\Models\WorkSchedule;
use App\Models\WorkScheduleDay;
use App\Services\Audit\ActivityLogService;
use Illuminate\Support\Facades\DB;

class WorkScheduleService
{
    public function __construct(
        protected ActivityLogService $activityLog
    ) {}

    public function createSchedule(
        string $category,
        \DateTimeInterface $validFrom,
        \DateTimeInterface $validUntil,
        array $days,
        bool $isPublished,
        \DateTimeInterface $publishedAt,
        int $createdBy
    ): WorkSchedule {
        return DB::transaction(function () use ($category, $validFrom, $validUntil, $days, $isPublished, $publishedAt, $createdBy) {
            $schedule = WorkSchedule::create([
                'category' => $category,
                'valid_from' => $validFrom,
                'valid_until' => $validUntil,
                'is_published' => $isPublished,
                'published_at' => $publishedAt,
                'created_by' => $createdBy,
            ]);

            foreach ($days as $day) {
                WorkScheduleDay::create([
                    'schedule_id' => $schedule->id,
                    'day_of_week' => $day['day_of_week'],
                    'start_time' => $day['start_time'],
                    'end_time' => $day['end_time'],
                    'break_start' => $day['break_start'] ?? null,
                    'break_end' => $day['break_end'] ?? null,
                ]);
            }

            $this->activityLog->log(
                $createdBy,
                'CREATE_WORK_SCHEDULE',
                'WorkSchedule',
                $schedule->id,
                null,
                $isPublished ? 'dipublikasi' : 'tidak dipublikasi',
            );

            return $schedule->load('days');
        });
    }

    public function updateSchedule(WorkSchedule $schedule, array $data): WorkSchedule
    {
        return DB::transaction(function () use ($schedule, $data) {
            $schedule->update($data);

            $this->activityLog->log(
                null,
                'UPDATE_WORK_SCHEDULE',
                'WorkSchedule',
                $schedule->id,
                null,
                $schedule->is_published ? 'dipublikasi' : 'tidak dipublikasi',
            );

            return $schedule->fresh();
        });
    }

    public function deleteSchedule(WorkSchedule $schedule): void
    {
        DB::transaction(function () use ($schedule) {
            $schedule->delete();

            $this->activityLog->log(
                null,
                'DELETE_WORK_SCHEDULE',
                'WorkSchedule',
                $schedule->id,
                'dipublikasi',
                'dihapus',
            );
        });
    }

    public function assignSchedule(int $employeeId, int $scheduleId): void
    {
        DB::transaction(function () use ($employeeId, $scheduleId) {
            // Just log the assignment
            $this->activityLog->log(
                $employeeId,
                'ASSIGN_WORK_SCHEDULE',
                'WorkSchedule',
                $scheduleId,
                'assigned',
                null,
                ['employee_id' => $employeeId, 'schedule_id' => $scheduleId]
            );
        });
    }
}
