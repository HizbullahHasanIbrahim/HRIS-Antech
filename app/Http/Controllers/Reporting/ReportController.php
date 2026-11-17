<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reporting;

use App\Http\Controllers\Controller;
use App\Services\Reporting\HrisReportQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected HrisReportQueryService $reportService
    ) {}

    public function summarySdm(Request $request): JsonResponse
    {
        $this->authorize('viewAny');

        $data = $this->reportService->reportSummarySdm(
            $request->input('unit_id'),
            $request->input('periode')
        );

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function cutiLembur(Request $request): JsonResponse
    {
        $this->authorize('viewAny');

        $data = $this->reportService->reportCutLembur(
            $request->input('unit_id'),
            $request->input('periode')
        );

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function karirDosen(Request $request): JsonResponse
    {
        $this->authorize('viewAny');

        $data = $this->reportService->reportKarirDosen(
            $request->input('unit_id'),
            $request->input('periode')
        );

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function pangkatTendik(Request $request): JsonResponse
    {
        $this->authorize('viewAny');

        $data = $this->reportService->reportPangkatTendik(
            $request->input('unit_id'),
            $request->input('periode')
        );

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function bkdLkd(Request $request): JsonResponse
    {
        $this->authorize('viewAny');

        $data = $this->reportService->reportBkdLkd(
            $request->input('unit_id'),
            $request->input('periode')
        );

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function skp(Request $request): JsonResponse
    {
        $this->authorize('viewAny');

        $data = $this->reportService->reportSkp(
            $request->input('unit_id'),
            $request->input('periode')
        );

        return response()->json(['success' => true, 'data' => $data]);
    }
}
