<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Applicant;
use App\Models\Application;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ArchiveExpiredRecruitmentDataCommand extends Command
{
    protected $signature = 'hris:archive-rejected-applicants {--dry-run : Run without archiving} {--force : Force archive even if has other applications}';

    protected $description = 'Archive rejected applicants older than 2 years (Q-09)';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        // Archive threshold: 2 years after decided_at
        $threshold = now()->subYears(2);

        $this->info("Archiving rejected applications decided before {$threshold->toDateString()}...");

        $query = Application::where('status', 'ditolak')
            ->whereNotNull('decided_at')
            ->where('decided_at', '<', $threshold)
            ->whereNull('deleted_at');

        if (! $force) {
            // Only archive if applicant has NO other accepted applications
            $query->whereNotExists(function ($q) {
                $q->selectRaw(1)
                    ->from('applications as a2')
                    ->whereColumn('a2.applicant_id', 'applications.applicant_id')
                    ->where('a2.status', 'diterima')
                    ->whereNull('a2.deleted_at');
            });
        }

        $applications = $query->get();

        $this->info("Found {$applications->count()} application(s) to archive.");

        if ($dryRun) {
            foreach ($applications as $app) {
                $this->line("  - App ID: {$app->id} | Applicant: {$app->applicant_id} | Decided: {$app->decided_at}");
            }

            return self::SUCCESS;
        }

        $archived = 0;
        $errors = 0;

        foreach ($applications as $application) {
            try {
                DB::transaction(function () use ($application, &$archived) {
                    // Soft delete the application
                    $application->delete();

                    // Check if applicant has any other non-deleted applications
                    $otherApps = Application::where('applicant_id', $application->applicant_id)
                        ->whereNull('deleted_at')
                        ->count();

                    if ($otherApps === 0) {
                        // Archive applicant (soft delete)
                        Applicant::where('id', $application->applicant_id)->delete();

                        // Delete CV file if exists
                        $applicant = Applicant::withTrashed()->find($application->applicant_id);
                        if ($applicant && $applicant->cv_path) {
                            Storage::disk('private')->delete($applicant->cv_path);
                        }
                    }

                    $archived++;
                });
            } catch (\Throwable $e) {
                $this->error("  Failed to archive application {$application->id}: {$e->getMessage()}");
                $errors++;
            }
        }

        $this->info("Archived: {$archived} | Errors: {$errors}");

        return self::SUCCESS;
    }
}
