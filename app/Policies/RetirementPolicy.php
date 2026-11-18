<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Retirement;
use App\Models\User;
use App\Services\Audit\ActivityLogService;
use Illuminate\Auth\Access\HandlesAuthorization;

class RetirementPolicy
{
    use HandlesAuthorization;

    public function __construct(protected ActivityLogService $activityLog) {}

    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin BSDM') ||
               $user->hasRole('Kepala Biro') ||
               $user->hasRole('Kepala Bagian');
    }

    public function view(User $user, Retirement $retirement): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Kepala Bagian') && $user->employee_id) {
            return $retirement->employee?->unit_id === $user->employee?->unit_id;
        }

        return $user->employee_id === $retirement->employee_id;
    }

    public function create(User $user): bool
    {
        return $user->employee_id !== null;
    }

    public function cancel(User $user, Retirement $retirement): bool
    {
        if (! in_array($retirement->status, ['diusulkan', 'diproses'], true)) {
            return false;
        }

        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        return $user->employee_id === $retirement->employee_id;
    }

    public function process(User $user, Retirement $retirement): bool
    {
        if (! in_array($retirement->status, ['diusulkan', 'diproses'], true)) {
            return false;
        }

        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        return $user->employee_id === $retirement->employee_id;
    }

    public function complete(User $user, Retirement $retirement): bool
    {
        if ($retirement->status !== 'diproses') {
            return false;
        }

        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        return $user->employee_id === $retirement->employee_id;
    }
}
