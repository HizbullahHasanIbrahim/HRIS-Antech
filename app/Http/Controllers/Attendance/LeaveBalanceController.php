<?php

declare(strict_types=1);

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\LeaveBalance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveBalanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $this->authorize('viewAny', LeaveBalance::class);

        $query = LeaveBalance::with('employee', 'leaveType')
            ->when($request->input('year'), fn ($q, $y) => $q->where('year', $y));

        if (! $request->user()->hasRole('Admin BSDM') && ! $request->user()->hasRole('Kepala Biro')) {
            $query->where('employee_id', $employee->id);
        }

        return response()->json(['success' => true, 'data' => $query->paginate()]);
    }
}
