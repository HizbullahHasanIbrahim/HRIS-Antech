<?php

declare(strict_types=1);

namespace App\Http\Controllers\Career;

use App\Http\Controllers\Controller;
use App\Http\Requests\Career\AcademicRankRequest;
use App\Http\Requests\Career\CertificationRequest;
use App\Http\Requests\Career\StaffRankRequest;
use App\Http\Requests\Career\StudyLeaveRequest;
use App\Http\Requests\Career\TrainingRequest;
use App\Models\AcademicRankHistory;
use App\Models\Certification;
use App\Models\StaffRankHistory;
use App\Models\StudyLeave;
use App\Models\Training;
use App\Services\Career\AcademicRankService;
use App\Services\Career\CertificationService;
use App\Services\Career\StaffRankService;
use App\Services\Career\StudyLeaveService;
use App\Services\Career\TrainingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CareerController extends Controller
{
    public function __construct(
        protected AcademicRankService $academicRankService,
        protected StaffRankService $staffRankService,
        protected StudyLeaveService $studyLeaveService,
        protected CertificationService $certificationService,
        protected TrainingService $trainingService,
    ) {}

    // Academic Rank endpoints
    public function indexAcademicRanks(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $this->authorize('viewAny', AcademicRankHistory::class);

        $query = AcademicRankHistory::where('employee_id', $employee->id)
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s));

        return response()->json(['success' => true, 'data' => $query->paginate()]);
    }

    public function storeAcademicRank(AcademicRankRequest $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $this->authorize('create', AcademicRankHistory::class);

        $rank = $this->academicRankService->createRankHistory(
            $employee,
            $request->validated('rank_name'),
            (float) $request->validated('credit_points'),
            (bool) $request->validated('is_accelerated'),
            (int) $request->validated('approver_id')
        );

        return response()->json(['success' => true, 'data' => $rank], 201);
    }

    public function approveAcademicRank(Request $request, AcademicRankHistory $rank): JsonResponse
    {
        $this->authorize('approve', $rank);

        $approver = $request->user()->employee;
        if (! $approver) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $rank = $this->academicRankService->approveRankHistory($rank, $approver);

        return response()->json(['success' => true, 'data' => $rank]);
    }

    public function rejectAcademicRank(Request $request, AcademicRankHistory $rank): JsonResponse
    {
        $this->authorize('reject', $rank);

        $request->validate(['decision_note' => ['required', 'string']]);

        $approver = $request->user()->employee;
        if (! $approver) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $rank = $this->academicRankService->rejectRankHistory($rank, $approver, $request->input('decision_note'));

        return response()->json(['success' => true, 'data' => $rank]);
    }

    // Staff Rank endpoints
    public function indexStaffRanks(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $this->authorize('viewAny', StaffRankHistory::class);

        $query = StaffRankHistory::where('employee_id', $employee->id)
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s));

        return response()->json(['success' => true, 'data' => $query->paginate()]);
    }

    public function storeStaffRank(StaffRankRequest $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $this->authorize('create', StaffRankHistory::class);

        $rank = $this->staffRankService->createRankHistory(
            $employee,
            $request->validated('rank_level'),
            $request->validated('status'),
            (int) $request->validated('approver_id')
        );

        return response()->json(['success' => true, 'data' => $rank], 201);
    }

    public function approveStaffRank(Request $request, StaffRankHistory $rank): JsonResponse
    {
        $this->authorize('approve', $rank);

        $approver = $request->user()->employee;
        if (! $approver) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $rank = $this->staffRankService->approveRankHistory($rank, $approver);

        return response()->json(['success' => true, 'data' => $rank]);
    }

    public function rejectStaffRank(Request $request, StaffRankHistory $rank): JsonResponse
    {
        $this->authorize('reject', $rank);

        $request->validate(['decision_note' => ['required', 'string']]);

        $approver = $request->user()->employee;
        if (! $approver) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $rank = $this->staffRankService->rejectRankHistory($rank, $approver, $request->input('decision_note'));

        return response()->json(['success' => true, 'data' => $rank]);
    }

    // Study Leave endpoints
    public function indexStudyLeaves(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $this->authorize('viewAny', StudyLeave::class);

        $query = StudyLeave::where('employee_id', $employee->id)
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s));

        return response()->json(['success' => true, 'data' => $query->paginate()]);
    }

    public function storeStudyLeave(StudyLeaveRequest $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $this->authorize('create', StudyLeave::class);

        $studyLeave = $this->studyLeaveService->createStudyLeave(
            $employee,
            $request->validated('study_program'),
            $request->validated('institution'),
            new \DateTimeImmutable($request->validated('start_date')),
            new \DateTimeImmutable($request->validated('end_date')),
            $request->validated('funding_source'),
            $request->input('scholarship_name'),
            (int) $request->validated('supervisor_id')
        );

        return response()->json(['success' => true, 'data' => $studyLeave], 201);
    }

    public function approveStudyLeave(Request $request, StudyLeave $studyLeave): JsonResponse
    {
        $this->authorize('approve', $studyLeave);

        $approver = $request->user()->employee;
        if (! $approver) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $studyLeave = $this->studyLeaveService->approveStudyLeave($studyLeave, $approver);

        return response()->json(['success' => true, 'data' => $studyLeave]);
    }

    public function rejectStudyLeave(Request $request, StudyLeave $studyLeave): JsonResponse
    {
        $this->authorize('reject', $studyLeave);

        $request->validate(['decision_note' => ['required', 'string']]);

        $approver = $request->user()->employee;
        if (! $approver) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $studyLeave = $this->studyLeaveService->rejectStudyLeave($studyLeave, $approver, $request->input('decision_note'));

        return response()->json(['success' => true, 'data' => $studyLeave]);
    }

    // Certification endpoints
    public function indexCertifications(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $this->authorize('viewAny', Certification::class);

        $query = Certification::where('employee_id', $employee->id);

        return response()->json(['success' => true, 'data' => $query->paginate()]);
    }

    public function storeCertification(CertificationRequest $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $this->authorize('create', Certification::class);

        $certificatePath = null;
        if ($request->hasFile('certificate_file')) {
            $certificatePath = $request->file('certificate_file')->store('certificates', 'private');
        }

        $certification = $this->certificationService->createCertification(
            $employee,
            $request->validated('certification_name'),
            new \DateTimeImmutable($request->validated('certification_date')),
            $request->validated('certification_level'),
            $request->validated('certification_number'),
            $certificatePath
        );

        return response()->json(['success' => true, 'data' => $certification], 201);
    }

    public function destroyCertification(Certification $certification): JsonResponse
    {
        $this->authorize('delete', $certification);

        $this->certificationService->deleteCertification($certification);

        return response()->json(['success' => true, 'message' => 'Certification deleted.']);
    }

    // Training endpoints
    public function indexTrainings(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $this->authorize('viewAny', Training::class);

        $query = Training::where('employee_id', $employee->id)
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s));

        return response()->json(['success' => true, 'data' => $query->paginate()]);
    }

    public function storeTraining(TrainingRequest $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $this->authorize('create', Training::class);

        $training = $this->trainingService->createTraining(
            $employee,
            $request->validated('title'),
            $request->validated('training_type'),
            $request->validated('organizer'),
            new \DateTimeImmutable($request->validated('start_date')),
            new \DateTimeImmutable($request->validated('end_date')),
            (int) $request->validated('duration_hours'),
            $request->input('retirement_id')
        );

        return response()->json(['success' => true, 'data' => $training], 201);
    }

    public function completeTraining(Request $request, Training $training): JsonResponse
    {
        $this->authorize('complete', $training);

        $request->validate([
            'certificate_number' => ['nullable', 'string', 'max:100'],
            'certificate_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $certificatePath = null;
        if ($request->hasFile('certificate_file')) {
            $certificatePath = $request->file('certificate_file')->store('certificates', 'private');
        }

        $training = $this->trainingService->completeTraining(
            $training,
            $request->input('certificate_number'),
            $certificatePath
        );

        return response()->json(['success' => true, 'data' => $training]);
    }

    public function cancelTraining(Request $request, Training $training): JsonResponse
    {
        $this->authorize('cancel', $training);

        $request->validate(['reason' => ['required', 'string']]);

        $training = $this->trainingService->cancelTraining($training, $request->input('reason'));

        return response()->json(['success' => true, 'data' => $training]);
    }
}
