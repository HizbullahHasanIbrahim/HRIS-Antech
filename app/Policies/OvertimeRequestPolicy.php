<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\OvertimeRequest;
use App\Models\User;
use App\Services\Audit\ActivityLogService;
use Illuminate\Auth\Access\HandlesAuthorization;

class OvertimeRequestPolicy
{
    use HandlesAuthorization;

    public function __construct(protected ActivityLogService $activityLog) {}

    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin BSDM') ||
               $user->hasRole('Kepala Biro') ||
               $user->hasRole('Kepala Bagian');
    }

    public function view(User $user, OvertimeRequest $overtimeRequest): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Kepala Bagian') && $user->employee_id) {
            return $this->isInUnitScope($user, $overtimeRequest);
        }

        return $user->employee_id === $overtimeRequest->employee_id;
    }

    public function create(User $user): bool
    {
        return $user->employee_id !== null;
    }

    public function approve(User $user, OvertimeRequest $overtimeRequest): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Kepala Bagian') && $user->employee_id) {
            return $this->isInUnitScope($user, $overtimeRequest) &&
                   $user->employee_id !== $overtimeRequest->employee_id;
        }

        return false;
    }

    public function reject(User $user, OvertimeRequest $overtimeRequest): bool
    {
        return $this->approve($user, $overtimeRequest);
    }

    public function cancel(User $user, OvertimeRequest $overtimeRequest): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        return $user->employee_id === $overtimeRequest->employee_id && $overtimeRequest->status === 'diajukan';
    }

    private function isInUnitScope(User $user, OvertimeRequest $overtimeRequest): bool
    {
        if (! $user->employee_id) {
            return false;
        }

        $userUnitId = $user->employee?->unit_id;
        if (! $userUnitId) {
            return false;
        }

        return $overtimeRequest->employee?->unit_id === $userUnitId ||
               $overtimeRequest->employee?->department?->parent_id === $userUnitId;
    }
}
