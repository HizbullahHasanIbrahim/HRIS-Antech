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
        string $institution,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        string $fundingSource,
        ?string $scholarshipName,
        int $supervisorId
    ): StudyLeave {
        return DB::transaction(function () use ($employee, $studyProgram, $institution, $startDate, $endDate, $fundingSource, $scholarshipName, $supervisorId) {
            DB::table('employees')->where('id', $employee->id)->lockForUpdate()->first();

            $studyLeave = StudyLeave::create([
                'employee_id' => $employee->id,
                'study_program' => $studyProgram,
                'institution' => $institution,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'funding_source' => $fundingSource,
                'scholarship_name' => $scholarshipName,
                'status' => 'diajukan',
                'supervisor_id' => $supervisorId,
            ]);

            $this->activityLog->log(
                $employee->user?->id,
                'CREATE_STUDY_LEAVE',
                'StudyLeave',
                $studyLeave->id,
                null,
                'diajukan',
            );

            return $studyLeave;
        });
    }

    public function approveStudyLeave(StudyLeave $studyLeave, Employee $approver): StudyLeave
    {
        if ($studyLeave->status !== 'diajukan') {
            throw new \RuntimeException('Hanya pengajuan studi lanjut yang diajukan yang dapat disetujui.');
        }

        return DB::transaction(function () use ($studyLeave, $approver) {
            DB::table('employees')->where('id', $studyLeave->employee_id)->lockForUpdate()->first();

            $studyLeave->update([
                'status' => 'disetujui',
                'approved_at' => now(),
            ]);

            $this->activityLog->log(
                $approver->user?->id,
                'APPROVE_STUDY_LEAVE',
                'StudyLeave',
                $studyLeave->id,
                'diajukan',
                'disetujui',
            );

            return $studyLeave->refresh();
        });
    }

    public function rejectStudyLeave(StudyLeave $studyLeave, Employee $approver, string $decisionNote): StudyLeave
    {
        if ($studyLeave->status !== 'diajukan') {
            throw new \RuntimeException('Hanya pengajuan studi lanjut yang diajukan yang dapat ditolak.');
        }

        return DB::transaction(function () use ($studyLeave, $approver, $decisionNote) {
            DB::table('employees')->where('id', $studyLeave->employee_id)->lockForUpdate()->first();

            $studyLeave->update([
                'status' => 'ditolak',
                'decision_note' => $decisionNote,
                'approved_at' => now(),
            ]);

            $this->activityLog->log(
                $approver->user?->id,
                'REJECT_STUDY_LEAVE',
                'StudyLeave',
                $studyLeave->id,
                'diajukan',
                'ditolak',
                $decisionNote,
            );

            return $studyLeave->refresh();
        });
    }
}
