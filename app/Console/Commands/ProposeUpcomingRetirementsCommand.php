<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\Retirement;
use App\Services\Audit\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProposeUpcomingRetirementsCommand extends Command
{
    protected $signature = 'hris:propose-retirements {--dry-run : Run without creating proposals}';

    protected $description = 'Create retirement proposals for employees approaching retirement age (RULE-10)';

    public function __construct(protected ActivityLogService $activityLog)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        // Retirement age policy: 58 years old for Indonesian civil servants (PNS)
        $retirementAge = config('hris.retirement_age', 58);
        // Propose 1 year before retirement
        $proposalLeadMonths = config('hris.retirement_proposal_lead_months', 12);

        $cutoffDate = Carbon::today()->addMonths($proposalLeadMonths);

        $this->info("Checking employees turning {$retirementAge} by {$cutoffDate->toDateString()}...");

        $employees = Employee::where('employment_status', 'aktif')
            ->whereHas('user', fn ($q) => $q->where('is_active', true))
            ->whereRaw('DATE_ADD(birth_date, INTERVAL ? YEAR) <= ?', [$retirementAge, $cutoffDate->toDateString()])
            ->get();

        $this->info("Found {$employees->count()} employee(s) approaching retirement.");

        if ($dryRun) {
            foreach ($employees as $employee) {
                $retirementDate = Carbon::parse($employee->birth_date)->addYears($retirementAge);
                $this->line("  - {$employee->nip} | {$employee->full_name} | Retirement: {$retirementDate->toDateString()}");
            }

            return self::SUCCESS;
        }

        $created = 0;
        $skipped = 0;

        foreach ($employees as $employee) {
            // Check if already has active proposal
            $existing = Retirement::where('employee_id', $employee->id)
                ->where('status', 'diusulkan')
                ->first();

            if ($existing) {
                $this->warn("  Skipped {$employee->nip}: already has active proposal (ID: {$existing->id})");
                $skipped++;

                continue;
            }

            $retirementDate = Carbon::parse($employee->birth_date)->addYears($retirementAge);
            $proposedDate = Carbon::today();
            $effectiveDate = $retirementDate;

            // Use transaction with pessimistic lock
            DB::transaction(function () use ($employee, $proposedDate, $effectiveDate, &$created) {
                DB::table('employees')->where('id', $employee->id)->lockForUpdate()->first();

                // Double-check after lock
                if (Retirement::where('employee_id', $employee->id)
                    ->where('status', 'diusulkan')
                    ->exists()) {
                    return;
                }

                $retirement = Retirement::create([
                    'employee_id' => $employee->id,
                    'retirement_age' => $retirementAge,
                    'planned_date' => $effectiveDate,
                    'proposed_at' => $proposedDate,
                    'status' => 'diusulkan',
                    'notes' => "Usulan otomatis oleh sistem (usia {$retirementAge} thn tercapai {$effectiveDate->format('d F Y')})",
                    'version' => 1,
                ]);

                $this->activityLog->log(
                    1, // System user
                    'PROPOSE_RETIREMENT_AUTO',
                    'Retirement',
                    $retirement->id,
                    null,
                    'diusulkan',
                    'Usulan otomatis oleh scheduled job',
                    [
                        'retirement_age' => $retirementAge,
                        'planned_date' => $effectiveDate->toDateString(),
                        'proposed_at' => $proposedDate->toDateTimeString(),
                    ]
                );

                $created++;
            });
        }

        $this->info("Created: {$created} | Skipped: {$skipped}");

        return self::SUCCESS;
    }
}
