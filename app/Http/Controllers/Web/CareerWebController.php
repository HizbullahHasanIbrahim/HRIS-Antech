<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AcademicRankHistory;
use App\Models\Certification;
use App\Models\StaffRankHistory;
use App\Models\StudyLeave;
use App\Models\Training;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CareerWebController extends Controller
{
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

        return Inertia::render('Career/Index', [
            'academicRanks' => $academicQuery->paginate(15)->withQueryString(),
            'staffRanks' => $staffQuery->paginate(15)->withQueryString(),
            'isAdminOrHead' => $isAdminOrHead,
            'scopeEmployeeId' => $scopeEmployeeId,
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
            'isAdminOrHead' => $isAdminOrHead,
        ]);
    }
}
