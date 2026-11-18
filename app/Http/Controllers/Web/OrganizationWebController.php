<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationWebController
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $employee = $user?->employee;

        // RULE-15: Non-admin users only see their own unit
        $isAdminOrHead = $user?->hasRole('Admin BSDM') || $user?->hasRole('admin-bsdm') || $user?->hasRole('Kepala Biro') || $user?->hasRole('kepala-biro');
        $scopeUnitId = $isAdminOrHead ? $request->input('unit_id', $employee?->unit_id) : $employee?->unit_id;

        $departmentsQuery = Department::query()
            ->where('is_active', true)
            ->withCount(['employees' => fn ($q) => $q->where('employment_status', 'aktif')])
            ->when($request->input('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($wq) use ($search) {
                    $wq->where('name', 'like', "%{$search}%");
                });
            })
            ->orderBy('name');

        if (! $isAdminOrHead) {
            $departmentsQuery->where('id', $scopeUnitId);
        }

        $departments = $departmentsQuery->paginate(15)->withQueryString();

        $employeesQuery = Employee::query()
            ->where('employment_status', 'aktif')
            ->with(['department', 'supervisor', 'unit'])
            ->when($request->input('search'), function ($q) use ($request) {
                $search = $request->input('search');

                return $q->where(function ($wq) use ($search) {
                    $wq->where('full_name', 'like', "%{$search}%")
                        ->orWhere('nip', 'like', "%{$search}%");
                });
            })
            ->when(! $isAdminOrHead, fn ($q) => $q->where('unit_id', $scopeUnitId))
            ->orderBy('full_name');

        $employees = $employeesQuery->paginate(15)->withQueryString();

        return Inertia::render('Organization/Index', [
            'departments' => $departments,
            'employees' => $employees,
            'unitFilter' => $scopeUnitId,
            'isAdminOrHead' => $isAdminOrHead,
        ]);
    }
}
