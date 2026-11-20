<?php

declare(strict_types=1);

namespace App\Services\Performance;

use App\Exceptions\BusinessRuleViolationException;
use App\Exceptions\ConcurrencyConflictException;
use App\Models\Employee;
use App\Models\PerformanceTarget;
use App\Services\Audit\ActivityLogService;
use Illuminate\Support\Facades\DB;

class SkpEvaluationService
{
    public function __construct(
        protected ActivityLogService $activityLog,
    ) {}

    /**
     * Create a new SKP evaluation target (P-13).
     */
    public function createTarget(
        Employee $employee,
        string $periodLabel,
        \DateTimeInterface $periodStart,
        \DateTimeInterface $periodEnd,
        int $appraiserId,
    ): PerformanceTarget {
        if ($employee->id === $appraiserId) {
            throw new BusinessRuleViolationException('Employee cannot appraise their own SKP.');
        }

        return DB::transaction(function () use ($employee, $periodLabel, $periodStart, $periodEnd, $appraiserId) {
            DB::table('employees')->where('id', $employee->id)->lockForUpdate()->first();

            if (PerformanceTarget::where('employee_id', $employee->id)
                ->where('period_label', $periodLabel)
                ->where('status', 'disetujui')
                ->exists()) {
                throw new BusinessRuleViolationException('Target SKP sudah disetujui untuk periode ini.');
            }

            $target = PerformanceTarget::create([
                'employee_id' => $employee->id,
                'period_label' => $periodLabel,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'status' => 'diajukan',
                'appraiser_id' => $appraiserId,
                'version' => 1,
            ]);

            $this->activityLog->log(
                $employee->user?->id,
                'CREATE_PERFORMANCE_TARGET',
                'PerformanceTarget',
                $target->id,
                null,
                'diajukan',
                null,
                ['period' => $periodLabel, 'appraiser' => $appraiserId],
            );

            return $target;
        });
    }

    /**
     * Submit a SKP target for evaluation.
     */
    public function submitTarget(PerformanceTarget $target): PerformanceTarget
    {
        if ($target->status !== 'draf') {
            throw new BusinessRuleViolationException('Only draft SKP targets can be submitted.');
        }

        return DB::transaction(function () use ($target) {
            $target->update([
                'status' => 'diajukan',
                'version' => $target->version + 1,
            ]);

            $this->activityLog->log(
                $target->employee?->user?->id,
                'SUBMIT_PERFORMANCE_TARGET',
                'PerformanceTarget',
                $target->id,
                'draf',
                'diajukan',
                null,
                ['appraiser_id' => $target->appraiser_id],
            );

            return $target->refresh();
        });
    }

    /**
     * Evaluate a SKP target with final score and rating (RULE-08).
     */
    public function evaluateTarget(PerformanceTarget $target, Employee $evaluator): PerformanceTarget
    {
        if ($target->status !== 'diajukan') {
            throw new BusinessRuleViolationException('Only submitted SKP targets can be evaluated.');
        }

        return DB::transaction(function () use ($target, $evaluator) {
            DB::table('employees')->where('id', $target->employee_id)->lockForUpdate()->first();

            $finalScore = $this->calculateFinalScore($target);
            $rating = $this->calculateRating($finalScore);

            $updated = PerformanceTarget::where('id', $target->id)
                ->where('version', $target->version)
                ->update([
                    'status' => 'disetujui',
                    'approved_at' => now(),
                    'final_score' => $finalScore,
                    'rating' => $rating,
                    'version' => $target->version + 1,
                ]);

            if ($updated === 0) {
                throw new ConcurrencyConflictException('Target SKP telah dimodifikasi oleh pengguna lain.');
            }

            $this->activityLog->log(
                $evaluator->user?->id,
                'EVALUATE_PERFORMANCE_TARGET',
                'PerformanceTarget',
                $target->id,
                'diajukan',
                'disetujui',
                null,
                [
                    'final_score' => $finalScore,
                    'rating' => $rating,
                ],
            );

            return $target->refresh();
        });
    }

    /**
     * Reject a SKP target with decision note (RULE-11).
     */
    public function rejectTarget(PerformanceTarget $target, Employee $evaluator, string $decisionNote): PerformanceTarget
    {
        if ($target->status !== 'diajukan') {
            throw new BusinessRuleViolationException('Only submitted SKP targets can be rejected.');
        }

        if (trim($decisionNote) === '') {
            throw new BusinessRuleViolationException('Decision note is required for rejection.');
        }

        return DB::transaction(function () use ($target, $evaluator, $decisionNote) {
            DB::table('employees')->where('id', $target->employee_id)->lockForUpdate()->first();

            $updated = PerformanceTarget::where('id', $target->id)
                ->where('version', $target->version)
                ->update([
                    'status' => 'ditolak',
                    'approved_at' => now(),
                    'version' => $target->version + 1,
                ]);

            if ($updated === 0) {
                throw new ConcurrencyConflictException('Target SKP telah dimodifikasi oleh pengguna lain.');
            }

            $this->activityLog->log(
                $evaluator->user?->id,
                'REJECT_PERFORMANCE_TARGET',
                'PerformanceTarget',
                $target->id,
                'diajukan',
                'ditolak',
                $decisionNote,
            );

            return $target->refresh();
        });
    }

    /**
     * Calculate final score as weighted average of performance items.
     */
    protected function calculateFinalScore(PerformanceTarget $target): float
    {
        $items = $target->items;
        if ($items->isEmpty()) {
            return 0.0;
        }

        $totalScore = 0.0;
        $totalWeight = 0.0;

        foreach ($items as $item) {
            $itemScore = (float) ($item->score ?? 0);
            $itemWeight = (float) $item->weight;
            $totalScore += $itemScore * $itemWeight;
            $totalWeight += $itemWeight;
        }

        return $totalWeight > 0 ? round($totalScore / $totalWeight, 2) : 0.0;
    }

    /**
     * Map numeric score to textual rating.
     */
    protected function calculateRating(float $finalScore): string
    {
        return match (true) {
            $finalScore >= 90 => 'Sangat Baik',
            $finalScore >= 80 => 'Baik',
            $finalScore >= 70 => 'Cukup',
            $finalScore >= 60 => 'Kurang',
            default => 'Tidak Memenuhi Syarat',
        };
    }
}
