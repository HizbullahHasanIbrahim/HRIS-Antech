<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\WorkloadReport;
use App\Services\Audit\ActivityLogService;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkloadReportPolicy
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

    public function view(User $user, WorkloadReport $report): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Kepala Bagian') && $user->employee_id) {
            return $this->isInUnitScope($user, $report);
        }

        return $user->employee_id === $report->employee_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Dosen') && $user->employee?->category === 'dosen';
    }

    public function update(User $user, WorkloadReport $report): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        // Dosen can only revise if report is not yet validated
        if ($user->employee_id === $report->employee_id && in_array($report->status, ['draf', 'submitted'])) {
            return true;
        }

        return false;
    }

    public function submit(User $user, WorkloadReport $report): bool
    {
        return $user->employee_id === $report->employee_id && $report->status === 'draf';
    }

    public function validate(User $user, WorkloadReport $report): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        if ($user->hasRole('Kepala Bagian') && $user->employee_id) {
            return $this->isInUnitScope($user, $report) &&
                   $user->employee_id !== $report->employee_id;
        }

        if ($user->hasRole('Validator BKD') && $user->employee_id) {
            return true;
        }

        return false;
    }

    public function revise(User $user, WorkloadReport $report): bool
    {
        if ($user->hasRole('Admin BSDM') || $user->hasRole('Kepala Biro')) {
            return true;
        }

        return $user->employee_id === $report->employee_id && $report->status === 'final';
    }

    public function delete(User $user, WorkloadReport $report): bool
    {
        return false; // Reports are never deleted
    }

    private function isInUnitScope(User $user, WorkloadReport $report): bool
    {
        if (! $user->employee_id) {
            return false;
        }

        $userUnitId = $user->employee?->unit_id;
        if (! $userUnitId) {
            return false;
        }

        return $report->employee?->unit_id === $userUnitId ||
               $report->employee?->department?->parent_id === $userUnitId;
    }
}
