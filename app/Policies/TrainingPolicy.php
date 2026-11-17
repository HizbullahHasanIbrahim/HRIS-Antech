<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Training;
use App\Models\User;
use App\Services\Audit\ActivityLogService;
use Illuminate\Auth\Access\HandlesAuthorization;

class TrainingPolicy
{
    use HandlesAuthorization;

    public function __construct(protected ActivityLogService $activityLog) {}

    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin BSDM') ||
               $user->hasRole('Kepala Biro') ||
               $user->hasRole('Kepala Bagian');
    }

    public function view(User $user, Training $training): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Kepala Bagian') && $user->employee_id) {
            return $this->isInUnitScope($user, $training);
        }

        return $user->employee_id === $training->employee_id;
    }

    public function create(User $user): bool
    {
        return false; // Trainings are typically created by admin
    }

    public function update(User $user, Training $training): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Kepala Bagian') && $user->employee_id) {
            return $this->isInUnitScope($user, $training);
        }

        return false;
    }

    public function complete(User $user, Training $training): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        return $user->employee_id === $training->employee_id;
    }

    public function cancel(User $user, Training $training): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Kepala Bagian') && $user->employee_id) {
            return $this->isInUnitScope($user, $training);
        }

        return false;
    }

    public function delete(User $user, Training $training): bool
    {
        return false;
    }

    private function isInUnitScope(User $user, Training $training): bool
    {
        if (! $user->employee_id) {
            return false;
        }

        $userUnitId = $user->employee?->unit_id;
        if (! $userUnitId) {
            return false;
        }

        return $training->employee?->unit_id === $userUnitId ||
               $training->employee?->department?->parent_id === $userUnitId;
    }
}
