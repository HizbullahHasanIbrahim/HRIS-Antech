<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AllowanceRequest;
use App\Models\User;
use App\Services\Audit\ActivityLogService;
use Illuminate\Auth\Access\HandlesAuthorization;

class AllowanceRequestPolicy
{
    use HandlesAuthorization;

    public function __construct(protected ActivityLogService $activityLog) {}

    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin BSDM') ||
               $user->hasRole('Kepala Biro') ||
               $user->hasRole('Kepala Bagian') ||
               $user->hasRole('Welfare Admin');
    }

    public function view(User $user, AllowanceRequest $request): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Kepala Bagian') && $user->employee_id) {
            return $this->isInUnitScope($user, $request);
        }

        if ($user->hasRole('Welfare Admin')) {
            return true;
        }

        return $user->employee_id === $request->employee_id;
    }

    public function create(User $user): bool
    {
        return $user->employee_id !== null;
    }

    public function update(User $user, AllowanceRequest $request): bool
    {
        return false; // Requests are immutable after creation
    }

    public function approve(User $user, AllowanceRequest $request): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Kepala Bagian') && $user->employee_id) {
            return $this->isInUnitScope($user, $request) &&
                   $user->employee_id !== $request->employee_id;
        }

        if ($user->hasRole('Welfare Admin')) {
            return true;
        }

        return false;
    }

    public function cancel(User $user, AllowanceRequest $request): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Welfare Admin')) {
            return true;
        }

        // Employee can cancel own request if still pending
        if ($user->employee_id === $request->employee_id && $request->status === 'diajukan') {
            return true;
        }

        return false;
    }

    public function delete(User $user, AllowanceRequest $request): bool
    {
        return false;
    }

    private function isInUnitScope(User $user, AllowanceRequest $request): bool
    {
        if (! $user->employee_id) {
            return false;
        }

        $userUnitId = $user->employee?->unit_id;
        if (! $userUnitId) {
            return false;
        }

        return $request->employee?->unit_id === $userUnitId ||
               $request->employee?->department?->parent_id === $userUnitId;
    }
}
