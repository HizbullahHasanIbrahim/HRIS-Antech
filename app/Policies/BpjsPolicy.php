<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BpjsMembership;
use App\Models\User;
use App\Services\Audit\ActivityLogService;
use Illuminate\Auth\Access\HandlesAuthorization;

class BpjsPolicy
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

    public function view(User $user, BpjsMembership $membership): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Kepala Bagian') && $user->employee_id) {
            return $this->isInUnitScope($user, $membership);
        }

        // Employee can view own BPJS membership
        return $user->employee_id === $membership->employee_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Admin BSDM') ||
               $user->hasRole('Kepala Biro') ||
               $user->hasRole('Welfare Admin');
    }

    public function update(User $user, BpjsMembership $membership): bool
    {
        return false; // BPJS memberships are immutable after creation
    }

    public function cancel(User $user, BpjsMembership $membership): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Welfare Admin')) {
            return true;
        }

        return false;
    }

    public function delete(User $user, BpjsMembership $membership): bool
    {
        return false;
    }

    private function isInUnitScope(User $user, BpjsMembership $membership): bool
    {
        if (! $user->employee_id) {
            return false;
        }

        $userUnitId = $user->employee?->unit_id;
        if (! $userUnitId) {
            return false;
        }

        return $membership->employee?->unit_id === $userUnitId ||
               $membership->employee?->department?->parent_id === $userUnitId;
    }
}
