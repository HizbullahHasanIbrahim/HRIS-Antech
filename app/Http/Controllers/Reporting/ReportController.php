<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reporting;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Employee;
use App\Models\Vacancy;
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

    public function searchEmployee(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Employee::class);

        $query = $request->input('q', '');
        $unitId = $request->input('unit_id');

        if (empty($query)) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $results = Employee::search($query)
            ->when($unitId, fn ($q) => $q->where('unit_id', $unitId))
            ->with('unit:id,name')
            ->limit(20)
            ->get(['id', 'nip', 'nidn', 'full_name', 'category', 'unit_id', 'employment_status', 'email', 'phone']);

        return response()->json(['success' => true, 'data' => $results]);
    }

    public function searchApplication(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Application::class);

        $query = $request->input('q', '');
        $unitId = $request->input('unit_id');

        if (empty($query)) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $results = Application::whereHas('applicant', function ($q) use ($query) {
            $q->where('full_name', 'LIKE', "%{$query}%")
                ->orWhere('email', 'LIKE', "%{$query}%");
        })
            ->when($unitId, function ($q) use ($unitId) {
                $q->whereHas('vacancy', fn ($v) => $v->where('unit_id', $unitId));
            })
            ->with(['applicant:id,full_name,email', 'vacancy:id,title'])
            ->limit(20)
            ->get(['id', 'applicant_id', 'vacancy_id', 'status', 'applied_at']);

        return response()->json(['success' => true, 'data' => $results]);
    }

    public function searchVacancy(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Vacancy::class);

        $query = $request->input('q', '');
        $unitId = $request->input('unit_id');

        if (empty($query)) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $results = Vacancy::where('title', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->when($unitId, fn ($q) => $q->where('unit_id', $unitId))
            ->with('unit:id,name')
            ->limit(20)
            ->get(['id', 'unit_id', 'title', 'category', 'status', 'open_date', 'close_date']);

        return response()->json(['success' => true, 'data' => $results]);
    }
}
