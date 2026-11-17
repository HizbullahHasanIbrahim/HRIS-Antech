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

    public function store(WorkloadReportRequest $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $report = $this->reportService->createReport(
            $employee,
            $request->only(['teaching', 'research', 'service', 'support']),
            $request->validated('academic_year'),
            $request->validated('semester'),
            (int) $request->input('validator_id')
        );

        return response()->json(['success' => true, 'data' => $report], 201);
    }

    public function submit(WorkloadReport $report): JsonResponse
    {
        $report = $this->reportService->submitReport($report);

        return response()->json(['success' => true, 'data' => $report]);
    }

    public function validateReport(WorkloadReport $report): JsonResponse
    {
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
