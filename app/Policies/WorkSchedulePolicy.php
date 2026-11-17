<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\WorkSchedule;
use App\Services\Audit\ActivityLogService;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkSchedulePolicy
{
    use HandlesAuthorization;

    public function __construct(protected ActivityLogService $activityLog) {}

    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin BSDM') ||
               $user->hasRole('Kepala Biro') ||
               $user->hasRole('Kepala Bagian') ||
               $user->hasRole('Staff HR');
    }

    public function view(User $user, WorkSchedule $schedule): bool
    {
        return true; // All authenticated users can view schedules
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro');
    }

    public function update(User $user, WorkSchedule $schedule): bool
    {
        return $user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro');
    }

    public function delete(User $user, WorkSchedule $schedule): bool
    {
        return $user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro');
    }

    public function assign(User $user, WorkSchedule $schedule): bool
    {
        return $user->hasRole('Admin BSDM') ||
               $user->hasRole('Kepala Biro') ||
               $user->hasRole('Staff HR');
    }
}
