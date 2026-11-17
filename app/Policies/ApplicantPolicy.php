<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Applicant;
use App\Models\User;
use App\Services\Audit\ActivityLogService;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApplicantPolicy
{
    use HandlesAuthorization;

    public function __construct(protected ActivityLogService $activityLog) {}

    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin BSDM') ||
               $user->hasRole('Kepala Biro') ||
               $user->hasRole('Staff Rekrutmen');
    }

    public function view(User $user, Applicant $applicant): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro') || $user->hasRole('Staff Rekrutmen')) {
            return true;
        }

        // Applicant can view own profile
        return $applicant->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        // Public registration
        return true;
    }

    public function update(User $user, Applicant $applicant): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Staff Rekrutmen')) {
            return true;
        }

        // Applicant can update own profile
        return $applicant->user_id === $user->id;
    }

    public function delete(User $user, Applicant $applicant): bool
    {
        return $user->hasRole('Admin BSDM');
    }
}
