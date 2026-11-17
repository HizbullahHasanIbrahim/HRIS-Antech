<?php

declare(strict_types=1);

namespace App\Services\Career;

use App\Models\AcademicRankHistory;
use App\Models\Employee;
use App\Services\Audit\ActivityLogService;
use Illuminate\Support\Facades\DB;

class AcademicRankService
{
    public function __construct(
        protected ActivityLogService $activityLog
    ) {}

    /**
     * Create a new academic rank history record.
     */
    public function createRankHistory(
        Employee $employee,
        string $rankName,
        float $creditPoints,
        bool $isAccelerated,
        int $approverId
    ): AcademicRankHistory {
        return DB::transaction(function () use ($employee, $rankName, $creditPoints, $isAccelerated, $approverId) {
            // Pessimistic lock on parent employee row (SDD-5.5)
            DB::table('employees')->where('id', $employee->id)->lockForUpdate()->first();

            $rank = AcademicRankHistory::create([
                'employee_id' => $employee->id,
                'rank_name' => $rankName,
                'credit_points' => $creditPoints,
                'is_accelerated' => $isAccelerated,
                'status' => 'diajukan',
                'submitted_at' => now(),
                'approver_id' => $approverId,
                'version' => 1,
            ]);

            $this->activityLog->log(
                null,
                'CREATE_ACADEMIC_RANK',
                'AcademicRankHistory',
                $rank->id,
                null,
                'diajukan',
                null,
                ['rank' => $rankName, 'credit' => $creditPoints, 'accelerated' => $isAccelerated]
            );

            return $rank;
        });
    }

    /**
     * Approve an academic rank history record.
     */
    public function approveRankHistory(AcademicRankHistory $rank, Employee $approver): AcademicRankHistory
    {
        if ($rank->status !== 'diajukan') {
            throw new \RuntimeException('Hanya rank yang sedang diajukan yang dapat disetujui.');
        }

        return DB::transaction(function () use ($rank, $approver) {
            // Pessimistic lock on parent employee row
            DB::table('employees')->where('id', $rank->employee_id)->lockForUpdate()->first();

            // Optimistic locking for version
            $updated = AcademicRankHistory::where('id', $rank->id)
                ->where('version', $rank->version)
                ->update([
                    'status' => 'disetujui',
                    'decided_at' => now(),
                    'effective_date' => now()->addDays(1),
                    'version' => $rank->version + 1,
                ]);

            if ($updated === 0) {
                throw new \RuntimeException('Rank history has been modified by another user.');
            }

            $rank->refresh();

            $this->activityLog->log(
                $approver->user?->id,
                'APPROVE_ACADEMIC_RANK',
                'AcademicRankHistory',
                $rank->id,
                'diajukan',
                'disetujui',
                null,
                ['effective_date' => $rank->effective_date]
            );

            return $rank;
        });
    }

    /**
     * Reject an academic rank history record.
     */
    public function rejectRankHistory(AcademicRankHistory $rank, Employee $approver, string $decisionNote): AcademicRankHistory
    {
        if ($rank->status !== 'diajukan') {
            throw new \RuntimeException('Hanya rank yang sedang diajukan yang dapat ditolak.');
        }

        return DB::transaction(function () use ($rank, $approver, $decisionNote) {
            // Pessimistic lock on parent employee row
            DB::table('employees')->where('id', $rank->employee_id)->lockForUpdate()->first();

            // Optimistic locking for version
            $updated = AcademicRankHistory::where('id', $rank->id)
                ->where('version', $rank->version)
                ->update([
                    'status' => 'ditolak',
                    'decided_at' => now(),
                    'version' => $rank->version + 1,
                ]);

            if ($updated === 0) {
                throw new \RuntimeException('Rank history has been modified by another user.');
            }

            $rank->refresh();

            $this->activityLog->log(
                $approver->id,
                'REJECT_ACADEMIC_RANK',
                'AcademicRankHistory',
                $rank->id,
                'diajukan',
                'ditolak',
                $decisionNote
            );

            return $rank;
        });
    }
}
