<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AllowanceRequest;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\OvertimeRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RequestWebController extends Controller
{
    public function index(Request $request): Response
    {
        $employee = $request->user()->employee;
        $isAdminOrHead = $request->user()->hasRole('Admin BSDM') || $request->user()->hasRole('Kepala Biro');

        $leaveQuery = LeaveRequest::with(['leaveType', 'employee', 'approver'])
            ->latest();

        $overtimeQuery = OvertimeRequest::with(['employee', 'approver'])
            ->latest();

        $allowanceQuery = AllowanceRequest::with(['employee', 'allowanceType', 'approver'])
            ->latest();

        if (! $isAdminOrHead && $employee) {
            $leaveQuery->where('employee_id', $employee->id);
            $overtimeQuery->where('employee_id', $employee->id);
            $allowanceQuery->where('employee_id', $employee->id);
        }

        return Inertia::render('Requests/Index', [
            'leaveRequests' => $leaveQuery->limit(50)->get(),
            'overtimeRequests' => $overtimeQuery->limit(50)->get(),
            'allowanceRequests' => $allowanceQuery->limit(50)->get(),
            'leaveTypes' => LeaveType::where('is_active', true)->get(),
            'authUserId' => $request->user()->id,
            'isApprover' => $isAdminOrHead || ($employee && $employee->assignments()->exists()),
        ]);
    }
}
