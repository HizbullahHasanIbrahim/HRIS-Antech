<?php

declare(strict_types=1);

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use Illuminate\Http\JsonResponse;

class LeaveTypeController extends Controller
{
    public function index(): JsonResponse
    {
        $types = LeaveType::where('is_active', true)->get();

        return response()->json(['success' => true, 'data' => $types]);
    }
}
