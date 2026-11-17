<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\Retirement;

class DashboardStatsService
{
    public function summary(): array
    {
        return [
            'sdm' => $this->sdmStats(),
            'cuti' => $this->leaveStats(),
            'lembur' => $this->overtimeStats(),
            'pensiun' => $this->retirementStats(),
        ];
    }

    private function sdmStats(): array
    {
        return [
            'total_pegawai' => Employee::count(),
            'aktif' => Employee::where('employment_status', 'aktif')->count(),
            'pensiun' => Employee::where('employment_status', 'pensiun')->count(),
            'by_category' => Employee::selectRaw('category, COUNT(*) as total')
                ->groupBy('category')
                ->pluck('total', 'category')
                ->toArray(),
            'by_unit' => Employee::selectRaw('departments.name as unit_name, COUNT(employees.id) as total')
                ->leftJoin('departments', 'employees.unit_id', '=', 'departments.id')
                ->groupBy('departments.name')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get()
                ->toArray(),
        ];
    }

    private function leaveStats(): array
    {
        return [
            'pending' => LeaveRequest::where('status', 'diajukan')->count(),
            'approved_this_month' => LeaveRequest::where('status', 'disetujui')
                ->whereMonth('decided_at', now()->month)
                ->whereYear('decided_at', now()->year)
                ->count(),
            'total_this_month' => LeaveRequest::whereMonth('submitted_at', now()->month)
                ->whereYear('submitted_at', now()->year)
                ->count(),
        ];
    }

    private function overtimeStats(): array
    {
        return [
            'pending' => OvertimeRequest::where('status', 'diajukan')->count(),
            'approved_this_month' => OvertimeRequest::where('status', 'disetujui')
                ->whereMonth('decided_at', now()->month)
                ->whereYear('decided_at', now()->year)
                ->count(),
            'total_hours_this_month' => OvertimeRequest::where('status', 'disetujui')
                ->whereMonth('work_date', now()->month)
                ->whereYear('work_date', now()->year)
                ->sum('total_hours'),
        ];
    }

    private function retirementStats(): array
    {
        $upcoming = Retirement::where('status', 'diusulkan')
            ->where('planned_date', '<=', now()->addMonths(12))
            ->with('employee:id,full_name,nip')
            ->orderBy('planned_date')
            ->get()
            ->map(fn ($r) => [
                'employee' => $r->employee?->full_name,
                'nip' => $r->employee?->nip,
                'planned_date' => $r->planned_date?->toDateString(),
            ])
            ->toArray();

        return [
            'pending_proposals' => Retirement::where('status', 'diusulkan')->count(),
            'upcoming_12_months' => count($upcoming),
            'upcoming_list' => $upcoming,
        ];
    }
}
