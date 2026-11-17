<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\EmployeeAssignment;
use App\Models\User;
use App\Services\Audit\ActivityLogService;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeeAssignmentPolicy
{
    use HandlesAuthorization;

    public function __construct(protected ActivityLogService $activityLog) {}

    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin BSDM') ||
               $user->hasRole('Kepala Biro') ||
               $user->hasRole('Kepala Bagian');
    }

    public function view(User $user, EmployeeAssignment $assignment): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Kepala Bagian') && $user->employee_id) {
            return $this->isInUnitScope($user, $assignment);
        }

        return $user->employee_id === $assignment->employee_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro');
    }

    public function update(User $user, EmployeeAssignment $assignment): bool
    {
        return $user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro');
    }

    public function terminate(User $user, EmployeeAssignment $assignment): bool
    {
        return $user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro');
    }

    public function delete(User $user, EmployeeAssignment $assignment): bool
    {
        return $user->hasRole('Admin BSDM');
    }

    private function isInUnitScope(User $user, EmployeeAssignment $assignment): bool
    {
        if (! $user->employee_id) {
            return false;
        }

        $userUnitId = $user->employee?->unit_id;
        if (! $userUnitId) {
            return false;
        }

        return $assignment->unit_id === $userUnitId ||
               $assignment->unit?->parent_id === $userUnitId;
    }
}
