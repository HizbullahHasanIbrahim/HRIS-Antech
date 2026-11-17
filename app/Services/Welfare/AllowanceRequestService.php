<?php

declare(strict_types=1);

namespace App\Services\Welfare;

use App\Models\AllowanceRequest;
use App\Models\AllowanceType;
use App\Models\Employee;
use App\Services\Audit\ActivityLogService;
use Illuminate\Support\Facades\DB;

class AllowanceRequestService
{
    public function __construct(
        protected ActivityLogService $activityLog
    ) {}

    /**
     * Create a new allowance request.
     */
    public function createRequest(
        Employee $employee,
        AllowanceType $allowanceType,
        string $periodLabel,
        float $amount,
        \DateTimeInterface $submittedAt,
        int $approverId
    ): AllowanceRequest {
        return DB::transaction(function () use ($employee, $allowanceType, $periodLabel, $amount, $submittedAt, $approverId) {
            // Pessimistic lock on parent employee row (SDD-5.11)
            DB::table('employees')->where('id', $employee->id)->lockForUpdate()->first();

            // Check for existing active request for same period and type
            $existing = AllowanceRequest::where('employee_id', $employee->id)
                ->where('allowance_type_id', $allowanceType->id)
                ->where('period_label', $periodLabel)
                ->whereNotIn('status', ['ditolak', 'dibatalkan'])
                ->exists();

            if ($existing) {
                throw new \RuntimeException('Sudah ada permintaan tunjangan untuk periode dan jenis yang sama.');
            }

            // Validate that doctoral education is required for certain types (RULE-09)
            if ($allowanceType->requires_doctorate) {
                if ($employee->highest_education !== 'S3') {
                    throw new \RuntimeException('Pengajuan tunjangan doktorat memerlukan pendidikan S3.');
                }
            }

            // Prevent self-approval (RULE-14)
            if ($employee->id === $approverId) {
                throw new \RuntimeException('Tidak boleh menyetujui permintaan tunjangan sendiri.');
            }

            $request = AllowanceRequest::create([
                'employee_id' => $employee->id,
                'allowance_type_id' => $allowanceType->id,
                'period_label' => $periodLabel,
                'amount' => $amount,
                'status' => 'diajukan',
                'submitted_at' => $submittedAt,
                'approver_id' => $approverId,
                'version' => 1,
            ]);

            $this->activityLog->log(
                $employee->user?->id,
                'CREATE_ALLOWANCE_REQUEST',
                'AllowanceRequest',
                $request->id,
                null,
                'diajukan',
                null,
                ['employee_id' => $employee->id, 'allowance_type' => $allowanceType->name]
            );

            return $request;
        });
    }

    /**
     * Approve an allowance request.
     */
    public function approveRequest(AllowanceRequest $request, Employee $approver): AllowanceRequest
    {
        if ($request->status !== 'diajukan') {
            throw new \RuntimeException('Hanya permintaan tunjangan yang sedang diajukan yang dapat disetujui.');
        }

        return DB::transaction(function () use ($request, $approver) {
            // Pessimistic lock on parent employee row
            DB::table('employees')->where('id', $request->employee_id)->lockForUpdate()->first();

            // Optimistic locking for version
            $updated = AllowanceRequest::where('id', $request->id)
                ->where('version', $request->version)
                ->update([
                    'status' => 'disetujui',
                    'decided_at' => now(),
                    'version' => $request->version + 1,
                ]);

            if ($updated === 0) {
                throw new \RuntimeException('Permintaan tunjangan telah dimodifikasi oleh pengguna lain.');
            }

            $request->refresh();

            $this->activityLog->log(
                $approver->user?->id,
                'APPROVE_ALLOWANCE_REQUEST',
                'AllowanceRequest',
                $request->id,
                'diajukan',
                'disetujui',
                null,
                ['approver_id' => $approver->id]
            );

            return $request;
        });
    }

    /**
     * Cancel an allowance request.
     */
    public function cancelRequest(AllowanceRequest $request): void
    {
        if ($request->status !== 'diajukan') {
            throw new \RuntimeException('Hanya permintaan tunjangan yang sedang diajukan yang dapat dibatalkan.');
        }

        DB::transaction(function () use ($request) {
            $request->update([
                'status' => 'dibatalkan',
                'version' => $request->version + 1,
            ]);

            $this->activityLog->log(
                null,
                'CANCEL_ALLOWANCE_REQUEST',
                'AllowanceRequest',
                $request->id,
                'diajukan',
                'dibatalkan'
            );
        });
    }

    /**
     * Get active allowance requests for an employee.
     */
    public function getActiveRequests(Employee $employee): array
    {
        return AllowanceRequest::where('employee_id', $employee->id)
            ->where('status', 'diajukan')
            ->get()
            ->toArray();
    }
}
