<?php

use App\Http\Controllers\Attendance\LeaveRequestController;
use App\Http\Controllers\Attendance\WorkScheduleController;
use App\Http\Controllers\Career\CareerController;
use App\Http\Controllers\Organization\DepartmentController;
use App\Http\Controllers\Organization\EmployeeAssignmentController;
use App\Http\Controllers\Organization\EmployeeController;
use App\Http\Controllers\Performance\SkpController;
use App\Http\Controllers\Performance\WorkloadReportController;
use App\Http\Controllers\Recruitment\RecruitmentController;
use App\Http\Controllers\Reporting\ReportController;
use App\Http\Controllers\Welfare\AllowanceRequestController;
use App\Http\Controllers\Welfare\BpjsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    // Leave Requests (P-04)
    Route::post('/leave-requests', [LeaveRequestController::class, 'store']);
    Route::post('/leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve']);
    Route::post('/leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject']);
    Route::post('/leave-requests/{leaveRequest}/cancel', [LeaveRequestController::class, 'cancel']);

    // Work Schedules (P-06)
    Route::get('/work-schedules', [WorkScheduleController::class, 'index']);
    Route::post('/work-schedules', [WorkScheduleController::class, 'store']);
    Route::delete('/work-schedules/{workSchedule}', [WorkScheduleController::class, 'destroy']);
    Route::post('/work-schedules/{workSchedule}/assign', [WorkScheduleController::class, 'assign']);

    // Performance BKD/LKD (P-12)
    Route::post('/workload-reports', [WorkloadReportController::class, 'store']);
    Route::post('/workload-reports/{report}/submit', [WorkloadReportController::class, 'submit']);
    Route::post('/workload-reports/{report}/validate', [WorkloadReportController::class, 'validateReport']);
    Route::post('/workload-reports/{report}/revise', [WorkloadReportController::class, 'revise']);

    // SKP Targets & Evaluations (P-13)
    Route::post('/skp/targets', [SkpController::class, 'storeTarget']);
    Route::post('/skp/targets/{target}/evaluate', [SkpController::class, 'evaluate']);
    Route::post('/skp/targets/{target}/reject', [SkpController::class, 'reject']);

    // Recruitment (P-01, P-02, P-03)
    Route::post('/vacancies', [RecruitmentController::class, 'storeVacancy']);
    Route::get('/vacancies', [RecruitmentController::class, 'indexVacancies']);
    Route::get('/vacancies/{vacancy}', [RecruitmentController::class, 'showVacancy']);
    Route::post('/applicants', [RecruitmentController::class, 'storeApplicant']);
    Route::get('/applicants', [RecruitmentController::class, 'indexApplicants']);
    Route::post('/applications', [RecruitmentController::class, 'storeApplication']);
    Route::put('/applications/{application}', [RecruitmentController::class, 'updateApplicationStatus']);
    Route::post('/applications/{application}/stages', [RecruitmentController::class, 'storeSelectionStage']);
    Route::put('/applications/{application}/stages/{stage}', [RecruitmentController::class, 'updateSelectionStageResult']);

    // Career (P-07-P-11)
    Route::post('/academic-ranks', [CareerController::class, 'storeAcademicRank']);
    Route::get('/academic-ranks', [CareerController::class, 'indexAcademicRanks']);
    Route::post('/academic-ranks/{rank}', [CareerController::class, 'approveAcademicRank']);
    Route::post('/academic-ranks/{rank}/reject', [CareerController::class, 'rejectAcademicRank']);

    Route::post('/staff-ranks', [CareerController::class, 'storeStaffRank']);
    Route::get('/staff-ranks', [CareerController::class, 'indexStaffRanks']);
    Route::post('/staff-ranks/{rank}', [CareerController::class, 'approveStaffRank']);
    Route::post('/staff-ranks/{rank}/reject', [CareerController::class, 'rejectStaffRank']);

    Route::post('/study-leaves', [CareerController::class, 'storeStudyLeave']);
    Route::get('/study-leaves', [CareerController::class, 'indexStudyLeaves']);
    Route::post('/study-leaves/{leave}', [CareerController::class, 'approveStudyLeave']);
    Route::post('/study-leaves/{leave}/reject', [CareerController::class, 'rejectStudyLeave']);

    Route::post('/certifications', [CareerController::class, 'storeCertification']);
    Route::get('/certifications', [CareerController::class, 'indexCertifications']);
    Route::delete('/certifications/{certification}', [CareerController::class, 'destroyCertification']);

    Route::post('/trainings', [CareerController::class, 'storeTraining']);
    Route::get('/trainings', [CareerController::class, 'indexTrainings']);
    Route::post('/trainings/{training}/complete', [CareerController::class, 'completeTraining']);
    Route::post('/trainings/{training}/cancel', [CareerController::class, 'cancelTraining']);

    // Welfare BPJS & Allowance (P-14, P-15)
    Route::post('/bpjs', [BpjsController::class, 'store']);
    Route::get('/bpjs', [BpjsController::class, 'index']);
    Route::post('/bpjs/{membership}/cancel', [BpjsController::class, 'cancel']);
    Route::post('/allowances', [AllowanceRequestController::class, 'store']);
    Route::get('/allowances', [AllowanceRequestController::class, 'index']);
    Route::post('/allowances/{allowance}/approve', [AllowanceRequestController::class, 'approve']);
    Route::post('/allowances/{allowance}/cancel', [AllowanceRequestController::class, 'cancel']);

    // Organization (Departments, Employees, Assignments)
    Route::apiResource('/departments', DepartmentController::class, ['only' => ['index', 'store', 'update', 'destroy']]);
    Route::apiResource('/employees', EmployeeController::class, ['only' => ['index', 'store', 'show', 'update', 'destroy']]);
    Route::apiResource('/employee-assignments', EmployeeAssignmentController::class, ['only' => ['index', 'store']]);
    Route::post('/employee-assignments/{assignment}/terminate', [EmployeeAssignmentController::class, 'terminate']);

    // Reports (P-17)
    Route::get('/reports/sdm-summary', [ReportController::class, 'summarySdm']);
    Route::get('/reports/cuti-lembur', [ReportController::class, 'cutiLembur']);
    Route::get('/reports/karir-dosen', [ReportController::class, 'karirDosen']);
    Route::get('/reports/pangkat-tendik', [ReportController::class, 'pangkatTendik']);
    Route::get('/reports/bkd-lkd', [ReportController::class, 'bkdLkd']);
    Route::get('/reports/skp', [ReportController::class, 'skp']);
});
