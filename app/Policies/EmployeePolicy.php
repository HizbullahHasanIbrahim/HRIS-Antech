<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;
use App\Services\Audit\ActivityLogService;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmployeePolicy
{
    use HandlesAuthorization;

    public function __construct(protected ActivityLogService $activityLog) {}

    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin BSDM') ||
               $user->hasRole('Kepala Biro') ||
               $user->hasRole('Kepala Bagian');
    }

    public function view(User $user, Employee $employee): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Kepala Bagian') && $user->employee_id) {
            // Unit scope: can view employees in own unit and sub-units
            return $this->isInUnitScope($user, $employee);
        }

        return $user->employee_id === $employee->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro');
    }

    public function update(User $user, Employee $employee): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        // Employee can update own profile (limited fields)
        if ($user->employee_id === $employee->id) {
            return true;
        }

        if ($user->hasRole('Kepala Bagian') && $user->employee_id) {
            return $this->isInUnitScope($user, $employee);
        }

        return false;
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->hasRole('Admin BSDM');
    }

    private function isInUnitScope(User $user, Employee $employee): bool
    {
        if (! $user->employee_id) {
            return false;
        }

        $userUnitId = $user->employee?->unit_id;
        if (! $userUnitId) {
            return false;
        }

        // Check if employee is in same unit or child units
        return $employee->unit_id === $userUnitId ||
               $employee->department?->parent_id === $userUnitId;
    }
}
