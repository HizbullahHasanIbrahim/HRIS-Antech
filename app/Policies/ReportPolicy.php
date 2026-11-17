<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Services\Audit\ActivityLogService;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportPolicy
{
    use HandlesAuthorization;

    public function __construct(protected ActivityLogService $activityLog) {}

    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin BSDM') ||
               $user->hasRole('Kepala Biro') ||
               $user->hasRole('Kepala Bagian') ||
               $user->hasRole('Staff HR') ||
               $user->hasRole('Wakil Rektor');
    }

    public function viewSdmSummary(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function viewCutiLembur(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function viewKarirDosen(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function viewPangkatTendik(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function viewBkdLkd(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function viewSkp(User $user): bool
    {
        return $this->viewAny($user);
    }
}
