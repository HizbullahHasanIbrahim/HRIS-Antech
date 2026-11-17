<?php

use App\Http\Controllers\Attendance\LeaveRequestController;
use App\Http\Controllers\Performance\SkpController;
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

    // SKP Targets & Evaluations (P-13)
    Route::post('/skp/targets', [SkpController::class, 'storeTarget']);
    Route::post('/skp/targets/{target}/evaluate', [SkpController::class, 'evaluate']);
    Route::post('/skp/targets/{target}/reject', [SkpController::class, 'reject']);
});
