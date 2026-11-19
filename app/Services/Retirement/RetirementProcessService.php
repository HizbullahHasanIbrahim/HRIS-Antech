<?php

declare(strict_types=1);

namespace App\Services\Retirement;

use App\Exceptions\BusinessRuleViolationException;
use App\Exceptions\ConcurrencyConflictException;
use App\Models\AllowanceRequest;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\PerformanceTarget;
use App\Models\Retirement;
use App\Models\Training;
use App\Services\Audit\ActivityLogService;
use Illuminate\Support\Facades\DB;

class RetirementProcessService
{
    public function __construct(
        protected ActivityLogService $activityLog,
    ) {}

    /**
     * Propose a retirement manually (P-16, RULE-10).
     */
    public function proposeRetirement(
        Employee $employee,
        int $retirementAge,
        \DateTimeInterface $plannedDate,
        string $notes,
        int $processedBy,
    ): Retirement {
        return DB::transaction(function () use ($employee, $retirementAge, $plannedDate, $notes, $processedBy) {
            DB::table('employees')->where('id', $employee->id)->lockForUpdate()->first();

            if (Retirement::where('employee_id', $employee->id)
                ->where('status', 'diajukan')
                ->exists()) {
                throw new BusinessRuleViolationException('Karyawan sudah memiliki usulan pensiun aktif.');
            }

            $retirement = Retirement::create([
                'employee_id' => $employee->id,
                'retirement_age' => $retirementAge,
                'planned_date' => $plannedDate,
                'proposed_at' => now(),
                'status' => 'diajukan',
                'processed_by' => $processedBy,
                'notes' => $notes,
                'version' => 1,
            ]);

            $this->activityLog->log(
                $processedBy,
                'PROPOSE_RETIREMENT',
                'Retirement',
                $retirement->id,
                null,
                'diajukan',
                $notes,
                ['retirement_age' => $retirementAge, 'planned_date' => $plannedDate],
            );

            return $retirement;
        });
    }

    /**
     * Process a retirement proposal and schedule pre-retirement training (EX-09).
     */
    public function processRetirement(Retirement $retirement, int $processedBy, ?string $notes = null): Retirement
    {
        if ($retirement->status !== 'diajukan') {
            throw new BusinessRuleViolationException('Hanya usulan pensiun yang diajukan yang dapat diproses.');
        }

        return DB::transaction(function () use ($retirement, $processedBy, $notes) {
            DB::table('employees')->where('id', $retirement->employee_id)->lockForUpdate()->first();

            if (! $this->areAllRequestsResolved($retirement->employee_id)) {
                throw new BusinessRuleViolationException('Semua pengajuan aktif karyawan harus diselesaikan sebelum memproses pensiun.');
            }

            $updated = Retirement::where('id', $retirement->id)
                ->where('version', $retirement->version)
                ->update([
                    'status' => 'diproses',
                    'processed_by' => $processedBy,
                    'notes' => $notes ?? $retirement->notes,
                    'version' => $retirement->version + 1,
                ]);

            if ($updated === 0) {
                throw new ConcurrencyConflictException('Usulan pensiun telah dimodifikasi oleh pengguna lain.');
            }

            $this->schedulePreRetirementTraining($retirement);

            $this->activityLog->log(
                $processedBy,
                'PROCESS_RETIREMENT',
                'Retirement',
                $retirement->id,
                'diajukan',
                'diproses',
                $notes,
                ['processed_by' => $processedBy],
            );

            return $retirement->refresh();
        });
    }

    /**
     * Complete retirement and set employee status to pensiun.
     */
    public function completeRetirement(Retirement $retirement, string $skNumber, \DateTimeInterface $skDate): Retirement
    {
        if ($retirement->status !== 'diproses') {
            throw new BusinessRuleViolationException('Hanya usulan pensiun yang diproses yang dapat diselesaikan.');
        }

        return DB::transaction(function () use ($retirement, $skNumber, $skDate) {
            DB::table('employees')->where('id', $retirement->employee_id)->lockForUpdate()->first();

            $updated = Retirement::where('id', $retirement->id)
                ->where('version', $retirement->version)
                ->update([
                    'status' => 'selesai',
                    'sk_number' => $skNumber,
                    'sk_date' => $skDate,
                    'completed_at' => now(),
                    'version' => $retirement->version + 1,
                ]);

            if ($updated === 0) {
                throw new ConcurrencyConflictException('Usulan pensiun telah dimodifikasi oleh pengguna lain.');
            }

            $retirement->employee->update([
                'employment_status' => 'pensiun',
                'end_date' => $retirement->planned_date,
            ]);

            $this->activityLog->log(
                $retirement->processed_by,
                'COMPLETE_RETIREMENT',
                'Retirement',
                $retirement->id,
                'diproses',
                'selesai',
                null,
                ['sk_number' => $skNumber, 'sk_date' => $skDate],
            );

            return $retirement->refresh();
        });
    }

    /**
     * Cancel a retirement proposal (RULE-10, RULE-11).
     */
    public function cancelRetirement(Retirement $retirement, ?int $actorId = null, ?string $reason = null): Retirement
    {
        if (! in_array($retirement->status, ['diajukan', 'diproses'], true)) {
            throw new BusinessRuleViolationException('Status pensiun tidak dapat dibatalkan.');
        }

        if ($reason !== null && trim($reason) === '') {
            throw new BusinessRuleViolationException('Alasan pembatalan wajib diisi.');
        }

        return DB::transaction(function () use ($retirement, $actorId, $reason) {
            DB::table('employees')->where('id', $retirement->employee_id)->lockForUpdate()->first();

            $updated = Retirement::where('id', $retirement->id)
                ->where('version', $retirement->version)
                ->update([
                    'status' => 'dibatalkan',
                    'notes' => $reason,
                    'version' => $retirement->version + 1,
                ]);

            if ($updated === 0) {
                throw new ConcurrencyConflictException('Usulan pensiun telah dimodifikasi oleh pengguna lain.');
            }

            $this->activityLog->log(
                $actorId,
                'CANCEL_RETIREMENT',
                'Retirement',
                $retirement->id,
                $retirement->status,
                'dibatalkan',
                $reason,
            );

            return $retirement->refresh();
        });
    }

    /**
     * Verify all pending requests for employee are resolved (EX-09).
     */
    protected function areAllRequestsResolved(int $employeeId): bool
    {
        $pending =
            LeaveRequest::where('employee_id', $employeeId)->where('status', 'diajukan')->count()
            + OvertimeRequest::where('employee_id', $employeeId)->where('status', 'diajukan')->count()
            + AllowanceRequest::where('employee_id', $employeeId)->where('status', 'diajukan')->count()
            + PerformanceTarget::where('employee_id', $employeeId)->where('status', 'diajukan')->count();

        return $pending === 0;
    }

    /**
     * Schedule pre-retirement training (P-16 step 3).
     */
    protected function schedulePreRetirementTraining(Retirement $retirement): void
    {
        Training::create([
            'employee_id' => $retirement->employee_id,
            'retirement_id' => $retirement->id,
            'title' => 'Pelatihan Pra-Pensiun',
            'training_type' => 'pra_pensiun',
            'organizer' => 'BSDM UAD',
            'start_date' => now()->addMonth()->toDateString(),
            'end_date' => now()->addMonths(2)->toDateString(),
            'duration_hours' => 40,
            'status' => 'dijadwalkan',
            'certificate_path' => null,
        ]);
    }
}
