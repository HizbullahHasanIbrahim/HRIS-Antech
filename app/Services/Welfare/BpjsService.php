<?php

declare(strict_types=1);

namespace App\Services\Welfare;

use App\Models\BpjsMembership;
use App\Models\Employee;
use App\Services\Audit\ActivityLogService;
use Illuminate\Support\Facades\DB;

class BpjsService
{
    public function __construct(
        protected ActivityLogService $activityLog
    ) {}

    /**
     * Create or update a BPJS membership.
     */
    public function registerMembership(
        Employee $employee,
        string $bpjsType,
        string $membershipNumber,
        \DateTimeInterface $registeredDate
    ): BpjsMembership {
        return DB::transaction(function () use ($employee, $bpjsType, $membershipNumber, $registeredDate) {
            // Pessimistic lock on parent employee row (SDD-5.10)
            DB::table('employees')->where('id', $employee->id)->lockForUpdate()->first();

            // Check for existing active membership of the same type
            $existing = BpjsMembership::where('employee_id', $employee->id)
                ->where('bpjs_type', $bpjsType)
                ->where('status', 'aktif')
                ->first();

            if ($existing) {
                $existing->update([
                    'status' => 'nonaktif',
                    'archived_at' => now(),
                ]);
            }

            $membership = BpjsMembership::create([
                'employee_id' => $employee->id,
                'bpjs_type' => $bpjsType,
                'membership_number' => $membershipNumber,
                'registered_date' => $registeredDate,
                'status' => 'aktif',
                'archived_at' => null,
            ]);

            $this->activityLog->log(
                $employee->user?->id,
                'REGISTER_BPJS_MEMBERSHIP',
                'BpjsMembership',
                $membership->id,
                null,
                'aktif',
                null,
                ['employee_id' => $employee->id, 'membership_number' => $membershipNumber, 'bpjs_type' => $bpjsType]
            );

            return $membership;
        });
    }

    /**
     * Get active BPJS membership for an employee.
     */
    public function getActiveMembership(Employee $employee): ?BpjsMembership
    {
        return BpjsMembership::where('employee_id', $employee->id)
            ->where('status', 'aktif')
            ->first();
    }

    /**
     * Cancel active BPJS membership.
     */
    public function cancelMembership(BpjsMembership $membership): void
    {
        DB::transaction(function () use ($membership) {
            $membership->update([
                'status' => 'nonaktif',
                'archived_at' => now(),
            ]);

            $this->activityLog->log(
                $membership->employee?->user?->id,
                'CANCEL_BPJS_MEMBERSHIP',
                'BpjsMembership',
                $membership->id,
                'aktif',
                'nonaktif'
            );
        });
    }
}
