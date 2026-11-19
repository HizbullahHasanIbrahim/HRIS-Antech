<?php

use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\CareerActionController;
use App\Http\Controllers\Web\CareerWebController;
use App\Http\Controllers\Web\OrganizationWebController;
use App\Http\Controllers\Web\PerformanceActionController;
use App\Http\Controllers\Web\PerformanceWebController;
use App\Http\Controllers\Web\ReportWebController;
use App\Http\Controllers\Web\RequestActionController;
use App\Http\Controllers\Web\RequestWebController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthWebController::class, 'login'])->name('login.submit');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard/Index');
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

    Route::get('/reports', [ReportWebController::class, 'index'])->name('reports.index');

    Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout');
});
