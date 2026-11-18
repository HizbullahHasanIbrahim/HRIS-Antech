<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AcademicRankHistory;
use App\Models\AllowanceRequest;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\PerformanceTarget;
use App\Models\StaffRankHistory;
use App\Models\WorkloadReport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportWebController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('view-reports');

        $user = $request->user();
        $employee = $user?->employee;

        // RULE-15: Effective Unit Scoping
        $isAdminOrHead = $user?->hasRole('Admin BSDM') || $user?->hasRole('admin-bsdm')
            || $user?->hasRole('Kepala Biro') || $user?->hasRole('kepala-biro');
        $userUnitId = $employee?->unit_id;

        $requestedUnitId = $request->input('unit_id');

        // Scope logic:
        // 1. If Admin/Head: Allow requested unit, default to all (null)
        // 2. Else: Force to user's own unit
        $scopedUnitId = $isAdminOrHead ? $requestedUnitId : $userUnitId;

        $periode = $request->input('periode', now()->format('Y-m'));

        // KPI Metrics
        $totalEmployees = Employee::when($scopedUnitId, fn ($q) => $q->where('unit_id', $scopedUnitId))
            ->where('employment_status', 'aktif')
            ->count();

        $totalDosen = Employee::when($scopedUnitId, fn ($q) => $q->where('unit_id', $scopedUnitId))
            ->where('category', 'dosen')
            ->where('employment_status', 'aktif')
            ->count();

        $totalTendik = Employee::when($scopedUnitId, fn ($q) => $q->where('unit_id', $scopedUnitId))
            ->where('category', 'tendik')
            ->where('employment_status', 'aktif')
            ->count();

        $pensiunThisYear = Employee::when($scopedUnitId, fn ($q) => $q->where('unit_id', $scopedUnitId))
            ->where('employment_status', 'pensiun')
            ->whereYear('end_date', now()->year)
            ->count();

        // Leave & Overtime monthly figures
        $startOfMonth = now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = now()->endOfMonth()->format('Y-m-d');

        $leaveRequestsThisMonth = LeaveRequest::when($scopedUnitId, fn ($q) => $q->whereHas('employee', fn ($e) => $e->where('unit_id', $scopedUnitId)))
            ->whereBetween('start_date', [$startOfMonth, $endOfMonth])
            ->count();

        $leaveDaysApproved = LeaveRequest::when($scopedUnitId, fn ($q) => $q->whereHas('employee', fn ($e) => $e->where('unit_id', $scopedUnitId)))
            ->where('status', 'disetujui')
            ->whereBetween('start_date', [$startOfMonth, $endOfMonth])
            ->sum('total_days');

        $overtimeHoursApproved = OvertimeRequest::when($scopedUnitId, fn ($q) => $q->whereHas('employee', fn ($e) => $e->where('unit_id', $scopedUnitId)))
            ->where('status', 'disetujui')
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->sum('total_hours');

        $allowanceRequestsThisMonth = AllowanceRequest::when($scopedUnitId, fn ($q) => $q->whereHas('employee', fn ($e) => $e->where('unit_id', $scopedUnitId)))
            ->whereBetween('submitted_at', [$startOfMonth, $endOfMonth])
            ->count();

        // Rank distribution
        $dosenRankDistribution = AcademicRankHistory::selectRaw('academic_rank_histories.rank_name as rank, COUNT(*) as count')
            ->join('employees', 'academic_rank_histories.employee_id', '=', 'employees.id')
            ->when($scopedUnitId, fn ($q) => $q->where('employees.unit_id', $scopedUnitId))
            ->where('employees.employment_status', 'aktif')
            ->groupBy('academic_rank_histories.rank_name')
            ->orderBy('count', 'desc')
            ->get();

        $tendikRankDistribution = StaffRankHistory::selectRaw('staff_rank_histories.rank_grade as rank, COUNT(*) as count')
            ->join('employees', 'staff_rank_histories.employee_id', '=', 'employees.id')
            ->when($scopedUnitId, fn ($q) => $q->where('employees.unit_id', $scopedUnitId))
            ->where('employees.employment_status', 'aktif')
            ->groupBy('staff_rank_histories.rank_grade')
            ->orderBy('count', 'desc')
            ->get();

        // BKD Compliance rates (current semester)
        $currentAcademicYear = now()->month >= 7 ? now()->year : now()->year - 1;
        $currentSemester = now()->month >= 7 ? 1 : 2;

        $totalDosenForBkd = Employee::when($scopedUnitId, fn ($q) => $q->where('unit_id', $scopedUnitId))
            ->where('category', 'dosen')
            ->where('employment_status', 'aktif')
            ->count();

        $bkdSubmitted = WorkloadReport::whereHas('employee', fn ($q) => $q
            ->when($scopedUnitId, fn ($q2) => $q2->where('unit_id', $scopedUnitId))
            ->where('category', 'dosen')
            ->where('employment_status', 'aktif')
        )
            ->where('academic_year', $currentAcademicYear)
            ->where('semester', $currentSemester)
            ->where('is_current', 1)
            ->count();

        $bkdFinal = WorkloadReport::whereHas('employee', fn ($q) => $q
            ->when($scopedUnitId, fn ($q2) => $q2->where('unit_id', $scopedUnitId))
            ->where('category', 'dosen')
            ->where('employment_status', 'aktif')
        )
            ->where('academic_year', $currentAcademicYear)
            ->where('semester', $currentSemester)
            ->where('status', 'final')
            ->count();

        $bkdComplianceRate = $totalDosenForBkd > 0
            ? round(($bkdSubmitted / $totalDosenForBkd) * 100, 1)
            : 0;

        $bkdFinalRate = $totalDosenForBkd > 0
            ? round(($bkdFinal / $totalDosenForBkd) * 100, 1)
            : 0;

        // SKP Compliance
        $totalEmployeesForSkp = Employee::when($scopedUnitId, fn ($q) => $q->where('unit_id', $scopedUnitId))
            ->where('employment_status', 'aktif')
            ->count();

        $skpSubmitted = PerformanceTarget::whereHas('employee', fn ($q) => $q
            ->when($scopedUnitId, fn ($q2) => $q2->where('unit_id', $scopedUnitId))
            ->where('employment_status', 'aktif')
        )
            ->where('status', '!=', 'draf')
            ->count();

        $skpApproved = PerformanceTarget::whereHas('employee', fn ($q) => $q
            ->when($scopedUnitId, fn ($q2) => $q2->where('unit_id', $scopedUnitId))
            ->where('employment_status', 'aktif')
        )
            ->where('status', 'disetujui')
            ->count();

        $skpComplianceRate = $totalEmployeesForSkp > 0
            ? round(($skpSubmitted / $totalEmployeesForSkp) * 100, 1)
            : 0;

        $skpApprovedRate = $totalEmployeesForSkp > 0
            ? round(($skpApproved / $totalEmployeesForSkp) * 100, 1)
            : 0;

        // Units for filter dropdown
        $units = Department::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'unit_type']);

        return Inertia::render('Reports/Index', [
            'kpi' => [
                'totalEmployees' => $totalEmployees,
                'totalDosen' => $totalDosen,
                'totalTendik' => $totalTendik,
                'pensiunThisYear' => $pensiunThisYear,
                'leaveRequestsThisMonth' => $leaveRequestsThisMonth,
                'leaveDaysApproved' => $leaveDaysApproved,
                'overtimeHoursApproved' => $overtimeHoursApproved,
                'allowanceRequestsThisMonth' => $allowanceRequestsThisMonth,
            ],
            'charts' => [
                'dosenRankDistribution' => $dosenRankDistribution,
                'tendikRankDistribution' => $tendikRankDistribution,
            ],
            'compliance' => [
                'bkd' => [
                    'submitted' => $bkdSubmitted,
                    'final' => $bkdFinal,
                    'total' => $totalDosenForBkd,
                    'complianceRate' => $bkdComplianceRate,
                    'finalRate' => $bkdFinalRate,
                    'period' => "{$currentAcademicYear}/{$currentSemester}",
                ],
                'skp' => [
                    'submitted' => $skpSubmitted,
                    'approved' => $skpApproved,
                    'total' => $totalEmployeesForSkp,
                    'complianceRate' => $skpComplianceRate,
                    'approvedRate' => $skpApprovedRate,
                ],
            ],
            'units' => $units,
            'filters' => [
                'unit_id' => $scopedUnitId,
                'periode' => $periode,
            ],
            'exportUrls' => [
                'sdm' => '/api/v1/reports/export/sdm-summary',
                'cuti-lembur' => '/api/v1/reports/export/cuti-lembur',
                'karir-dosen' => '/api/v1/reports/export/karir-dosen',
                'pangkat-tendik' => '/api/v1/reports/export/pangkat-tendik',
                'bkd-lkd' => '/api/v1/reports/export/bkd-lkd',
                'skp' => '/api/v1/reports/export/skp',
            ],
        ]);
    }
}
