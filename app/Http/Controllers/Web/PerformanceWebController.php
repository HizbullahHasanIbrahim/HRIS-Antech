<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PerformanceTarget;
use App\Models\WorkloadReport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PerformanceWebController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $employee = $user?->employee;

        $isAdminOrHead = $user?->hasRole('Admin BSDM') || $user?->hasRole('admin-bsdm') || $user?->hasRole('Kepala Biro') || $user?->hasRole('kepala-biro');
        $scopeEmployeeId = $isAdminOrHead ? $request->input('employee_id', $employee?->id) : $employee?->id;

        $bkdQuery = WorkloadReport::query()
            ->with(['employee.department', 'validator'])
            ->where('is_current', true)
            ->whereHas('employee', fn ($q) => $q->where('category', 'dosen'))
            ->when($request->input('search'), fn ($q, $s) => $q->whereHas('employee', fn ($eq) => $eq->where('full_name', 'like', "%{$s}%")))
            ->when(! $isAdminOrHead, fn ($q) => $q->where('employee_id', $scopeEmployeeId))
            ->orderByDesc('academic_year')
            ->orderByDesc('semester');

        $lkdQuery = WorkloadReport::query()
            ->with(['employee.department', 'validator'])
            ->where('is_current', true)
            ->whereHas('employee', fn ($q) => $q->where('category', 'dosen'))
            ->when($request->input('search'), fn ($q, $s) => $q->whereHas('employee', fn ($eq) => $eq->where('full_name', 'like', "%{$s}%")))
            ->when(! $isAdminOrHead, fn ($q) => $q->where('employee_id', $scopeEmployeeId))
            ->orderByDesc('academic_year')
            ->orderByDesc('semester');

        $skpQuery = PerformanceTarget::query()
            ->with(['employee.department', 'appraiser'])
            ->when($request->input('search'), fn ($q, $s) => $q->whereHas('employee', fn ($eq) => $eq->where('full_name', 'like', "%{$s}%")))
            ->when(! $isAdminOrHead, fn ($q) => $q->where('employee_id', $scopeEmployeeId))
            ->orderByDesc('period_start');

        return Inertia::render('Performance/Index', [
            'bkdReports' => $bkdQuery->paginate(15)->withQueryString(),
            'lkdReports' => $lkdQuery->paginate(15)->withQueryString(),
            'skpTargets' => $skpQuery->paginate(15)->withQueryString(),
            'isAdminOrHead' => $isAdminOrHead,
            'scopeEmployeeId' => $scopeEmployeeId,
        ]);
    }
}
