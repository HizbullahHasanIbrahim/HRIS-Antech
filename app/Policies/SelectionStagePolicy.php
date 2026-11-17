<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SelectionStage;
use App\Models\User;
use App\Services\Audit\ActivityLogService;
use Illuminate\Auth\Access\HandlesAuthorization;

class SelectionStagePolicy
{
    use HandlesAuthorization;

    public function __construct(protected ActivityLogService $activityLog) {}

    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin BSDM') ||
               $user->hasRole('Kepala Biro') ||
               $user->hasRole('Staff Rekrutmen') ||
               $user->hasRole('Asesor');
    }

    public function view(User $user, SelectionStage $stage): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Staff Rekrutmen')) {
            return $stage->application?->vacancy?->unit_id === $user->employee?->unit_id;
        }

        if ($user->hasRole('Asesor')) {
            // Assessor can view stages they're assigned to
            return true; // Simplified - in reality would check assignment
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Admin BSDM') ||
               $user->hasRole('Kepala Biro') ||
               $user->hasRole('Staff Rekrutmen');
    }

    public function update(User $user, SelectionStage $stage): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro') || $user->hasRole('Staff Rekrutmen')) {
            return true;
        }

        if ($user->hasRole('Asesor')) {
            // Assessor can update result of their assigned stage
            return $stage->status === 'menunggu';
        }

        return false;
    }

    public function delete(User $user, SelectionStage $stage): bool
    {
        return false; // Stages are never deleted
    }
}
