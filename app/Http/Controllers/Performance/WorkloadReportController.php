<?php

declare(strict_types=1);

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Performance\WorkloadReportRequest;
use App\Models\WorkloadReport;
use App\Services\Performance\WorkloadReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkloadReportController extends Controller
{
    public function __construct(
        protected WorkloadReportService $reportService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $this->authorize('viewAny', WorkloadReport::class);

        $query = WorkloadReport::with('employee')
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s));

        if (! $request->user()->hasRole('Admin BSDM') && ! $request->user()->hasRole('Kepala Biro') && ! $request->user()->hasRole('Kepala Bagian') && ! $request->user()->hasRole('Staff HR')) {
            $query->where('employee_id', $employee->id);
        }

        return response()->json(['success' => true, 'data' => $query->paginate()]);
    }

    public function store(WorkloadReportRequest $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $this->authorize('create', WorkloadReport::class);

        $report = $this->reportService->createReport(
            $employee,
            [
                'teaching' => $request->validated('teaching_credit'),
                'research' => $request->validated('research_credit'),
                'service' => $request->validated('service_credit'),
                'support' => $request->validated('support_credit'),
            ],
            $request->validated('academic_year'),
            $request->validated('semester'),
            (int) $request->validated('validator_id')
        );

        return response()->json(['success' => true, 'data' => $report], 201);
    }

    public function submit(WorkloadReport $report): JsonResponse
    {
        $this->authorize('update', $report);

        $report = $this->reportService->submitReport($report);

        return response()->json(['success' => true, 'data' => $report]);
    }

    public function validateReport(WorkloadReport $report): JsonResponse
    {
        $this->authorize('validate', $report);

        $report = $this->reportService->validateReport($report);

        return response()->json(['success' => true, 'data' => $report]);
    }

    public function revise(Request $request, WorkloadReport $report): JsonResponse
    {
        $request->validate([
            'teaching' => 'required|numeric',
            'research' => 'required|numeric',
            'service' => 'required|numeric',
            'support' => 'required|numeric',
            'revision_reason' => 'required|string',
        ]);

        $revised = $this->reportService->reviseReport(
            $report,
            $request->only(['teaching', 'research', 'service', 'support']),
            $request->input('revision_reason')
        );

        return response()->json(['success' => true, 'data' => $revised]);
    }
}
