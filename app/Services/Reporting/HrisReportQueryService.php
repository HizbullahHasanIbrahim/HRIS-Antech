<?php

declare(strict_types=1);

namespace App\Services\Reporting;

use App\Models\AcademicRankHistory;
use App\Models\AllowanceRequest;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\PerformanceTarget;
use App\Models\StaffRankHistory;
use Illuminate\Support\Collection;

class HrisReportQueryService
{
    /**
     * Laporan 1: Ringkasan SDM per unit/bidang.
     */
    public function reportSummarySdm(?string $unitId = null, ?string $periode = null): Collection
    {
        $query = Employee::selectRaw('
            departments.name as unit_name,
            departments.unit_type,
            COUNT(employees.id) as total_pegawai,
            SUM(CASE WHEN employment_status = "aktif" THEN 1 ELSE 0 END) as aktif,
            SUM(CASE WHEN employment_status = "pensiun" THEN 1 ELSE 0 END) as pensiun')
            ->leftJoin('departments', 'employees.unit_id', '=', 'departments.id');

        if ($unitId) {
            $query->where('employees.unit_id', $unitId);
        }

        if ($periode) {
            $query->where('employees.join_date', '<=', $periode)
                ->where(function ($q) use ($periode) {
                    $q->whereNull('employees.end_date')
                        ->orWhere('employees.end_date', '>=', $periode);
                });
        }

        return $query->groupBy('departments.id')
            ->orderBy('departments.name')
            ->get();
    }

    /**
     * Laporan 2: Ringkasan cuti dan lembur.
     */
    public function reportCutLembur(?string $unitId = null, ?string $periode = null): Collection
    {
        $leaveCounts = LeaveRequest::selectRaw('
            employee_id,
            COUNT(*) as total_requests,
            SUM(CASE WHEN status = "disetujui" THEN total_days ELSE 0 END) as total_days_terverifikasi')
            ->when($unitId, fn ($q) => $q->whereHas('employee', fn ($e) => $e->where('unit_id', $unitId)))
            ->when($periode, fn ($q) => $q->whereBetween('start_date', [$periode.' 01', $periode.' 31']))
            ->groupBy('employee_id')
            ->get();

        $overtimeCounts = OvertimeRequest::selectRaw('
            employee_id,
            SUM(CASE WHEN status = "disetujui" THEN total_hours ELSE 0 END) as total_hours_lembur')
            ->when($unitId, fn ($q) => $q->whereHas('employee', fn ($e) => $e->where('unit_id', $unitId)))
            ->when($periode, fn ($q) => $q->whereBetween('work_date', [$periode.' 01', $periode.' 31']))
            ->groupBy('employee_id')
            ->get();

        $allowanceCounts = AllowanceRequest::selectRaw('
            employee_id,
            COUNT(*) as total_tunjangan_requests,
            SUM(CASE WHEN status = "selesai" THEN 1 ELSE 0 END) as total_disetujui')
            ->when($unitId, fn ($q) => $q->whereHas('employee', fn ($e) => $e->where('unit_id', $unitId)))
            ->when($periode, fn ($q) => $q->whereBetween('submitted_at', [$periode.' 01', $periode.' 31']))
            ->groupBy('employee_id')
            ->get();

        $merged = $leaveCounts->merge($overtimeCounts)->merge($allowanceCounts);

        return $merged->map(function ($item) {
            return [
                'employee_id' => $item->employee_id,
                'leave_requests' => $item->total_requests ?? 0,
                'leave_days_terverifikasi' => $item->total_days_terverifikasi ?? 0,
                'overtime_hours' => $item->total_hours_lembur ?? 0,
                'allowance_requests' => $item->total_tunjangan_requests ?? 0,
                'allowance_approved' => $item->total_disetujui ?? 0,
            ];
        });
    }

    /**
     * Laporan 3: Riwayat Karir (Dosen).
     */
    public function reportKarirDosen(?string $unitId = null, ?string $periode = null): Collection
    {
        $query = AcademicRankHistory::selectRaw('
            employees.nip,
            employees.full_name,
            academic_rank_histories.rank_name,
            academic_rank_histories.status,
            academic_rank_histories.effective_date,
            academic_rank_histories.is_accelerated')
            ->join('employees', 'academic_rank_histories.employee_id', '=', 'employees.id');

        if ($unitId) {
            $query->where('employees.unit_id', $unitId);
        }

        return $query->orderBy('academic_rank_histories.effective_date', 'desc')->get();
    }

    /**
     * Laporan 4: Riwayat Pangkat (Tendik).
     */
    public function reportPangkatTendik(?string $unitId = null, ?string $periode = null): Collection
    {
        $query = StaffRankHistory::selectRaw('
            employees.nip,
            employees.full_name,
            staff_rank_histories.rank_level,
            staff_rank_histories.status,
            staff_rank_histories.effective_date')
            ->join('employees', 'staff_rank_histories.employee_id', '=', 'employees.id');

        if ($unitId) {
            $query->where('employees.unit_id', $unitId);
        }

        return $query->orderBy('staff_rank_histories.effective_date', 'desc')->get();
    }

    /**
     * Laporan 5: BKD/LKD per semester.
     */
    public function reportBkdLkd(?string $unitId = null, ?string $periode = null): Collection
    {
        $query = WorkloadReport::selectRaw('
            employees.nip,
            employees.full_name,
            workload_reports.academic_year,
            workload_reports.semester,
            workload_reports.total_credit,
            workload_reports.status')
            ->join('employees', 'workload_reports.employee_id', '=', 'employees.id');

        if ($unitId) {
            $query->where('employees.unit_id', $unitId);
        }

        if ($periode) {
            $query->where('workload_reports.academic_year', $periode);
        }

        return $query->orderBy('workload_reports.academic_year', 'desc')
            ->orderBy('workload_reports.semester', 'desc')
            ->get();
    }

    /**
     * Laporan 6: SKP per periode.
     */
    public function reportSkp(?string $unitId = null, ?string $periode = null): Collection
    {
        $query = PerformanceTarget::selectRaw('
            employees.nip,
            employees.full_name,
            performance_targets.period_label,
            performance_targets.final_score,
            performance_targets.rating,
            performance_targets.status')
            ->join('employees', 'performance_targets.employee_id', '=', 'employees.id');

        if ($unitId) {
            $query->where('employees.unit_id', $unitId);
        }

        if ($periode) {
            $query->where('performance_targets.period_label', $periode);
        }

        return $query->orderBy('performance_targets.period_label', 'desc')->get();
    }
}
