<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Application;
use App\Models\User;
use App\Services\Audit\ActivityLogService;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApplicationPolicy
{
    use HandlesAuthorization;

    public function __construct(protected ActivityLogService $activityLog) {}

    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin BSDM') ||
               $user->hasRole('Kepala Biro') ||
               $user->hasRole('Staff Rekrutmen') ||
               $user->hasRole('Pimpinan Unit');
    }

    public function view(User $user, Application $application): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Staff Rekrutmen') && $user->employee_id) {
            return $this->isInUnitScope($user, $application);
        }

        if ($user->hasRole('Pimpinan Unit') && $user->employee_id) {
            return $this->isInUnitScope($user, $application);
        }

        // Applicant can view own application
        return $application->applicant?->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        // Public - applicants can apply
        return true;
    }

    public function update(User $user, Application $application): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Staff Rekrutmen') && $user->employee_id) {
            return $this->isInUnitScope($user, $application);
        }

        return false;
    }

    public function decide(User $user, Application $application): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Pimpinan Unit') && $user->employee_id) {
            return $this->isInUnitScope($user, $application);
        }

        return false;
    }

    public function delete(User $user, Application $application): bool
    {
        return false; // Applications are never deleted
    }

    private function isInUnitScope(User $user, Application $application): bool
    {
        if (! $user->employee_id) {
            return false;
        }

        $userUnitId = $user->employee?->unit_id;
        if (! $userUnitId) {
            return false;
        }

        return $application->vacancy?->unit_id === $userUnitId;
    }
}
