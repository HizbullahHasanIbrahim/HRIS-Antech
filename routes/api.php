<?php

use App\Http\Controllers\Attendance\LeaveRequestController;
use App\Http\Controllers\Attendance\OvertimeRequestController;
use App\Http\Controllers\Attendance\WorkScheduleController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Career\CareerController;
use App\Http\Controllers\Common\FileUploadController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Notifications\NotificationController;
use App\Http\Controllers\Organization\DepartmentController;
use App\Http\Controllers\Organization\EmployeeAssignmentController;
use App\Http\Controllers\Organization\EmployeeController;
use App\Http\Controllers\Performance\SkpController;
use App\Http\Controllers\Performance\WorkloadReportController;
use App\Http\Controllers\Recruitment\RecruitmentController;
use App\Http\Controllers\Reporting\ExportController;
use App\Http\Controllers\Reporting\ReportController;
use App\Http\Controllers\Retirement\RetirementController;
use App\Http\Controllers\Welfare\AllowanceRequestController;
use App\Http\Controllers\Welfare\BpjsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Auth routes (public)
Route::prefix('v1/auth')->middleware('throttle:auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth:api');
    Route::post('/refresh', [AuthController::class, 'refresh'])->middleware('auth:api');
});

// Public recruitment routes (no auth required)
Route::prefix('v1')->middleware('throttle:api')->group(function () {
    Route::get('/vacancies', [RecruitmentController::class, 'indexVacancies']);
    Route::get('/vacancies/{vacancy}', [RecruitmentController::class, 'showVacancy']);
    Route::post('/applicants', [RecruitmentController::class, 'storeApplicant']);
    Route::post('/applications', [RecruitmentController::class, 'storeApplication']);
    Route::post('/upload/cv', [FileUploadController::class, 'cv']);
});

Route::middleware(['auth:api', 'unit.scope', 'throttle:api'])->prefix('v1')->group(function () {
    // Dashboard Stats
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

    // File Upload (Protected)
    Route::post('/upload/certification', [FileUploadController::class, 'certification']);

    // Leave Requests (P-04)
    Route::post('/leave-requests', [LeaveRequestController::class, 'store']);
    Route::post('/leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve']);
    Route::post('/leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject']);
    Route::post('/leave-requests/{leaveRequest}/cancel', [LeaveRequestController::class, 'cancel']);

    // Overtime Requests (P-05)
    Route::post('/overtime-requests', [OvertimeRequestController::class, 'store']);
    Route::post('/overtime-requests/{overtimeRequest}/approve', [OvertimeRequestController::class, 'approve']);
    Route::post('/overtime-requests/{overtimeRequest}/reject', [OvertimeRequestController::class, 'reject']);
    Route::post('/overtime-requests/{overtimeRequest}/cancel', [OvertimeRequestController::class, 'cancel']);

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
    Route::post('/applications/{application}', [RecruitmentController::class, 'updateApplicationStatus']);
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
    Route::post('/study-leaves/{studyLeave}', [CareerController::class, 'approveStudyLeave']);
    Route::post('/study-leaves/{studyLeave}/reject', [CareerController::class, 'rejectStudyLeave']);

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
    Route::post('/allowances/{allowanceRequest}/approve', [AllowanceRequestController::class, 'approve']);
    Route::post('/allowances/{allowanceRequest}/cancel', [AllowanceRequestController::class, 'cancel']);

    // Retirement (P-16)
    Route::post('/retirements', [RetirementController::class, 'store']);
    Route::get('/retirements', [RetirementController::class, 'index']);
    Route::get('/retirements/{retirement}', [RetirementController::class, 'show']);
    Route::post('/retirements/{retirement}/process', [RetirementController::class, 'process']);
    Route::post('/retirements/{retirement}/cancel', [RetirementController::class, 'cancel']);
    Route::post('/retirements/{retirement}/complete', [RetirementController::class, 'complete']);

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

    // Report Exports (CSV) - rate limited
    Route::middleware('throttle:export')->group(function () {
        Route::get('/reports/export/sdm-summary', [ExportController::class, 'sdm']);
        Route::get('/reports/export/cuti-lembur', [ExportController::class, 'cutiLembur']);
        Route::get('/reports/export/karir-dosen', [ExportController::class, 'karirDosen']);
        Route::get('/reports/export/pangkat-tendik', [ExportController::class, 'pangkatTendik']);
        Route::get('/reports/export/bkd-lkd', [ExportController::class, 'bkdLkd']);
        Route::get('/reports/export/skp', [ExportController::class, 'skp']);
    });

    // Search endpoints - rate limited
    Route::middleware('throttle:search')->group(function () {
        Route::get('/search/employees', [ReportController::class, 'searchEmployee']);
        Route::get('/search/applications', [ReportController::class, 'searchApplication']);
        Route::get('/search/vacancies', [ReportController::class, 'searchVacancy']);
    });
});
