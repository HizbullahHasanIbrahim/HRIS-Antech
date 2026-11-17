<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PerformanceTarget;
use App\Models\User;
use App\Services\Audit\ActivityLogService;
use Illuminate\Auth\Access\HandlesAuthorization;

class SkpPolicy
{
    use HandlesAuthorization;

    public function __construct(protected ActivityLogService $activityLog) {}

    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin BSDM') ||
               $user->hasRole('Kepala Biro') ||
               $user->hasRole('Kepala Bagian') ||
               $user->hasRole('Dosen') ||
               $user->hasRole('Tendik');
    }

    public function view(User $user, PerformanceTarget $target): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Kepala Bagian') && $user->employee_id) {
            return $this->isInUnitScope($user, $target);
        }

        return $user->employee_id === $target->employee_id;
    }

    public function create(User $user): bool
    {
        return $user->employee_id !== null;
    }

    public function update(User $user, PerformanceTarget $target): bool
    {
        return false; // Targets are immutable after creation
    }

    public function evaluate(User $user, PerformanceTarget $target): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Kepala Bagian') && $user->employee_id) {
            return $this->isInUnitScope($user, $target) &&
                   $user->employee_id !== $target->employee_id;
        }

        if ($user->hasRole('Pejabat Penilai') && $user->employee_id) {
            return $user->employee_id === $target->appraiser_id;
        }

        return false;
    }

    public function reject(User $user, PerformanceTarget $target): bool
    {
        return $this->evaluate($user, $target);
    }

    public function delete(User $user, PerformanceTarget $target): bool
    {
        return false;
    }

    private function isInUnitScope(User $user, PerformanceTarget $target): bool
    {
        if (! $user->employee_id) {
            return false;
        }

        $userUnitId = $user->employee?->unit_id;
        if (! $userUnitId) {
            return false;
        }

        return $target->employee?->unit_id === $userUnitId ||
               $target->employee?->department?->parent_id === $userUnitId;
    }
}
