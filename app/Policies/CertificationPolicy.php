<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Certification;
use App\Models\User;
use App\Services\Audit\ActivityLogService;
use Illuminate\Auth\Access\HandlesAuthorization;

class CertificationPolicy
{
    use HandlesAuthorization;

    public function __construct(protected ActivityLogService $activityLog) {}

    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin BSDM') ||
               $user->hasRole('Kepala Biro') ||
               $user->hasRole('Kepala Bagian');
    }

    public function view(User $user, Certification $certification): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Kepala Bagian') && $user->employee_id) {
            return $this->isInUnitScope($user, $certification);
        }

        return $user->employee_id === $certification->employee_id;
    }

    public function create(User $user): bool
    {
        return $user->employee_id !== null;
    }

    public function update(User $user, Certification $certification): bool
    {
        return false; // Certifications are immutable after creation
    }

    public function delete(User $user, Certification $certification): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        return $user->employee_id === $certification->employee_id;
    }

    private function isInUnitScope(User $user, Certification $certification): bool
    {
        if (! $user->employee_id) {
            return false;
        }

        $userUnitId = $user->employee?->unit_id;
        if (! $userUnitId) {
            return false;
        }

        return $certification->employee?->unit_id === $userUnitId ||
               $certification->employee?->department?->parent_id === $userUnitId;
    }
}
