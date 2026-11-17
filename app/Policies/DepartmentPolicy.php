<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Department;
use App\Models\User;
use App\Services\Audit\ActivityLogService;
use Illuminate\Auth\Access\HandlesAuthorization;

class DepartmentPolicy
{
    use HandlesAuthorization;

    public function __construct(protected ActivityLogService $activityLog) {}

    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro');
    }

    public function view(User $user, Department $department): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro');
    }

    public function update(User $user, Department $department): bool
    {
        return $user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro');
    }

    public function delete(User $user, Department $department): bool
    {
        return $user->hasRole('Admin BSDM');
    }
}
