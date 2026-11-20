<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AcademicRankHistory;
use App\Models\Certification;
use App\Models\Employee;
use App\Models\StaffRankHistory;
use App\Models\StudyLeave;
use App\Models\Training;
use App\Services\Audit\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CareerWebController extends Controller
{
    public function __construct(
        protected ActivityLogService $activityLog
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $employee = $user?->employee;

        $isAdminOrHead = $user?->hasRole('Admin BSDM') || $user?->hasRole('admin-bsdm') || $user?->hasRole('Kepala Biro') || $user?->hasRole('kepala-biro');
        $scopeEmployeeId = $isAdminOrHead ? $request->input('employee_id', $employee?->id) : $employee?->id;

        $academicQuery = AcademicRankHistory::query()
            ->with(['employee.department', 'approver'])
            ->when($request->input('search'), fn ($q, $s) => $q->whereHas('employee', fn ($eq) => $eq->where('full_name', 'like', "%{$s}%")))
            ->when(! $isAdminOrHead, fn ($q) => $q->where('employee_id', $scopeEmployeeId))
            ->orderByDesc('effective_date');

        $staffQuery = StaffRankHistory::query()
            ->with(['employee.department', 'approver'])
            ->when($request->input('search'), fn ($q, $s) => $q->whereHas('employee', fn ($eq) => $eq->where('full_name', 'like', "%{$s}%")))
            ->when(! $isAdminOrHead, fn ($q) => $q->where('employee_id', $scopeEmployeeId))
            ->orderByDesc('effective_date');

        $employees = Employee::where('employment_status', 'aktif')
            ->select(['id', 'full_name', 'nip'])
            ->when(! $isAdminOrHead && $employee, fn ($q) => $q->where('unit_id', $employee->unit_id))
            ->orderBy('full_name')
            ->get();

        return Inertia::render('Career/Index', [
            'academicRanks' => $academicQuery->paginate(15)->withQueryString(),
            'staffRanks' => $staffQuery->paginate(15)->withQueryString(),
            'isAdminOrHead' => $isAdminOrHead,
            'scopeEmployeeId' => $scopeEmployeeId,
            'employees' => $employees,
        ]);
    }

    public function studyLeave(Request $request): Response
    {
        $user = $request->user();
        $employee = $user?->employee;
        $isAdminOrHead = $user?->hasRole('Admin BSDM') || $user?->hasRole('Kepala Biro');

        $query = StudyLeave::with('employee.department')
            ->when(! $isAdminOrHead && $employee, fn ($q) => $q->where('employee_id', $employee->id))
            ->latest('start_date');

        $studies = $query->paginate(15)->withQueryString();

        $employees = Employee::where('employment_status', 'aktif')
            ->select(['id', 'full_name', 'nip'])
            ->when(! $isAdminOrHead && $employee, fn ($q) => $q->where('unit_id', $employee->unit_id))
            ->orderBy('full_name')
            ->get();

        return Inertia::render('Career/StudyLeave', [
            'studies' => array_merge($studies->toArray(), [
                'data' => $studies->getCollection()->map(fn ($s) => [
                    'id' => $s->id,
                    'candidate' => $s->employee?->full_name ?? '-',
                    'program' => ($s->degree_level ?? 'S3').' '.($s->study_program ?? '-').' ('.($s->university ?? '-').')',
                    'status' => $s->status,
                    'reason' => $s->mentoring_notes,
                    'scholarship' => $s->scholarship_name,
                ])->values(),
            ]),
            'employees' => $employees,
            'isAdminOrHead' => $isAdminOrHead,
        ]);
    }

    public function certifications(Request $request): Response
    {
        $user = $request->user();
        $employee = $user?->employee;
        $isAdminOrHead = $user?->hasRole('Admin BSDM') || $user?->hasRole('Kepala Biro');

        $query = Certification::with('employee.department')
            ->when(! $isAdminOrHead && $employee, fn ($q) => $q->where('employee_id', $employee->id))
            ->latest('submitted_date');

        $certs = $query->paginate(15)->withQueryString();

        $employees = Employee::where('employment_status', 'aktif')
            ->select(['id', 'full_name', 'nip'])
            ->when(! $isAdminOrHead && $employee, fn ($q) => $q->where('unit_id', $employee->unit_id))
            ->orderBy('full_name')
            ->get();

        return Inertia::render('Career/Certifications', [
            'certifications' => array_merge($certs->toArray(), [
                'data' => $certs->getCollection()->map(fn ($c) => [
                    'id' => $c->id,
                    'employee' => $c->employee?->full_name ?? '-',
                    'name' => $c->cert_type,
                    'number' => $c->cert_number,
                    'year' => $c->submitted_date?->format('Y') ?? '-',
                    'status' => $c->status,
                ])->values(),
            ]),
            'employees' => $employees,
            'isAdminOrHead' => $isAdminOrHead,
        ]);
    }

    public function training(Request $request): Response
    {
        $user = $request->user();
        $employee = $user?->employee;
        $isAdminOrHead = $user?->hasRole('Admin BSDM') || $user?->hasRole('Kepala Biro');

        $query = Training::with('employee.department')
            ->when(! $isAdminOrHead && $employee, fn ($q) => $q->where('employee_id', $employee->id))
            ->latest('start_date');

        $trainings = $query->paginate(15)->withQueryString();

        $employees = Employee::where('employment_status', 'aktif')
            ->select(['id', 'full_name', 'nip'])
            ->when(! $isAdminOrHead && $employee, fn ($q) => $q->where('unit_id', $employee->unit_id))
            ->orderBy('full_name')
            ->get();

        return Inertia::render('Career/Training', [
            'trainings' => array_merge($trainings->toArray(), [
                'data' => $trainings->getCollection()->map(fn ($t) => [
                    'id' => $t->id,
                    'employee' => $t->employee?->full_name ?? '-',
                    'name' => $t->title,
                    'provider' => $t->organizer,
                    'date' => $t->start_date?->format('d/m/Y') ?? '-',
                    'duration' => ($t->duration_hours ?? 0).' Jam',
                    'status' => $t->status,
                ])->values(),
            ]),
            'employees' => $employees,
            'isAdminOrHead' => $isAdminOrHead,
        ]);
    }

    public function storeCertification(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'cert_type' => 'required|string|max:50',
            'cert_number' => 'required|string|max:50|unique:certifications,cert_number',
            'submitted_date' => 'required|date',
            'cert_level' => 'required|string|max:12',
        ]);

        DB::transaction(function () use ($validated) {
            DB::table('employees')->where('id', $validated['employee_id'])->lockForUpdate()->first();

            $certification = Certification::create([
                'employee_id' => $validated['employee_id'],
                'cert_type' => $validated['cert_type'],
                'cert_number' => $validated['cert_number'],
                'submitted_date' => $validated['submitted_date'],
                'status' => 'diajukan',
            ]);

            $this->activityLog->log(
                null,
                'CREATE_CERTIFICATION',
                'Certification',
                $certification->id,
                null,
                'diajukan',
            );
        });

        return back()->with('success', 'Sertifikasi berhasil dibuat.');
    }

    public function storeStudyLeave(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'degree_level' => 'required|string|max:5',
            'university' => 'required|string|max:150',
            'study_program' => 'required|string|max:150',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'scholarship_name' => 'required|string|max:150',
        ]);

        DB::transaction(function () use ($validated) {
            DB::table('employees')->where('id', $validated['employee_id'])->lockForUpdate()->first();

            $studyLeave = StudyLeave::create([
                'employee_id' => $validated['employee_id'],
                'degree_level' => $validated['degree_level'],
                'university' => $validated['university'],
                'study_program' => $validated['study_program'],
                'start_date' => $validated['start_date'],
                'expected_end_date' => $validated['end_date'],
                'scholarship_name' => $validated['scholarship_name'],
                'status' => 'direncanakan',
            ]);

            $this->activityLog->log(
                null,
                'CREATE_STUDY_LEAVE',
                'StudyLeave',
                $studyLeave->id,
                null,
                'direncanakan',
            );
        });

        return back()->with('success', 'Pengajuan studi lanjut berhasil dibuat.');
    }

    public function storeTraining(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'title' => 'required|string|max:200',
            'training_type' => 'required|string|max:15',
            'organizer' => 'required|string|max:150',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'duration_hours' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($validated) {
            DB::table('employees')->where('id', $validated['employee_id'])->lockForUpdate()->first();

            $training = Training::create([
                'employee_id' => $validated['employee_id'],
                'title' => $validated['title'],
                'training_type' => $validated['training_type'],
                'organizer' => $validated['organizer'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'duration_hours' => $validated['duration_hours'],
                'status' => 'dijadwalkan',
            ]);

            $this->activityLog->log(
                null,
                'CREATE_TRAINING',
                'Training',
                $training->id,
                null,
                'dijadwalkan',
            );
        });

        return back()->with('success', 'Pelatihan berhasil dibuat.');
    }
}
