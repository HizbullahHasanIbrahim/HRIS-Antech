<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Vacancy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

        // ponytail: applicants table has no unit_id column, so no unit scoping here.
        // Add scope when applicants gain a unit_id FK.
        $applicants = Applicant::query()->get(['id', 'full_name', 'email', 'phone', 'highest_education']);

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
            'departments' => Department::where('is_active', true)->select(['id', 'name'])->orderBy('name')->get(),
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
                    'name' => $u->employee?->full_name ?? $u->username,
                    'email' => $u->employee?->email ?? '-',
                    'role' => $u->roles->pluck('name')->join(', ') ?: 'Staff',
                    'department' => $u->employee?->department?->name ?? '-',
                    'status' => $u->employee?->employment_status ?? ($u->is_active ? 'aktif' : 'tidak_aktif'),
                ])->values(),
            ]),
            'departments' => Department::where('is_active', true)->select(['id', 'name'])->orderBy('name')->get(),
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
            ->whereHas('employee', fn ($q) => $q->where('join_date', '>=', now()->subDays(30)))
            ->when(! $isAdminOrHead && $employee, fn ($q) => $q->where('employee_id', $employee->id))
            ->latest('created_at');

        $users = $usersQuery->paginate(15)->withQueryString();

        return Inertia::render('Recruitment/Onboarding', [
            'users' => array_merge($users->toArray(), [
                'data' => $users->getCollection()->map(fn ($u) => [
                    'id' => $u->id,
                    'name' => $u->employee?->full_name ?? '-',
                    'email' => $u->email,
                    'status' => $u->employee?->employment_status ?? 'tidak_aktif',
                    'statusDate' => $u->employee?->join_date?->format('d/m/Y'),
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
        $user = $request->user();

        if (! $user || ! $user->hasRole('Admin BSDM') && ! $user->hasRole('Kepala Biro')) {
            abort(403, 'Hanya Admin BSDM dan Kepala Biro yang berhak membuat lowongan kerja.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|in:dosen,tenaga_kependidikan',
            'unit_id' => 'required|exists:departments,id',
            'quota' => 'required|integer|min:1',
            'open_date' => 'required|date',
            'close_date' => 'required|date|after_or_equal:open_date',
        ]);

        Vacancy::create([
            'unit_id' => $request->unit_id,
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'min_qualification' => 'S1 / S2 / S3 Sesuai Bidang',
            'quota' => $request->quota,
            'open_date' => $request->open_date,
            'close_date' => $request->close_date,
            'status' => 'diajukan',
            'created_by' => $user->id,
        ]);

        return back()->with('success', 'Lowongan kerja berhasil dibuat.');
    }

    /**
     * Store a new user (web action).
     */
    public function storeUser(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('Admin BSDM')) {
            abort(403, 'Hanya Admin BSDM yang berhak menambahkan pengguna baru.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|string|email|max:150',
            'role' => 'required|string|in:dosen,tendik,admin-bsdm',
            'unit_id' => 'required|exists:departments,id',
            'password' => 'required|string|min:8',
        ]);

        $roleName = match ($validated['role']) {
            'dosen' => 'Dosen',
            'tendik' => 'Tendik',
            'admin-bsdm' => 'Admin BSDM',
        };

        $role = Role::firstOrCreate(['name' => $roleName]);

        DB::transaction(function () use ($validated, $role) {
            $employee = Employee::create([
                'nip' => 'UAD'.now()->format('Ymd').str_pad((string) (Employee::count() + 1), 4, '0', STR_PAD_LEFT),
                'full_name' => $validated['name'],
                'email' => $validated['email'],
                'category' => $validated['role'] === 'admin-bsdm' ? 'tendik' : $validated['role'],
                'gender' => 'L',
                'birth_place' => 'Yogyakarta',
                'birth_date' => '1995-01-01',
                'highest_education' => 'S1',
                'unit_id' => $validated['unit_id'],
                'employment_status' => 'aktif',
                'join_date' => now(),
            ]);

            $user = User::create([
                'employee_id' => $employee->id,
                'username' => Str::slug($validated['name'], '_'),
                'password_hash' => Hash::make($validated['password']),
                'is_active' => true,
            ]);

            UserRole::create([
                'user_id' => $user->id,
                'role_id' => $role->id,
                'unit_id' => $validated['unit_id'],
            ]);
        });

        return back()->with('success', 'Pengguna baru berhasil dibuat.');
    }
}
