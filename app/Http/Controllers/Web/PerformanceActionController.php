<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PerformanceTarget;
use App\Models\WorkloadReport;
use App\Services\Performance\SkpEvaluationService;
use App\Services\Performance\WorkloadReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerformanceActionController extends Controller
{
    public function __construct(
        protected WorkloadReportService $workloadReportService,
        protected SkpEvaluationService $skpEvaluationService,
    ) {}

    public function validateWorkload(WorkloadReport $report): RedirectResponse
    {
        $this->authorize('validate', $report);

        $this->workloadReportService->validateReport($report);

        return back()->with('success', 'Laporan beban kerja berhasil divalidasi.');
    }

    public function reviseWorkload(Request $request, WorkloadReport $report): RedirectResponse
    {
        $validated = $request->validate([
            'teaching' => ['required', 'numeric'],
            'research' => ['required', 'numeric'],
            'service' => ['required', 'numeric'],
            'support' => ['required', 'numeric'],
            'revision_reason' => ['required', 'string', 'min:3'],
        ]);

        $this->workloadReportService->reviseReport(
            $report,
            [
                'teaching' => (float) $validated['teaching'],
                'research' => (float) $validated['research'],
                'service' => (float) $validated['service'],
                'support' => (float) $validated['support'],
            ],
            $validated['revision_reason']
        );

        return back()->with('success', 'Laporan beban kerja berhasil direvisi.');
    }

    public function evaluateSkp(Request $request, PerformanceTarget $target): RedirectResponse
    {
        $this->authorize('evaluate', $target);

        $evaluator = $request->user()?->employee;
        if (! $evaluator) {
            abort(403, 'User has no employee profile.');
        }

        $this->skpEvaluationService->evaluateTarget($target, $evaluator);

        return back()->with('success', 'Sasaran kinerja pegawai berhasil dinilai.');
    }

    public function rejectSkp(Request $request, PerformanceTarget $target): RedirectResponse
    {
        $this->authorize('reject', $target);

        $validated = $request->validate([
            'decision_note' => ['required', 'string', 'min:3'],
        ]);

        $evaluator = $request->user()?->employee;
        if (! $evaluator) {
            abort(403, 'User has no employee profile.');
        }

        $this->skpEvaluationService->rejectTarget($target, $evaluator, $validated['decision_note']);

        return back()->with('success', 'Sasaran kinerja pegawai ditolak.');
    }

    public function cancelWorkload(WorkloadReport $report): RedirectResponse
    {
        // For workload reports, "cancel" means setting status to 'ditolak' (rejected)
        // This maintains consistency with other request types while working within service constraints
        $updated = DB::transaction(function () use ($report) {
            // Pessimistic lock on parent employee row
            DB::table('employees')->where('id', $report->employee_id)->lockForUpdate()->first();

            // Optimistic locking for version
            $updated = WorkloadReport::where('id', $report->id)
                ->where('version', $report->version)
                ->update([
                    'status' => 'ditolak',
                    'version' => $report->version + 1,
                ]);

            if ($updated === 0) {
                throw new \RuntimeException('Laporan beban kerja telah dimodifikasi oleh pengguna lain.');
            }

            return $updated;
        });

        return back()->with('success', 'Laporan beban kerja berhasil dibatalkan.');
    }
}
