<?php

declare(strict_types=1);

namespace App\Services\Organization;

use App\Models\Employee;
use App\Models\EmployeeAssignment;
use App\Services\Audit\ActivityLogService;
use Illuminate\Support\Facades\DB;

class EmployeeAssignmentService
{
    public function __construct(
        protected ActivityLogService $activityLog
    ) {}

    /**
     * Assign an employee to a position/unit.
     */
    public function assign(
        Employee $employee,
        int $unitId,
        string $positionTitle,
        string $assignmentType,
        \DateTimeInterface $startDate,
        ?string $skNumber = null
    ): EmployeeAssignment {
        return DB::transaction(function () use ($employee, $unitId, $positionTitle, $assignmentType, $startDate, $skNumber) {
            // Pessimistic lock on parent employee row
            DB::table('employees')->where('id', $employee->id)->lockForUpdate()->first();

            // Ponytaill: Deactivate previous active assignments if needed?
            // The requirement doesn't explicitly say only one active, but usually true for main assignments.
            // For now, just create a new one.

            $assignment = EmployeeAssignment::create([
                'employee_id' => $employee->id,
                'unit_id' => $unitId,
                'position_title' => $positionTitle,
                'assignment_type' => $assignmentType,
                'start_date' => $startDate,
                'sk_number' => $skNumber,
                'is_active' => true,
            ]);

            $this->activityLog->log(
                null,
                'ASSIGN_POSITION',
                'EmployeeAssignment',
                $assignment->id,
                null,
                'aktif',
                null,
                ['unit_id' => $unitId, 'position' => $positionTitle]
            );

            return $assignment;
        });
    }

    /**
     * Terminate an assignment.
     */
    public function terminate(EmployeeAssignment $assignment, \DateTimeInterface $endDate): void
    {
        DB::transaction(function () use ($assignment, $endDate) {
            $assignment->update([
                'end_date' => $endDate,
                'is_active' => false,
            ]);

            $this->activityLog->log(
                null,
                'TERMINATE_ASSIGNMENT',
                'EmployeeAssignment',
                $assignment->id,
                'aktif',
                'nonaktif'
            );
        });
    }
}
