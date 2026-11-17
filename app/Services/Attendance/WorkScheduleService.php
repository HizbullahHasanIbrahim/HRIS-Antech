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
        string $name,
        string $scheduleType,
        \DateTimeInterface $effectiveStart,
        \DateTimeInterface $effectiveEnd,
        array $days
    ): WorkSchedule {
        return DB::transaction(function () use ($name, $scheduleType, $effectiveStart, $effectiveEnd, $days) {
            // Deactivate any existing active schedules of same type
            WorkSchedule::where('schedule_type', $scheduleType)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $schedule = WorkSchedule::create([
                'name' => $name,
                'schedule_type' => $scheduleType,
                'effective_start' => $effectiveStart,
                'effective_end' => $effectiveEnd,
                'is_active' => true,
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
                null,
                'CREATE_WORK_SCHEDULE',
                'WorkSchedule',
                $schedule->id,
                null,
                'aktif',
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
                $schedule->is_active ? 'aktif' : 'nonaktif',
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
                'aktif',
                'dihapus',
            );
        });
    }

    public function assignSchedule(int $employeeId, int $scheduleId): void
    {
        DB::transaction(function () use ($employeeId, $scheduleId) {
            // Just log the assignment
            $this->activityLog->log(
                null,
                'ASSIGN_WORK_SCHEDULE',
                'Employee',
                $employeeId,
                null,
                'aktif',
                null,
                ['schedule_id' => $scheduleId]
            );
        });
    }
}
