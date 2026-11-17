<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Vacancy;
use App\Services\Audit\ActivityLogService;
use Illuminate\Auth\Access\HandlesAuthorization;

class VacancyPolicy
{
    use HandlesAuthorization;

    public function __construct(protected ActivityLogService $activityLog) {}

    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin BSDM') ||
               $user->hasRole('Kepala Biro') ||
               $user->hasRole('Staff Rekrutmen');
    }

    public function view(User $user, Vacancy $vacancy): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Staff Rekrutmen') && $user->employee_id) {
            return $this->isInUnitScope($user, $vacancy);
        }

        return true; // Public view for applicants
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Admin BSDM') ||
               $user->hasRole('Kepala Biro') ||
               $user->hasRole('Staff Rekrutmen');
    }

    public function update(User $user, Vacancy $vacancy): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Staff Rekrutmen') && $user->employee_id) {
            return $this->isInUnitScope($user, $vacancy) && $vacancy->status === 'draf';
        }

        return false;
    }

    public function delete(User $user, Vacancy $vacancy): bool
    {
        return ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) &&
               $vacancy->status === 'draf';
    }

    private function isInUnitScope(User $user, Vacancy $vacancy): bool
    {
        if (! $user->employee_id) {
            return false;
        }

        $userUnitId = $user->employee?->unit_id;
        if (! $userUnitId) {
            return false;
        }

        return $vacancy->unit_id === $userUnitId;
    }
}
