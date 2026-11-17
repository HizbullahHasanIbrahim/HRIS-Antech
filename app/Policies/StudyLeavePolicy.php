<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\StudyLeave;
use App\Models\User;
use App\Services\Audit\ActivityLogService;
use Illuminate\Auth\Access\HandlesAuthorization;

class StudyLeavePolicy
{
    use HandlesAuthorization;

    public function __construct(protected ActivityLogService $activityLog) {}

    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin BSDM') ||
               $user->hasRole('Kepala Biro') ||
               $user->hasRole('Kepala Bagian');
    }

    public function view(User $user, StudyLeave $studyLeave): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Kepala Bagian') && $user->employee_id) {
            return $this->isInUnitScope($user, $studyLeave);
        }

        return $user->employee_id === $studyLeave->employee_id;
    }

    public function create(User $user): bool
    {
        return $user->employee_id !== null;
    }

    public function update(User $user, StudyLeave $studyLeave): bool
    {
        return false; // Submissions are immutable after creation
    }

    public function approve(User $user, StudyLeave $studyLeave): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Kepala Bagian') && $user->employee_id) {
            return $this->isInUnitScope($user, $studyLeave) &&
                   $user->employee_id !== $studyLeave->employee_id;
        }

        if ($user->hasRole('Supervisor') && $user->employee_id) {
            return $user->employee_id === $studyLeave->supervisor_id;
        }

        return false;
    }

    public function reject(User $user, StudyLeave $studyLeave): bool
    {
        return $this->approve($user, $studyLeave);
    }

    public function delete(User $user, StudyLeave $studyLeave): bool
    {
        return false;
    }

    private function isInUnitScope(User $user, StudyLeave $studyLeave): bool
    {
        if (! $user->employee_id) {
            return false;
        }

        $userUnitId = $user->employee?->unit_id;
        if (! $userUnitId) {
            return false;
        }

        return $studyLeave->employee?->unit_id === $userUnitId ||
               $studyLeave->employee?->department?->parent_id === $userUnitId;
    }
}
