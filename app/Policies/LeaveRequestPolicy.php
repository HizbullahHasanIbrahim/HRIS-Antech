<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\Audit\ActivityLogService;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class LeaveRequestPolicy
{
    use HandlesAuthorization;

    public function __construct(protected ActivityLogService $activityLog) {}

    public function viewAny(User $user): bool
    {
        // Simple ponytail: Global access for BSDM, Unit-scoped for others.
        return true;
    }

    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->employee_id === $leaveRequest->employee_id ||
               $user->employee_id === $leaveRequest->approver_id ||
               $user->hasRole('Admin BSDM');
    }

    public function create(User $user): bool
    {
        return $user->employee_id !== null;
    }

    public function approve(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($user->employee_id === $leaveRequest->approver_id) {
            return true;
        }

        $this->logDenied($user, 'leave_requests', $leaveRequest->id, 'APPROVE');

        return false;
    }

    public function reject(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($user->employee_id === $leaveRequest->approver_id) {
            return true;
        }

        $this->logDenied($user, 'leave_requests', $leaveRequest->id, 'REJECT');

        return false;
    }

    public function cancel(User $user, LeaveRequest $leaveRequest): bool
    {
        return $user->employee_id === $leaveRequest->employee_id;
    }

    protected function logDenied(User $user, string $entityType, int $entityId, string $action): void
    {
        $this->activityLog->log(
            $user->id,
            'ACCESS_DENIED',
            $entityType,
            $entityId,
            null,
            null,
            "User attempted unauthorized action: $action"
        );

        Log::warning("ACCESS_DENIED: User {$user->id} attempted to {$action} {$entityType} {$entityId}");
    }
}
