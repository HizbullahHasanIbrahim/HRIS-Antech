<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BpjsMembership;
use App\Models\User;
use App\Services\Audit\ActivityLogService;
use Illuminate\Auth\Access\HandlesAuthorization;

class BpjsMembershipPolicy
{
    use HandlesAuthorization;

    public function __construct(protected ActivityLogService $activityLog) {}

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, BpjsMembership $membership): bool
    {
        // Admin can view all
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        // Employee can view their own
        return $user->employee_id === $membership->employee_id;
    }

    public function create(User $user): bool
    {
        return $user->employee_id !== null;
    }

    public function update(User $user, BpjsMembership $membership): bool
    {
        // Only Welfare Admin or owner can update
        return $user->hasRole('Welfare Admin') || $user->employee_id === $membership->employee_id;
    }

    public function cancel(User $user, BpjsMembership $membership): bool
    {
        // Only Welfare Admin or owner can cancel
        if ($user->hasRole('Welfare Admin') || $user->employee_id === $membership->employee_id) {
            return true;
        }

        $this->activityLog->log(
            $user->id,
            'ACCESS_DENIED',
            'BpjsMembership',
            $membership->id,
            null,
            null,
            ['attempted_action' => 'cancel', 'reason' => 'Not owner or Welfare Admin']
        );

        return false;
    }
}
