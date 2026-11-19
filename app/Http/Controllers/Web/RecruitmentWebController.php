<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecruitmentWebController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $employee = $user?->employee;

        $isAdminOrHead = $user?->hasRole('Admin BSDM') || $user?->hasRole('Kepala Biro');

        $query = Vacancy::with('department')
            ->when(! $isAdminOrHead && $employee, fn ($q) => $q->where('unit_id', $employee->unit_id))
            ->where('status', 'diajukan')
            ->latest();

        $vacancies = $query->paginate(15)->withQueryString();

        $applicantsQuery = Applicant::query();
        if (! $isAdminOrHead && $employee) {
            $applicantsQuery->where(function ($q) use ($employee) {
                $q->where('unit_id', $employee->unit_id)
                    ->orWhereNull('unit_id');
            });
        }
        $applicants = $applicantsQuery->get(['id', 'full_name', 'email', 'nip']);

        return Inertia::render('Recruitment/Index', [
            'vacancies' => array_merge($vacancies->toArray(), [
                'data' => $vacancies->getCollection()->map(fn ($v) => [
                    'id' => $v->id,
                    'title' => $v->title,
                    'description' => $v->description,
                    'status' => $v->status,
                    'department' => $v->department?->name ?? '-',
                    'type' => $v->category ?? '-',
                    'openDate' => $v->open_date?->format('d/m/Y'),
                    'deadline' => $v->close_date?->format('d/m/Y'),
                    'applications' => $v->applications()
                        ->where('status', 'diterima')
                        ->count(),
                ])->values(),
            ]),
            'applicants' => $applicants,
            'isAdminOrHead' => $isAdminOrHead,
            'scopeEmployeeId' => $employee?->id ?? null,
        ]);
    }

    /**
     * Get users for the Users page (web action).
     */
    public function users(Request $request): Response
    {
        $user = $request->user();
        $employee = $user?->employee;

        $isAdminOrHead = $user?->hasRole('Admin BSDM') || $user?->hasRole('Kepala Biro');

        $usersQuery = User::with('employee.department', 'roles')
            ->when(! $isAdminOrHead && $employee, fn ($q) => $q->where('employee_id', $employee->id))
            ->latest();

        $users = $usersQuery->paginate(15)->withQueryString();

        return Inertia::render('Recruitment/Users', [
            'users' => array_merge($users->toArray(), [
                'data' => $users->getCollection()->map(fn ($u) => [
                    'id' => $u->id,
                    'name' => $u->employee?->full_name ?? '-',
                    'email' => $u->email,
                    'role' => $u->roles->pluck('name')->join(', ') ?? '-',
                    'department' => $u->employee?->department?->name ?? '-',
                    'status' => $u->employee?->employment_status ?? 'tidak_aktif',
                ])->values(),
            ]),
            'isAdminOrHead' => $isAdminOrHead,
        ]);
    }

    /**
     * Get onboarding users for the Onboarding page (web action).
     */
    public function onboarding(Request $request): Response
    {
        $user = $request->user();
        $employee = $user?->employee;

        $isAdminOrHead = $user?->hasRole('Admin BSDM') || $user?->hasRole('Kepala Biro');

        $usersQuery = User::with('employee')
            ->whereHas('employee', fn ($q) => $q->where('start_date', '>=', now()->subDays(30)))
            ->when(! $isAdminOrHead && $employee, fn ($q) => $q->where('employee_id', $employee->id))
            ->latest('employee.start_date');

        $users = $usersQuery->paginate(15)->withQueryString();

        return Inertia::render('Recruitment/Onboarding', [
            'users' => array_merge($users->toArray(), [
                'data' => $users->getCollection()->map(fn ($u) => [
                    'id' => $u->id,
                    'name' => $u->employee?->full_name ?? '-',
                    'email' => $u->email,
                    'status' => $u->employee?->employment_status ?? 'tidak_aktif',
                    'statusDate' => $u->employee?->start_date?->format('d/m/Y'),
                ])->values(),
            ]),
            'isAdminOrHead' => $isAdminOrHead,
        ]);
    }

    /**
     * Store a new vacancy (web action).
     */
    public function storeVacancy(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|in:penegatan,tenaga_kontrak',
            'unit_id' => 'required|exists:departments,id',
            'quota' => 'required|integer|min:1',
            'open_date' => 'required|date',
            'close_date' => 'required|date|after_or_equal:open_date',
        ]);

        $employee = $request->user()->employee;

        if (! $employee) {
            return back()->withErrors(['message' => 'User has no employee profile.']);
        }

        Vacancy::create([
            'unit_id' => $request->unit_id,
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'quota' => $request->quota,
            'open_date' => $request->open_date,
            'close_date' => $request->close_date,
            'status' => 'diajukan',
            'created_by' => $employee->id,
        ]);

        return back()->with('success', 'Lowongan kerja berhasil dibuat.');
    }
}
