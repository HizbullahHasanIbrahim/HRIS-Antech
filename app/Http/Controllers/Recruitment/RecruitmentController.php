<?php

declare(strict_types=1);

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Http\Requests\Recruitment\ApplicantRequest;
use App\Http\Requests\Recruitment\ApplicationRequestRequest;
use App\Http\Requests\Recruitment\SelectionStageRequest;
use App\Http\Requests\Recruitment\VacancyRequest;
use App\Models\Applicant;
use App\Models\Application;
use App\Models\SelectionStage;
use App\Models\Vacancy;
use App\Services\Recruitment\RecruitmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecruitmentController extends Controller
{
    public function __construct(
        protected RecruitmentService $recruitmentService
    ) {}

    // Vacancy endpoints
    public function indexVacancies(Request $request): JsonResponse
    {
        $query = Vacancy::with('department')
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s));

        return response()->json(['success' => true, 'data' => $query->paginate()]);
    }

    public function storeVacancy(VacancyRequest $request): JsonResponse
    {
        $this->authorize('create', Vacancy::class);

        $vacancy = $this->recruitmentService->createVacancy($request->validated(), (int) $request->user()->id);

        return response()->json(['success' => true, 'data' => $vacancy], 201);
    }

    public function showVacancy(Vacancy $vacancy): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $vacancy->load('department')]);
    }

    // Applicant endpoints
    public function indexApplicants(Request $request): JsonResponse
    {
        $query = Applicant::when($request->input('search'), function ($q, $search) {
            $q->where('full_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });

        return response()->json(['success' => true, 'data' => $query->paginate()]);
    }

    public function storeApplicant(ApplicantRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $cvPath = $request->file('cv_file')->store('cv', 'private');

        $applicant = Applicant::create(array_merge($validated, [
            'cv_path' => $cvPath,
        ]));

        return response()->json(['success' => true, 'data' => $applicant], 201);
    }

    // Application endpoints
    public function storeApplication(ApplicationRequestRequest $request): JsonResponse
    {
        $application = $this->recruitmentService->apply(
            (int) $request->validated('vacancy_id'),
            (int) $request->validated('applicant_id')
        );

        return response()->json(['success' => true, 'data' => $application], 201);
    }

    public function updateApplicationStatus(Request $request, Application $application): JsonResponse
    {
        $this->authorize('update', $application);

        $request->validate([
            'status' => ['required', 'string', 'in:diajukan,administrasi,tulis,wawancara,final,diterima,ditolak'],
            'aik_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $application->update([
            'status' => $request->input('status'),
            'aik_score' => $request->input('aik_score', $application->aik_score),
            'decided_at' => in_array($request->input('status'), ['diterima', 'ditolak']) ? now() : null,
        ]);

        return response()->json(['success' => true, 'data' => $application->refresh()]);
    }

    // Selection Stage endpoints
    public function storeSelectionStage(SelectionStageRequest $request, Application $application): JsonResponse
    {
        $this->authorize('create', SelectionStage::class);

        $validated = $request->validated();
        $validated['application_id'] = $application->id;

        $stage = SelectionStage::create($validated);

        return response()->json(['success' => true, 'data' => $stage], 201);
    }

    public function updateSelectionStageResult(Request $request, Application $application, SelectionStage $stage): JsonResponse
    {
        $this->authorize('update', $stage);

        $validated = $request->validate([
            'result' => ['required', 'string', 'in:lolos,gugur'],
            'score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $stage->update($validated);

        return response()->json(['success' => true, 'data' => $stage->refresh()]);
    }
}
