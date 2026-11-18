<?php

declare(strict_types=1);

namespace App\Services\Career;

use App\Models\Employee;
use App\Models\StudyLeave;
use App\Services\Audit\ActivityLogService;
use Illuminate\Support\Facades\DB;

class StudyLeaveService
{
    public function __construct(
        protected ActivityLogService $activityLog
    ) {}

    public function createStudyLeave(
        Employee $employee,
        string $studyProgram,
        string $university,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        string $scholarshipName,
        int $supervisorId
    ): StudyLeave {
        return DB::transaction(function () use ($employee, $studyProgram, $university, $startDate, $endDate, $scholarshipName) {
            DB::table('employees')->where('id', $employee->id)->lockForUpdate()->first();

            $studyLeave = StudyLeave::create([
                'employee_id' => $employee->id,
                'study_program' => $studyProgram,
                'university' => $university,
                'start_date' => $startDate,
                'expected_end_date' => $endDate,
                'scholarship_name' => $scholarshipName,
                'status' => 'direncanakan',
            ]);

            $this->activityLog->log(
                $employee->user?->id,
                'CREATE_STUDY_LEAVE',
                'StudyLeave',
                $studyLeave->id,
                null,
                'direncanakan',
            );

            return $studyLeave;
        });
    }

    public function approveStudyLeave(StudyLeave $studyLeave, Employee $approver): StudyLeave
    {
        if ($studyLeave->status !== 'direncanakan') {
            throw new \RuntimeException('Hanya pengajuan studi lanjut yang direncanakan yang dapat disetujui.');
        }

        return DB::transaction(function () use ($studyLeave, $approver) {
            DB::table('employees')->where('id', $studyLeave->employee_id)->lockForUpdate()->first();

            $studyLeave->update([
                'status' => 'berjalan',
            ]);

            $this->activityLog->log(
                $approver->user?->id,
                'APPROVE_STUDY_LEAVE',
                'StudyLeave',
                $studyLeave->id,
                'direncanakan',
                'berjalan',
            );

            return $studyLeave->refresh();
        });
    }

    public function rejectStudyLeave(StudyLeave $studyLeave, Employee $approver, string $decisionNote): StudyLeave
    {
        if ($studyLeave->status !== 'direncanakan') {
            throw new \RuntimeException('Hanya pengajuan studi lanjut yang direncanakan yang dapat ditolak.');
        }

        return DB::transaction(function () use ($studyLeave, $approver, $decisionNote) {
            DB::table('employees')->where('id', $studyLeave->employee_id)->lockForUpdate()->first();

            $studyLeave->update([
                'status' => 'dibatalkan',
                'mentoring_notes' => $decisionNote,
            ]);

            $this->activityLog->log(
                $approver->user?->id,
                'REJECT_STUDY_LEAVE',
                'StudyLeave',
                $studyLeave->id,
                'direncanakan',
                'dibatalkan',
            );

            return $studyLeave->refresh();
        });
    }
}
