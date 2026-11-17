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
        string $category,
        \DateTimeInterface $effectiveFrom,
        ?int $supervisorId = null,
        ?\DateTimeInterface $effectiveUntil = null
    ): EmployeeAssignment {
        return DB::transaction(function () use ($employee, $unitId, $category, $effectiveFrom, $supervisorId, $effectiveUntil) {
            // Pessimistic lock on parent employee row
            DB::table('employees')->where('id', $employee->id)->lockForUpdate()->first();

            $assignment = EmployeeAssignment::create([
                'employee_id' => $employee->id,
                'unit_id' => $unitId,
                'supervisor_id' => $supervisorId,
                'category' => $category,
                'effective_from' => $effectiveFrom,
                'effective_until' => $effectiveUntil,
            ]);

            $this->activityLog->log(
                null,
                'ASSIGN_POSITION',
                'EmployeeAssignment',
                $assignment->id,
                null,
                'aktif',
                null,
                ['unit_id' => $unitId, 'category' => $category]
            );

            return $assignment;
        });
    }

    /**
     * Terminate an assignment.
     */
    public function terminate(EmployeeAssignment $assignment, \DateTimeInterface $effectiveUntil): void
    {
        DB::transaction(function () use ($assignment, $effectiveUntil) {
            $assignment->update([
                'effective_until' => $effectiveUntil,
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
