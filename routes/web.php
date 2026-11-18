<?php

use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\CareerWebController;
use App\Http\Controllers\Web\OrganizationWebController;
use App\Http\Controllers\Web\PerformanceWebController;
use App\Http\Controllers\Web\ReportWebController;
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

    Route::get('/organization', [OrganizationWebController::class, 'index'])->name('organization.index');

    Route::get('/performance', [PerformanceWebController::class, 'index'])->name('performance.index');

    Route::get('/career', [CareerWebController::class, 'index'])->name('career.index');

    Route::get('/reports', [ReportWebController::class, 'index'])->name('reports.index');

    Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout');
});
