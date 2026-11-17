<?php

declare(strict_types=1);

namespace App\Services\Career;

use App\Models\Employee;
use App\Models\StaffRankHistory;
use App\Services\Audit\ActivityLogService;
use Illuminate\Support\Facades\DB;

class StaffRankService
{
    public function __construct(
        protected ActivityLogService $activityLog
    ) {}

    /**
     * Create a new staff rank history record.
     */
    public function createRankHistory(
        Employee $employee,
        string $rankGrade,
        string $status,
        int $approverId,
        ?string $functionalPosition = null
    ): StaffRankHistory {
        return DB::transaction(function () use ($employee, $rankGrade, $status, $approverId, $functionalPosition) {
            // Pessimistic lock on parent employee row (SDD-5.6)
            DB::table('employees')->where('id', $employee->id)->lockForUpdate()->first();

            $rank = StaffRankHistory::create([
                'employee_id' => $employee->id,
                'rank_grade' => $rankGrade,
                'functional_position' => $functionalPosition,
                'status' => $status,
                'submitted_at' => now(),
                'approver_id' => $approverId,
                'version' => 1,
            ]);

            $this->activityLog->log(
                null,
                'CREATE_STAFF_RANK',
                'StaffRankHistory',
                $rank->id,
                null,
                $status,
                null,
                ['rank' => $rankGrade, 'approver' => $approverId]
            );

            return $rank;
        });
    }

    /**
     * Approve a staff rank history record.
     */
    public function approveRankHistory(StaffRankHistory $rank, Employee $approver): StaffRankHistory
    {
        if ($rank->status !== 'diajukan') {
            throw new \RuntimeException('Hanya rank yang sedang diajukan yang dapat disetujui.');
        }

        return DB::transaction(function () use ($rank, $approver) {
            // Pessimistic lock on parent employee row
            DB::table('employees')->where('id', $rank->employee_id)->lockForUpdate()->first();

            // Optimistic locking for version
            $updated = StaffRankHistory::where('id', $rank->id)
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
                $approver->id,
                'APPROVE_STAFF_RANK',
                'StaffRankHistory',
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
     * Reject a staff rank history record.
     */
    public function rejectRankHistory(StaffRankHistory $rank, Employee $approver, string $decisionNote): StaffRankHistory
    {
        if ($rank->status !== 'diajukan') {
            throw new \RuntimeException('Hanya rank yang sedang diajukan yang dapat ditolak.');
        }

        return DB::transaction(function () use ($rank, $approver, $decisionNote) {
            // Pessimistic lock on parent employee row
            DB::table('employees')->where('id', $rank->employee_id)->lockForUpdate()->first();

            // Optimistic locking for version
            $updated = StaffRankHistory::where('id', $rank->id)
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
                'REJECT_STAFF_RANK',
                'StaffRankHistory',
                $rank->id,
                'diajukan',
                'ditolak',
                $decisionNote
            );

            return $rank;
        });
    }
}
