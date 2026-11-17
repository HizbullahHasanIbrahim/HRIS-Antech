<?php

namespace App\Services\Recruitment;

use App\Enums\ApplicationStatus;
use App\Exceptions\UnauthorizedActionException;
use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class OnboardingService
{
    /**
     * Create an employee from an approved application.
     */
    public function onboardEmployee(Application $application, string $nip, ?string $nidn = null): Employee
    {
        if ($application->status !== ApplicationStatus::APPROVED->value) {
            throw new UnauthorizedActionException('Only approved applications can be onboarded.');
        }

        if (! $application->vacancy_id) {
            throw new \InvalidArgumentException('Application must be linked to a vacancy.');
        }

        $this->validateNipUnique($nip);

        $employee = DB::transaction(function () use ($application, $nip, $nidn) {
            $employee = Employee::create([
                'nip' => $nip,
                'nidn' => $nidn,
                'full_name' => $application->applicant->full_name,
                'email' => $application->applicant->email,
                'phone' => $application->applicant->phone,
                'unit_id' => $application->vacancy->unit_id,
                'employment_status' => 'aktif',
                'join_date' => now(),
                'application_id' => $application->id,
            ]);

            $application->update(['status' => ApplicationStatus::EMPLOYEE_CREATED->value]);

            $this->logActivity(null, 'CREATE_EMPLOYEE', 'Employee', $employee->id, null, 'aktif');

            return $employee;
        });

        return $employee;
    }

    protected function validateNipUnique(string $nip): void
    {
        if (Employee::where('nip', $nip)->exists()) {
            throw new \InvalidArgumentException('NIP already exists.');
        }
    }

    protected function logActivity(?int $actorId, string $action, string $entityType, int $entityId, ?string $oldStatus, ?string $newStatus): void
    {
        ActivityLog::create([
            'actor_id' => $actorId ?? 1,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'created_at' => now(),
        ]);
    }
}
