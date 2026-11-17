<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AcademicRankHistory;
use App\Models\User;
use App\Services\Audit\ActivityLogService;
use Illuminate\Auth\Access\HandlesAuthorization;

class AcademicRankPolicy
{
    use HandlesAuthorization;

    public function __construct(protected ActivityLogService $activityLog) {}

    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin BSDM') ||
               $user->hasRole('Kepala Biro') ||
               $user->hasRole('Kepala Bagian') ||
               $user->hasRole('Dosen');
    }

    public function view(User $user, AcademicRankHistory $rank): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Kepala Bagian') && $user->employee_id) {
            return $this->isInUnitScope($user, $rank);
        }

        // Employee can view own rank history
        return $user->employee_id === $rank->employee_id;
    }

    public function create(User $user): bool
    {
        // Dosen can submit own academic rank proposal
        return $user->hasRole('Dosen') && $user->employee?->category === 'dosen';
    }

    public function update(User $user, AcademicRankHistory $rank): bool
    {
        return false; // Submissions are immutable after creation
    }

    public function approve(User $user, AcademicRankHistory $rank): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Kepala Bagian') && $user->employee_id) {
            return $this->isInUnitScope($user, $rank) &&
                   $user->employee_id !== $rank->employee_id; // No self-approval
        }

        return false;
    }

    public function reject(User $user, AcademicRankHistory $rank): bool
    {
        return $this->approve($user, $rank); // Same authorization as approve
    }

    public function delete(User $user, AcademicRankHistory $rank): bool
    {
        return false; // Rank history is never deleted
    }

    private function isInUnitScope(User $user, AcademicRankHistory $rank): bool
    {
        if (! $user->employee_id) {
            return false;
        }

        $userUnitId = $user->employee?->unit_id;
        if (! $userUnitId) {
            return false;
        }

        return $rank->employee?->unit_id === $userUnitId ||
               $rank->employee?->department?->parent_id === $userUnitId;
    }
}
