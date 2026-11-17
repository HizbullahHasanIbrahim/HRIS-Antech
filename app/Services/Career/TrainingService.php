<?php

declare(strict_types=1);

namespace App\Services\Career;

use App\Models\Employee;
use App\Models\Training;
use App\Services\Audit\ActivityLogService;
use Illuminate\Support\Facades\DB;

class TrainingService
{
    public function __construct(
        protected ActivityLogService $activityLog
    ) {}

    public function createTraining(
        Employee $employee,
        string $title,
        string $trainingType,
        string $organizer,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        int $durationHours,
        ?int $retirementId = null
    ): Training {
        return DB::transaction(function () use ($employee, $title, $trainingType, $organizer, $startDate, $endDate, $durationHours, $retirementId) {
            DB::table('employees')->where('id', $employee->id)->lockForUpdate()->first();

            $training = Training::create([
                'employee_id' => $employee->id,
                'retirement_id' => $retirementId,
                'title' => $title,
                'training_type' => $trainingType,
                'organizer' => $organizer,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'duration_hours' => $durationHours,
                'status' => 'dijadwalkan',
            ]);

            $this->activityLog->log(
                $employee->user?->id,
                'CREATE_TRAINING',
                'Training',
                $training->id,
                null,
                'dijadwalkan',
            );

            return $training;
        });
    }

    public function completeTraining(Training $training, ?string $certificateNumber = null, ?string $certificatePath = null): Training
    {
        if ($training->status === 'selesai') {
            throw new \RuntimeException('Pelatihan sudah diselesaikan.');
        }

        return DB::transaction(function () use ($training, $certificateNumber, $certificatePath) {
            $training->update([
                'status' => 'selesai',
                'certificate_number' => $certificateNumber,
                'certificate_path' => $certificatePath,
            ]);

            $this->activityLog->log(
                $training->employee?->user?->id,
                'COMPLETE_TRAINING',
                'Training',
                $training->id,
                'dijadwalkan',
                'selesai',
            );

            return $training->refresh();
        });
    }

    public function cancelTraining(Training $training, string $reason): Training
    {
        return DB::transaction(function () use ($training, $reason) {
            $training->update([
                'status' => 'dibatalkan',
            ]);

            $this->activityLog->log(
                $training->employee?->user?->id,
                'CANCEL_TRAINING',
                'Training',
                $training->id,
                'dijadwalkan',
                'dibatalkan',
                $reason,
            );

            return $training->refresh();
        });
    }
}
