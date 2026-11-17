<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reporting;

use App\Http\Controllers\Controller;
use App\Models\AcademicRankHistory;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\PerformanceTarget;
use App\Models\StaffRankHistory;
use App\Models\WorkloadReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    private function csvStream(array $headers, \Closure $rows): StreamedResponse
    {
        return Response::stream(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            $rows($handle);
            fclose($handle);
        });
    }

    public function sdm(Request $request): StreamedResponse
    {
        $stream = $this->csvStream(
            ['Unit', 'Total Pegawai', 'Aktif', 'Pensiun'],
            function ($handle) use ($request) {
                $query = Employee::selectRaw('
                    departments.name as unit_name,
                    COUNT(employees.id) as total_pegawai,
                    SUM(CASE WHEN employment_status = "aktif" THEN 1 ELSE 0 END) as aktif,
                    SUM(CASE WHEN employment_status = "pensiun" THEN 1 ELSE 0 END) as pensiun')
                    ->leftJoin('departments', 'employees.unit_id', '=', 'departments.id');

                if ($request->filled('unit_id')) {
                    $query->where('employees.unit_id', $request->input('unit_id'));
                }

                $query->groupBy('departments.id', 'departments.name')
                    ->orderBy('departments.name')
                    ->get()
                    ->each(fn ($row) => fputcsv($handle, [
                        $row->unit_name,
                        $row->total_pegawai,
                        $row->aktif,
                        $row->pensiun,
                    ]));
            }
        );

        $stream->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $stream->headers->set('Content-Disposition', 'attachment; filename="sdm-export-'.now()->format('Ymd-His').'.csv"');

        return $stream;
    }

    public function cutiLembur(Request $request): StreamedResponse
    {
        $stream = $this->csvStream(
            ['Jenis', 'Pegawai', 'NIP', 'Tanggal Mulai', 'Tanggal Selesai', 'Total', 'Status'],
            function ($handle) use ($request) {
                // Cuti
                $leaveQuery = LeaveRequest::selectRaw('
                    leave_requests.employee_id,
                    employees.full_name,
                    employees.nip,
                    start_date,
                    end_date,
                    total_days,
                    status')
                    ->join('employees', 'leave_requests.employee_id', '=', 'employees.id')
                    ->where('status', 'diajukan');

                if ($request->filled('unit_id')) {
                    $leaveQuery->where('employees.unit_id', $request->input('unit_id'));
                }

                $leaveQuery->orderBy('start_date')
                    ->get()
                    ->each(fn ($row) => fputcsv($handle, [
                        'Cuti',
                        $row->full_name,
                        $row->nip,
                        $row->start_date,
                        $row->end_date,
                        $row->total_days,
                        $row->status,
                    ]));

                // Lembur
                $overtimeQuery = OvertimeRequest::selectRaw('
                    overtime_requests.employee_id,
                    employees.full_name,
                    employees.nip,
                    work_date,
                    start_time,
                    end_time,
                    total_hours,
                    status')
                    ->join('employees', 'overtime_requests.employee_id', '=', 'employees.id')
                    ->where('status', 'diajukan');

                if ($request->filled('unit_id')) {
                    $overtimeQuery->where('employees.unit_id', $request->input('unit_id'));
                }

                $overtimeQuery->orderBy('work_date')
                    ->get()
                    ->each(fn ($row) => fputcsv($handle, [
                        'Lembur',
                        $row->full_name,
                        $row->nip,
                        $row->work_date,
                        $row->end_time,
                        $row->total_hours,
                        $row->status,
                    ]));
            }
        );

        $stream->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $stream->headers->set('Content-Disposition', 'attachment; filename="cuti-lembur-export-'.now()->format('Ymd-His').'.csv"');

        return $stream;
    }

    public function pensiun(Request $request): StreamedResponse
    {
        $stream = $this->csvStream(
            ['Pegawai', 'NIP', 'Unit', 'Tanggal Lahir', 'Tanggal Pensiun', 'Usia Pensiun', 'Status'],
            function ($handle) use ($request) {
                $query = Employee::selectRaw('
                    employees.full_name,
                    employees.nip,
                    departments.name as unit_name,
                    employees.birth_date,
                    employees.end_date,
                    TIMESTAMPDIFF(YEAR, employees.birth_date, employees.end_date) as usia_pensiun,
                    employees.employment_status as status')
                    ->leftJoin('departments', 'employees.unit_id', '=', 'departments.id')
                    ->where('employment_status', 'pensiun');

                if ($request->filled('unit_id')) {
                    $query->where('employees.unit_id', $request->input('unit_id'));
                }

                $query->orderBy('employees.end_date')
                    ->get()
                    ->each(fn ($row) => fputcsv($handle, [
                        $row->full_name,
                        $row->nip,
                        $row->unit_name,
                        $row->birth_date,
                        $row->end_date,
                        $row->usia_pensiun,
                        $row->status,
                    ]));
            }
        );

        $stream->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $stream->headers->set('Content-Disposition', 'attachment; filename="pensiun-export-'.now()->format('Ymd-His').'.csv"');

        return $stream;
    }

    public function karirDosen(Request $request): StreamedResponse
    {
        $stream = $this->csvStream(
            ['Pegawai', 'NIP', 'Unit', 'Jabatan Akademik', 'Tanggal Efektif', 'Status'],
            function ($handle) use ($request) {
                $query = AcademicRankHistory::selectRaw('
                    employees.full_name,
                    employees.nip,
                    departments.name as unit_name,
                    academic_rank_histories.rank as jabatan_akademik,
                    academic_rank_histories.effective_date,
                    academic_rank_histories.status')
                    ->join('employees', 'academic_rank_histories.employee_id', '=', 'employees.id')
                    ->leftJoin('departments', 'employees.unit_id', '=', 'departments.id');

                if ($request->filled('unit_id')) {
                    $query->where('employees.unit_id', $request->input('unit_id'));
                }

                $query->orderBy('academic_rank_histories.effective_date', 'desc')
                    ->get()
                    ->each(fn ($row) => fputcsv($handle, [
                        $row->full_name,
                        $row->nip,
                        $row->unit_name,
                        $row->jabatan_akademik,
                        $row->effective_date,
                        $row->status,
                    ]));
            }
        );

        $stream->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $stream->headers->set('Content-Disposition', 'attachment; filename="karir-dosen-export-'.now()->format('Ymd-His').'.csv"');

        return $stream;
    }

    public function pangkatTendik(Request $request): StreamedResponse
    {
        $stream = $this->csvStream(
            ['Pegawai', 'NIP', 'Unit', 'Pangkat/Golongan', 'Tanggal Efektif', 'Status'],
            function ($handle) use ($request) {
                $query = StaffRankHistory::selectRaw('
                    employees.full_name,
                    employees.nip,
                    departments.name as unit_name,
                    staff_rank_histories.rank_grade as pangkat_golongan,
                    staff_rank_histories.effective_date,
                    staff_rank_histories.status')
                    ->join('employees', 'staff_rank_histories.employee_id', '=', 'employees.id')
                    ->leftJoin('departments', 'employees.unit_id', '=', 'departments.id');

                if ($request->filled('unit_id')) {
                    $query->where('employees.unit_id', $request->input('unit_id'));
                }

                $query->orderBy('staff_rank_histories.effective_date', 'desc')
                    ->get()
                    ->each(fn ($row) => fputcsv($handle, [
                        $row->full_name,
                        $row->nip,
                        $row->unit_name,
                        $row->pangkat_golongan,
                        $row->effective_date,
                        $row->status,
                    ]));
            }
        );

        $stream->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $stream->headers->set('Content-Disposition', 'attachment; filename="pangkat-tendik-export-'.now()->format('Ymd-His').'.csv"');

        return $stream;
    }

    public function bkdLkd(Request $request): StreamedResponse
    {
        $stream = $this->csvStream(
            ['Pegawai', 'NIP', 'Unit', 'Tahun Akademik', 'Semester', 'Kredit Mengajar', 'Kredit Penelitian', 'Kredit Pengabdian', 'Kredit Penunjang', 'Total Kredit', 'Versi', 'Status'],
            function ($handle) use ($request) {
                $query = WorkloadReport::selectRaw('
                    employees.full_name,
                    employees.nip,
                    departments.name as unit_name,
                    workload_reports.academic_year,
                    workload_reports.semester,
                    workload_reports.teaching_credit,
                    workload_reports.research_credit,
                    workload_reports.service_credit,
                    workload_reports.support_credit,
                    workload_reports.total_credit,
                    workload_reports.version,
                    workload_reports.is_current')
                    ->join('employees', 'workload_reports.employee_id', '=', 'employees.id')
                    ->leftJoin('departments', 'employees.unit_id', '=', 'departments.id')
                    ->where('is_current', 1);

                if ($request->filled('unit_id')) {
                    $query->where('employees.unit_id', $request->input('unit_id'));
                }

                $query->orderBy('workload_reports.academic_year', 'desc')
                    ->get()
                    ->each(fn ($row) => fputcsv($handle, [
                        $row->full_name,
                        $row->nip,
                        $row->unit_name,
                        $row->academic_year,
                        $row->semester,
                        $row->teaching_credit,
                        $row->research_credit,
                        $row->service_credit,
                        $row->support_credit,
                        $row->total_credit,
                        $row->version,
                        $row->is_current ? 'Final' : 'Draft',
                    ]));
            }
        );

        $stream->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $stream->headers->set('Content-Disposition', 'attachment; filename="bkd-lkd-export-'.now()->format('Ymd-His').'.csv"');

        return $stream;
    }

    public function skp(Request $request): StreamedResponse
    {
        $stream = $this->csvStream(
            ['Pegawai', 'NIP', 'Unit', 'Periode', 'Skor Akhir', 'Rating', 'Status', 'Tanggal Disetujui'],
            function ($handle) use ($request) {
                $query = PerformanceTarget::selectRaw('
                    employees.full_name,
                    employees.nip,
                    departments.name as unit_name,
                    performance_targets.period_label,
                    performance_targets.final_score,
                    performance_targets.rating,
                    performance_targets.status,
                    performance_targets.approved_at')
                    ->join('employees', 'performance_targets.employee_id', '=', 'employees.id')
                    ->leftJoin('departments', 'employees.unit_id', '=', 'departments.id')
                    ->where('status', 'disetujui');

                if ($request->filled('unit_id')) {
                    $query->where('employees.unit_id', $request->input('unit_id'));
                }

                $query->orderBy('performance_targets.period_label', 'desc')
                    ->get()
                    ->each(fn ($row) => fputcsv($handle, [
                        $row->full_name,
                        $row->nip,
                        $row->unit_name,
                        $row->period_label,
                        $row->final_score,
                        $row->rating,
                        $row->status,
                        $row->approved_at,
                    ]));
            }
        );

        $stream->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $stream->headers->set('Content-Disposition', 'attachment; filename="skp-export-'.now()->format('Ymd-His').'.csv"');

        return $stream;
    }
}
