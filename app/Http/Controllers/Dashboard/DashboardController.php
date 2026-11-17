<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardStatsService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardStatsService $statsService
    ) {}

    public function stats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->statsService->summary(),
        ]);
    }
}
