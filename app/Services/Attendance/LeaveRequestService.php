<?php

declare(strict_types=1);

namespace App\Services\Attendance;

use App\Enums\LeaveRequestStatus;
use App\Exceptions\BusinessRuleViolationException;
use App\Exceptions\ConcurrencyConflictException;
use App\Exceptions\UnauthorizedActionException;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Services\Audit\ActivityLogService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveRequestService
{
    public function __construct(
        protected ActivityLogService $activityLog
    ) {}

    /**
     * Create a new leave request.
     */
    public function createRequest(
        Employee $employee,
        int $leaveTypeId,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        string $reason,
        int $approverId
    ): LeaveRequest {
        $this->validateNoSelfApproval($employee, $approverId);
        $this->validateDateRange($startDate, $endDate);

        return DB::transaction(function () use ($employee, $leaveTypeId, $startDate, $endDate, $reason, $approverId) {
            // Pessimistic parent-row mutex (SDD 5, EX-02/EX-07/EX-08)
            DB::table('employees')->where('id', $employee->id)->lockForUpdate()->first();

            $this->validateNoOverlappingRequests($employee, $startDate, $endDate);

            $totalDays = $this->calculateWorkingDays($startDate, $endDate);
            $this->validateLeaveBalance($employee, $leaveTypeId, $totalDays);

            $leaveRequest = LeaveRequest::create([
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveTypeId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'total_days' => $totalDays,
                'reason' => $reason,
                'status' => LeaveRequestStatus::SUBMITTED->value,
                'approver_id' => $approverId,
                'submitted_at' => now(),
                'version' => 1,
            ]);

            $this->activityLog->log(
                $employee->user?->id,
                'CREATE_LEAVE_REQUEST',
                'LeaveRequest',
                $leaveRequest->id,
                null,
                LeaveRequestStatus::SUBMITTED->value,
            );

            return $leaveRequest;
        });
    }

    /**
     * Approve a submitted leave request (RULE-03: lock balance row in same tx).
     */
    public function approveRequest(LeaveRequest $request, Employee $approver, ?string $decisionNote = null): LeaveRequest
    {
        $this->validateTransition($request, LeaveRequestStatus::APPROVED, $approver);

        return DB::transaction(function () use ($request, $approver, $decisionNote) {
            $this->lockParentRows($request);

            $updated = LeaveRequest::where('id', $request->id)
                ->where('version', $request->version)
                ->update([
                    'status' => LeaveRequestStatus::APPROVED->value,
                    'decided_at' => now(),
                    'decision_note' => $decisionNote,
                    'version' => $request->version + 1,
                ]);

            if ($updated === 0) {
                throw new ConcurrencyConflictException('Leave request has been modified by another user.');
            }

            $this->applyToLeaveBalance($request);

            $this->activityLog->log(
                $approver->user?->id,
                'APPROVE_LEAVE_REQUEST',
                'LeaveRequest',
                $request->id,
                LeaveRequestStatus::SUBMITTED->value,
                LeaveRequestStatus::APPROVED->value,
                $decisionNote,
            );

            return $request->refresh();
        });
    }

    /**
     * Reject a submitted leave request (RULE-11: reason required).
     */
    public function rejectRequest(LeaveRequest $request, Employee $approver, string $decisionNote): LeaveRequest
    {
        if (trim($decisionNote) === '') {
            throw new BusinessRuleViolationException('Decision note is required for rejection.');
        }

        $this->validateTransition($request, LeaveRequestStatus::REJECTED, $approver);

        return DB::transaction(function () use ($request, $approver, $decisionNote) {
            $this->lockParentRows($request);

            $updated = LeaveRequest::where('id', $request->id)
                ->where('version', $request->version)
                ->update([
                    'status' => LeaveRequestStatus::REJECTED->value,
                    'decided_at' => now(),
                    'decision_note' => $decisionNote,
                    'version' => $request->version + 1,
                ]);

            if ($updated === 0) {
                throw new ConcurrencyConflictException('Leave request has been modified by another user.');
            }

            $this->activityLog->log(
                $approver->user?->id,
                'REJECT_LEAVE_REQUEST',
                'LeaveRequest',
                $request->id,
                LeaveRequestStatus::SUBMITTED->value,
                LeaveRequestStatus::REJECTED->value,
                $decisionNote,
            );

            return $request->refresh();
        });
    }

    /**
     * Cancel a submitted leave request.
     */
    public function cancelRequest(LeaveRequest $request, ?string $reason = null): LeaveRequest
    {
        if ($request->status !== LeaveRequestStatus::SUBMITTED->value) {
            throw new BusinessRuleViolationException('Only submitted leave requests can be cancelled.');
        }

        return DB::transaction(function () use ($request, $reason) {
            $updated = LeaveRequest::where('id', $request->id)
                ->where('version', $request->version)
                ->update([
                    'status' => LeaveRequestStatus::CANCELLED->value,
                    'decided_at' => now(),
                    'decision_note' => $reason,
                    'version' => $request->version + 1,
                ]);

            if ($updated === 0) {
                throw new ConcurrencyConflictException('Leave request has been modified by another user.');
            }

            $this->activityLog->log(
                $request->employee?->user?->id,
                'CANCEL_LEAVE_REQUEST',
                'LeaveRequest',
                $request->id,
                LeaveRequestStatus::SUBMITTED->value,
                LeaveRequestStatus::CANCELLED->value,
                $reason,
            );

            return $request->refresh();
        });
    }

    protected function validateNoSelfApproval(Employee $employee, int $approverId): void
    {
        if ($employee->id === $approverId) {
            throw new UnauthorizedActionException('Employee cannot approve their own leave request.');
        }
    }

    protected function validateDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): void
    {
        if (Carbon::parse($endDate)->lt(Carbon::parse($startDate))) {
            throw new BusinessRuleViolationException('End date must be on or after start date.');
        }
    }

    protected function validateLeaveBalance(Employee $employee, int $leaveTypeId, int $totalDays): void
    {
        $balance = LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveTypeId)
            ->lockForUpdate()
            ->first();

        if (! $balance || $balance->remaining_amount < $totalDays) {
            throw new BusinessRuleViolationException('Insufficient leave balance.');
        }
    }

    protected function validateNoOverlappingRequests(Employee $employee, \DateTimeInterface $startDate, \DateTimeInterface $endDate): void
    {
        $overlapping = LeaveRequest::where('employee_id', $employee->id)
            ->whereIn('status', [LeaveRequestStatus::SUBMITTED->value, LeaveRequestStatus::APPROVED->value])
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->exists();

        if ($overlapping) {
            throw new BusinessRuleViolationException('Leave date overlaps with an existing leave request.');
        }
    }

    protected function calculateWorkingDays(\DateTimeInterface $start, \DateTimeInterface $end): int
    {
        $start = Carbon::parse($start)->startOfDay();
        $end = Carbon::parse($end)->startOfDay();

        return max(1, (int) $start->diffInWeekdays($end) + 1);
    }

    protected function lockParentRows(LeaveRequest $request): void
    {
        DB::table('employees')->where('id', $request->employee_id)->lockForUpdate()->first();

        DB::table('leave_balances')
            ->where('employee_id', $request->employee_id)
            ->where('leave_type_id', $request->leave_type_id)
            ->lockForUpdate()
            ->first();
    }

    protected function applyToLeaveBalance(LeaveRequest $request): void
    {
        $balance = LeaveBalance::where('employee_id', $request->employee_id)
            ->where('leave_type_id', $request->leave_type_id)
            ->lockForUpdate()
            ->first();

        if ($balance === null) {
            throw new BusinessRuleViolationException('Leave balance not found.');
        }

        $balance->update([
            'used_days' => $balance->used_days + $request->total_days,
            'version' => $balance->version + 1,
        ]);
    }

    protected function validateTransition(
        LeaveRequest $request,
        LeaveRequestStatus $target,
        Employee $approver
    ): void {
        if ($request->approver_id !== $approver->id) {
            throw new UnauthorizedActionException('You are not authorized to decide this leave request.');
        }

        if ($request->status !== LeaveRequestStatus::SUBMITTED->value) {
            throw new BusinessRuleViolationException(
                sprintf('Cannot transition leave request from "%s" to "%s".', $request->status, $target->value)
            );
        }
    }
}
