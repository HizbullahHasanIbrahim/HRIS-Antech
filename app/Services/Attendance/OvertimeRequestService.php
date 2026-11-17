<?php

declare(strict_types=1);

namespace App\Services\Attendance;

use App\Enums\OvertimeStatus as OvertimeRequestStatus;
use App\Exceptions\BusinessRuleViolationException;
use App\Exceptions\ConcurrencyConflictException;
use App\Exceptions\UnauthorizedActionException;
use App\Models\Employee;
use App\Models\OvertimeRequest;
use App\Services\Audit\ActivityLogService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OvertimeRequestService
{
    public function __construct(
        protected ActivityLogService $activityLog
    ) {}

    /**
     * Create a new overtime request (P-05).
     */
    public function createRequest(
        Employee $employee,
        \DateTimeInterface $workDate,
        string $startTime,
        string $endTime,
        string $reason,
        int $approverId
    ): OvertimeRequest {
        $this->validateNoSelfApproval($employee, $approverId);
        $durationHours = $this->calculateDurationHours($startTime, $endTime);

        return DB::transaction(function () use ($employee, $workDate, $startTime, $endTime, $durationHours, $reason, $approverId) {
            DB::table('employees')->where('id', $employee->id)->lockForUpdate()->first();

            $overtimeRequest = OvertimeRequest::create([
                'employee_id' => $employee->id,
                'work_date' => $workDate,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'total_hours' => $durationHours,
                'reason' => $reason,
                'status' => OvertimeRequestStatus::SUBMITTED->value,
                'approver_id' => $approverId,
                'submitted_at' => now(),
                'version' => 1,
            ]);

            $this->activityLog->log(
                $employee->user?->id,
                'CREATE_OVERTIME_REQUEST',
                'OvertimeRequest',
                $overtimeRequest->id,
                null,
                OvertimeRequestStatus::SUBMITTED->value,
            );

            return $overtimeRequest;
        });
    }

    /**
     * Approve an overtime request (P-05, RULE-03).
     */
    public function approveRequest(OvertimeRequest $request, Employee $approver, ?string $decisionNote = null): OvertimeRequest
    {
        $this->validateTransition($request, OvertimeRequestStatus::APPROVED, $approver);

        return DB::transaction(function () use ($request, $approver, $decisionNote) {
            DB::table('employees')->where('id', $request->employee_id)->lockForUpdate()->first();

            $updated = OvertimeRequest::where('id', $request->id)
                ->where('version', $request->version)
                ->update([
                    'status' => OvertimeRequestStatus::APPROVED->value,
                    'decided_at' => now(),
                    'decision_note' => $decisionNote,
                    'version' => $request->version + 1,
                ]);

            if ($updated === 0) {
                throw new ConcurrencyConflictException('Overtime request has been modified by another user.');
            }

            $this->activityLog->log(
                $approver->user?->id,
                'APPROVE_OVERTIME_REQUEST',
                'OvertimeRequest',
                $request->id,
                OvertimeRequestStatus::SUBMITTED->value,
                OvertimeRequestStatus::APPROVED->value,
                $decisionNote,
            );

            return $request->refresh();
        });
    }

    /**
     * Reject an overtime request (RULE-11: reason required).
     */
    public function rejectRequest(OvertimeRequest $request, Employee $approver, string $decisionNote): OvertimeRequest
    {
        if (trim($decisionNote) === '') {
            throw new BusinessRuleViolationException('Decision note is required for rejection.');
        }

        $this->validateTransition($request, OvertimeRequestStatus::REJECTED, $approver);

        return DB::transaction(function () use ($request, $approver, $decisionNote) {
            DB::table('employees')->where('id', $request->employee_id)->lockForUpdate()->first();

            $updated = OvertimeRequest::where('id', $request->id)
                ->where('version', $request->version)
                ->update([
                    'status' => OvertimeRequestStatus::REJECTED->value,
                    'decided_at' => now(),
                    'decision_note' => $decisionNote,
                    'version' => $request->version + 1,
                ]);

            if ($updated === 0) {
                throw new ConcurrencyConflictException('Overtime request has been modified by another user.');
            }

            $this->activityLog->log(
                $approver->user?->id,
                'REJECT_OVERTIME_REQUEST',
                'OvertimeRequest',
                $request->id,
                OvertimeRequestStatus::SUBMITTED->value,
                OvertimeRequestStatus::REJECTED->value,
                $decisionNote,
            );

            return $request->refresh();
        });
    }

    public function cancelRequest(OvertimeRequest $request, ?string $reason = null): OvertimeRequest
    {
        if ($request->status !== OvertimeRequestStatus::SUBMITTED->value) {
            throw new BusinessRuleViolationException('Only submitted overtime requests can be cancelled.');
        }

        return DB::transaction(function () use ($request, $reason) {
            $updated = OvertimeRequest::where('id', $request->id)
                ->where('version', $request->version)
                ->update([
                    'status' => OvertimeRequestStatus::CANCELLED->value,
                    'decided_at' => now(),
                    'decision_note' => $reason,
                    'version' => $request->version + 1,
                ]);

            if ($updated === 0) {
                throw new ConcurrencyConflictException('Overtime request has been modified by another user.');
            }

            $this->activityLog->log(
                $request->employee?->user?->id,
                'CANCEL_OVERTIME_REQUEST',
                'OvertimeRequest',
                $request->id,
                OvertimeRequestStatus::SUBMITTED->value,
                OvertimeRequestStatus::CANCELLED->value,
                $reason,
            );

            return $request->refresh();
        });
    }

    protected function validateNoSelfApproval(Employee $employee, int $approverId): void
    {
        if ($employee->id === $approverId) {
            throw new UnauthorizedActionException('Employee cannot approve their own overtime request.');
        }
    }

    protected function validateTransition(
        OvertimeRequest $request,
        OvertimeRequestStatus $target,
        Employee $approver
    ): void {
        if ($request->approver_id !== $approver->id) {
            $this->logDenied($approver, 'overtime_requests', $request->id, 'approve/reject');
            throw new UnauthorizedActionException('You are not authorized to decide this overtime request.');
        }

        if ($request->status !== OvertimeRequestStatus::SUBMITTED->value) {
            throw new BusinessRuleViolationException(
                sprintf('Cannot transition overtime request from "%s" to "%s".', $request->status, $target->value)
            );
        }
    }

    protected function calculateDurationHours(string $startTime, string $endTime): float
    {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        return round($start->diffInSeconds($end) / 3600, 2);
    }

    protected function logDenied(Employee $approver, string $entityType, int $entityId, string $action): void
    {
        $this->activityLog->log(
            $approver->user?->id,
            'ACCESS_DENIED',
            $entityType,
            $entityId,
            null,
            null,
            "User attempted unauthorized action: {$action}",
        );
    }
}
