<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\StaffRankHistory;
use App\Models\User;
use App\Services\Audit\ActivityLogService;
use Illuminate\Auth\Access\HandlesAuthorization;

class StaffRankPolicy
{
    use HandlesAuthorization;

    public function __construct(protected ActivityLogService $activityLog) {}

    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin BSDM') ||
               $user->hasRole('Kepala Biro') ||
               $user->hasRole('Kepala Bagian') ||
               $user->hasRole('Tendik');
    }

    public function view(User $user, StaffRankHistory $rank): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Kepala Bagian') && $user->employee_id) {
            return $this->isInUnitScope($user, $rank);
        }

        return $user->employee_id === $rank->employee_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Tendik') && $user->employee?->category === 'tendik';
    }

    public function update(User $user, StaffRankHistory $rank): bool
    {
        return false; // Submissions are immutable after creation
    }

    public function approve(User $user, StaffRankHistory $rank): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Kepala Bagian') && $user->employee_id) {
            return $this->isInUnitScope($user, $rank) &&
                   $user->employee_id !== $rank->employee_id;
        }

        return false;
    }

    public function reject(User $user, StaffRankHistory $rank): bool
    {
        return $this->approve($user, $rank);
    }

    public function delete(User $user, StaffRankHistory $rank): bool
    {
        return false;
    }

    private function isInUnitScope(User $user, StaffRankHistory $rank): bool
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
