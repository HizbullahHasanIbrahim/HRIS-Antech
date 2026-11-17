<?php

declare(strict_types=1);

namespace App\Services\Performance;

use App\Models\Employee;
use App\Models\WorkloadReport;
use App\Services\Audit\ActivityLogService;
use Illuminate\Support\Facades\DB;

class WorkloadReportService
{
    public function __construct(
        protected ActivityLogService $activityLog
    ) {}

    /**
     * Create a new workload report.
     */
    public function createReport(
        Employee $employee,
        array $credits,
        string $academicYear,
        string $semester,
        int $validatorId
    ): WorkloadReport {
        return DB::transaction(function () use ($employee, $credits, $academicYear, $semester, $validatorId) {
            // Pessimistic lock on parent employee row (SDD-5.8)
            DB::table('employees')->where('id', $employee->id)->lockForUpdate()->first();

            // Check for existing report for this period
            if (WorkloadReport::where('employee_id', $employee->id)
                ->where('academic_year', $academicYear)
                ->where('semester', $semester)
                ->where('is_current', true)
                ->exists()) {
                throw new \RuntimeException('Laporan beban kerja sudah ada untuk periode ini.');
            }

            $report = WorkloadReport::create([
                'employee_id' => $employee->id,
                'academic_year' => $academicYear,
                'semester' => $semester,
                'teaching_credit' => $credits['teaching'],
                'research_credit' => $credits['research'],
                'service_credit' => $credits['service'],
                'support_credit' => $credits['support'],
                'status' => 'diajukan',
                'validator_id' => $validatorId,
                'version' => 1,
                'is_current' => true,
            ]);

            $this->activityLog->log(
                null,
                'CREATE_WORKLOAD_REPORT',
                'WorkloadReport',
                $report->id,
                null,
                'diajukan',
                null,
                ['year' => $academicYear, 'semester' => $semester, 'total_credit' => $report->total_credit]
            );

            return $report;
        });
    }

    /**
     * Submit a workload report for validation.
     */
    public function submitReport(WorkloadReport $report): WorkloadReport
    {
        return DB::transaction(function () use ($report) {
            $report->update([
                'status' => 'divalidasi',
                'version' => $report->version + 1,
            ]);

            $this->activityLog->log(
                $report->validator_id,
                'SUBMIT_WORKLOAD_REPORT',
                'WorkloadReport',
                $report->id,
                'diajukan',
                'divalidasi',
                null,
                ['validator_id' => $report->validator_id]
            );

            return $report->fresh();
        });
    }

    /**
     * Validate a workload report.
     */
    public function validateReport(WorkloadReport $report): WorkloadReport
    {
        return DB::transaction(function () use ($report) {
            // Pessimistic lock on parent employee row
            DB::table('employees')->where('id', $report->employee_id)->lockForUpdate()->first();

            // Optimistic locking for version
            $updated = WorkloadReport::where('id', $report->id)
                ->where('version', $report->version)
                ->update([
                    'status' => 'final',
                    'validated_at' => now(),
                    'version' => $report->version + 1,
                ]);

            if ($updated === 0) {
                throw new \RuntimeException('Laporan beban kerja telah dimodifikasi oleh pengguna lain.');
            }

            $report->refresh();

            $this->activityLog->log(
                $report->validator_id,
                'VALIDATE_WORKLOAD_REPORT',
                'WorkloadReport',
                $report->id,
                'divalidasi',
                'final',
                null,
                ['validated_at' => $report->validated_at]
            );

            return $report;
        });
    }

    /**
     * Revise a final workload report.
     */
    public function reviseReport(WorkloadReport $report, array $credits, string $revisionReason): WorkloadReport
    {
        return DB::transaction(function () use ($report, $credits, $revisionReason) {
            // Pessimistic lock on parent employee row
            DB::table('employees')->where('id', $report->employee_id)->lockForUpdate()->first();

            // Deactivate current report
            $report->update([
                'is_current' => false,
                'version' => $report->version + 1,
            ]);

            // Create revised report
            $revisedReport = WorkloadReport::create([
                'employee_id' => $report->employee_id,
                'academic_year' => $report->academic_year,
                'semester' => $report->semester,
                'teaching_credit' => $credits['teaching'],
                'research_credit' => $credits['research'],
                'service_credit' => $credits['service'],
                'support_credit' => $credits['support'],
                'status' => 'diajukan',
                'validator_id' => $report->validator_id,
                'version' => $report->version + 1,
                'is_current' => true,
                'revision_reason' => $revisionReason,
            ]);

            $this->activityLog->log(
                $report->validator_id,
                'REVISE_WORKLOAD_REPORT',
                'WorkloadReport',
                $revisedReport->id,
                'final',
                'diajukan',
                $revisionReason,
                ['revision' => true, 'old_report' => $report->id]
            );

            return $revisedReport;
        });
    }
}
