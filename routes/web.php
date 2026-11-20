<?php

use App\Http\Controllers\Web\AuditWebController;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\CareerActionController;
use App\Http\Controllers\Web\CareerWebController;
use App\Http\Controllers\Web\OrganizationWebController;
use App\Http\Controllers\Web\PerformanceActionController;
use App\Http\Controllers\Web\PerformanceWebController;
use App\Http\Controllers\Web\RecruitmentWebController;
use App\Http\Controllers\Web\ReportWebController;
use App\Http\Controllers\Web\RequestActionController;
use App\Http\Controllers\Web\RequestWebController;
use App\Http\Controllers\Web\RetirementWebController;
use App\Http\Controllers\Web\ScheduleWebController;
use App\Http\Controllers\Web\WelfareWebController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Landing page (public, separate from login flow)
Route::get('/', function () {
    return Inertia::render('Landing/Index');
})->name('landing');

// Separate Auth Flow
Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthWebController::class, 'login'])->name('login.submit')->middleware('throttle:5,1');

Route::get('/register', [AuthWebController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthWebController::class, 'register'])->name('register.submit');
Route::post('/register/applicant', [AuthWebController::class, 'registerApplicant'])->name('register.applicant');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function (\App\Services\Dashboard\DashboardStatsService $service) {
        return Inertia::render('Dashboard/Index', [
            'stats' => $service->summary(),
            'recentActivities' => \App\Models\ActivityLog::with('actor.employee')->latest()->take(5)->get()->map(fn ($log) => [
                'action' => $log->action,
                'actor_name' => $log->actor?->employee?->full_name ?? $log->actor?->username ?? 'System',
                'time' => $log->created_at?->format('Y-m-d H:i:s'),
            ]),
        ]);
    })->name('dashboard');

    Route::get('/requests', [RequestWebController::class, 'index'])->name('requests.index');

    // Action routes for leave/overtime/allowance requests (using web session auth)
    Route::prefix('requests')->group(function () {
        Route::post('/leave', [RequestActionController::class, 'storeLeave'])->name('requests.leave.create');
        Route::post('/leave/{leaveRequest}/approve', [RequestActionController::class, 'approveLeave'])->name('requests.leave.approve');
        Route::post('/leave/{leaveRequest}/reject', [RequestActionController::class, 'rejectLeave'])->name('requests.leave.reject');
        Route::post('/leave/{leaveRequest}/cancel', [RequestActionController::class, 'cancelLeave'])->name('requests.leave.cancel');

        Route::post('/overtime', [RequestActionController::class, 'storeOvertime'])->name('requests.overtime.create');
        Route::post('/overtime/{overtimeRequest}/approve', [RequestActionController::class, 'approveOvertime'])->name('requests.overtime.approve');
        Route::post('/overtime/{overtimeRequest}/reject', [RequestActionController::class, 'rejectOvertime'])->name('requests.overtime.reject');
        Route::post('/overtime/{overtimeRequest}/cancel', [RequestActionController::class, 'cancelOvertime'])->name('requests.overtime.cancel');

        Route::post('/allowance', [RequestActionController::class, 'storeAllowance'])->name('requests.allowance.create');
        Route::post('/allowance/{allowanceRequest}/approve', [RequestActionController::class, 'approveAllowance'])->name('requests.allowance.approve');
        Route::post('/allowance/{allowanceRequest}/reject', [RequestActionController::class, 'rejectAllowance'])->name('requests.allowance.reject');
        Route::post('/allowance/{allowanceRequest}/cancel', [RequestActionController::class, 'cancelAllowance'])->name('requests.allowance.cancel');
    });

    Route::get('/organization', [OrganizationWebController::class, 'index'])->name('organization.index');

    Route::get('/performance', [PerformanceWebController::class, 'index'])->name('performance.index');

    // Performance action routes (web auth)
    Route::prefix('performance')->group(function () {
        Route::post('/workload/{report}/validate', [PerformanceActionController::class, 'validateWorkload'])->name('performance.workload.validate');
        Route::post('/workload/{report}/revise', [PerformanceActionController::class, 'reviseWorkload'])->name('performance.workload.revise');
        Route::post('/workload/{report}/cancel', [PerformanceActionController::class, 'cancelWorkload'])->name('performance.workload.cancel');
        Route::post('/skp/{target}/evaluate', [PerformanceActionController::class, 'evaluateSkp'])->name('performance.skp.evaluate');
        Route::post('/skp/{target}/reject', [PerformanceActionController::class, 'rejectSkp'])->name('performance.skp.reject');
    });

    Route::get('/career', [CareerWebController::class, 'index'])->name('career.index');

    // Career action routes (web auth)
    Route::prefix('career')->group(function () {
        Route::post('/academic-ranks/{rank}/approve', [CareerActionController::class, 'approveAcademicRank'])->name('career.academic.approve');
        Route::post('/academic-ranks/{rank}/reject', [CareerActionController::class, 'rejectAcademicRank'])->name('career.academic.reject');
        Route::post('/staff-ranks/{rank}/approve', [CareerActionController::class, 'approveStaffRank'])->name('career.staff.approve');
        Route::post('/staff-ranks/{rank}/reject', [CareerActionController::class, 'rejectStaffRank'])->name('career.staff.reject');
    });

    Route::get('/recruitment', [RecruitmentWebController::class, 'index'])->name('recruitment.index');
    Route::post('/recruitment', [RecruitmentWebController::class, 'storeVacancy'])->name('recruitment.store');
    Route::get('/recruitment/users', [RecruitmentWebController::class, 'users'])->name('recruitment.users');
    Route::post('/recruitment/users', [RecruitmentWebController::class, 'storeUser'])->name('recruitment.users.store');
    Route::get('/recruitment/onboarding', [RecruitmentWebController::class, 'onboarding'])->name('recruitment.onboarding');
    Route::get('/schedules', [ScheduleWebController::class, 'index'])->name('schedules.index');
    Route::get('/welfare/bpjs', [WelfareWebController::class, 'bpjs'])->name('welfare.bpjs');
    Route::post('/welfare/bpjs', [WelfareWebController::class, 'storeBpjs'])->name('welfare.bpjs.store');
    Route::post('/welfare/bpjs/{membership}/cancel', [WelfareWebController::class, 'cancelBpjs'])->name('welfare.bpjs.cancel');
    Route::get('/career/study-leave', [CareerWebController::class, 'studyLeave'])->name('career.study-leave');
    Route::get('/career/certifications', [CareerWebController::class, 'certifications'])->name('career.certifications');
    Route::get('/career/training', [CareerWebController::class, 'training'])->name('career.training');
    Route::get('/retirement', [RetirementWebController::class, 'index'])->name('retirement.index');
    Route::post('/retirement', [RetirementWebController::class, 'store'])->name('retirement.store');
    Route::get('/reports', [ReportWebController::class, 'index'])->name('reports.index');
    Route::get('/audit/activity-logs', [AuditWebController::class, 'index'])->name('audit.activity-logs');

    Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout');
});
