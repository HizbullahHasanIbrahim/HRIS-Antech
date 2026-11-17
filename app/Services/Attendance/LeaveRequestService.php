<?php

namespace App\Services\Attendance;

use App\Enums\LeaveRequestStatus;
use App\Exceptions\ConcurrencyConflictException;
use App\Exceptions\UnauthorizedActionException;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class LeaveRequestService
{
    /**
     * Validate and create a leave request.
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
        
        $this->validateLeaveBalance($employee, $leaveTypeId, $startDate, $endDate);
        
        $this->validateNoOverlappingRequests($employee, $startDate, $endDate);
        
        return DB::transaction(function () use ($employee, $leaveTypeId, $startDate, $endDate, $reason, $approverId) {
            $totalDays = $this->calculateWorkingDays($startDate, $endDate);
            
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
            
            $this->logActivity($employee->id, 'CREATE_LEAVE_REQUEST', 'LeaveRequest', $leaveRequest->id, null, LeaveRequestStatus::SUBMITTED->value);
            
            return $leaveRequest;
        });
    }
    
    /**
     * Approve a leave request.
     */
    public function approveRequest(LeaveRequest $request, Employee $approver, ?string $decisionNote = null): LeaveRequest
    {
        $this->validateApprover($request, $approver, $decisionNote);
        
        return DB::transaction(function () use ($request, $approver, $decisionNote) {
            $this->lockRelatedRecords($request);
            
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
            
            $request->refresh();
            
            $this->updateLeaveBalance($request);
            
            $this->logActivity($request->employee_id, 'APPROVE_LEAVE_REQUEST', 'LeaveRequest', $request->id, LeaveRequestStatus::SUBMITTED->value, LeaveRequestStatus::APPROVED->value);
            
            return $request;
        });
    }
    
    /**
     * Reject a leave request.
     */
    public function rejectRequest(LeaveRequest $request, Employee $approver, string $decisionNote): LeaveRequest
    {
        $this->validateApprover($request, $approver, $decisionNote);
        
        return DB::transaction(function () use ($request, $approver, $decisionNote) {
            $this->lockRelatedRecords($request);
            
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
            
            $request->refresh();
            
            $this->logActivity($request->employee_id, 'REJECT_LEAVE_REQUEST', 'LeaveRequest', $request->id, LeaveRequestStatus::SUBMITTED->value, LeaveRequestStatus::REJECTED->value);
            
            return $request;
        });
    }
    
    /**
     * Cancel a pending leave request.
     */
    public function cancelRequest(LeaveRequest $request): LeaveRequest
    {
        if (! $request->status === LeaveRequestStatus::SUBMITTED->value) {
            throw new UnauthorizedActionException('Only submitted leave requests can be cancelled.');
        }
        
        return DB::transaction(function () use ($request) {
            $updated = LeaveRequest::where('id', $request->id)
                ->where('version', $request->version)
                ->update([
                    'status' => LeaveRequestStatus::CANCELLED->value,
                    'version' => $request->version + 1,
                ]);
                
            if ($updated === 0) {
                throw new ConcurrencyConflictException('Leave request has been modified by another user.');
            }
            
            $request->refresh();
            
            $this->logActivity($request->employee_id, 'CANCEL_LEAVE_REQUEST', 'LeaveRequest', $request->id, LeaveRequestStatus::SUBMITTED->value, LeaveRequestStatus::CANCELLED->value);
            
            return $request;
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
        if ($endDate < $startDate) {
            throw new \InvalidArgumentException('End date must be after start date.');
        }
    }
    
    protected function validateLeaveBalance(Employee $employee, int $leaveTypeId, \DateTimeInterface $startDate, \DateTimeInterface $endDate): void
    {
        $totalDays = $this->calculateWorkingDays($startDate, $endDate);
        
        $balance = LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveTypeId)
            ->first();
            
        if (! $balance || $balance->remaining_amount < $totalDays) {
            throw new \InvalidArgumentException('Insufficient leave balance.');
        }
    }
    
    protected function validateNoOverlappingRequests(Employee $employee, \DateTimeInterface $startDate, \DateTimeInterface $endDate): void
    {
        $overlapping = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'disetujui')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($q2) use ($startDate, $endDate) {
                      $q2->where('start_date', '<=', $startDate)
                         ->where('end_date', '>=', $endDate);
                  });
            })
            ->exists();
            
        if ($overlapping) {
            throw new \InvalidArgumentException('Leave date overlaps with an existing approved leave.');
        }
    }
    
    protected function calculateWorkingDays(\DateTimeInterface $start, \DateTimeInterface $end): int
    {
        $start = \Carbon\Carbon::parse($start)->startOfDay();
        $end = \Carbon\Carbon::parse($end)->startOfDay();
        
        return $start->diffInBusinessDays($end) + 1;
    }
    
    protected function lockRelatedRecords(LeaveRequest $request): void
    {
        DB::table('leave_balances')
            ->where('employee_id', $request->employee_id)
            ->where('leave_type_id', $request->leave_type_id)
            ->lockForUpdate()
            ->first();
            
        DB::table('employees')
            ->where('id', $request->employee_id)
            ->lockForUpdate()
            ->first();
    }
    
    protected function updateLeaveBalance(LeaveRequest $request): void
    {
        $balance = LeaveBalance::where('employee_id', $request->employee_id)
            ->where('leave_type_id', $request->leave_type_id)
            ->first();
            
        if ($balance) {
            $balance->update([
                'used_amount' => $balance->used_amount + $request->total_days,
                'remaining_amount' => $balance->remaining_amount - $request->total_days,
            ]);
        }
    }
    
    protected function logActivity(int $actorId, string $action, string $entityType, int $entityId, ?string $oldStatus, ?string $newStatus): void
    {
        \App\Models\ActivityLog::create([
            'actor_id' => $actorId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'created_at' => now(),
        ]);
    }
    
    protected function validateApprover(LeaveRequest $request, Employee $approver, ?string $decisionNote): void
    {
        if ($request->approver_id !== $approver->id) {
            throw new UnauthorizedActionException('You are not authorized to approve this leave request.');
        }
        
        if (! $request->status === LeaveRequestStatus::SUBMITTED->value) {
            throw new UnauthorizedActionException('Only submitted leave requests can be approved.');
        }
        
        if (($request->status === LeaveRequestStatus::REJECTED->value || $request->status === LeaveRequestStatus::CANCELLED->value) && empty($decisionNote)) {
            throw new \InvalidArgumentException('Decision note is required for rejection or cancellation.');
        }
    }
}