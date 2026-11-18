<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AcademicRankHistory;
use App\Models\StaffRankHistory;
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
}
