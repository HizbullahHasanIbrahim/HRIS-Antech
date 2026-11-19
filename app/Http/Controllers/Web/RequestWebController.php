<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AllowanceRequest;
use App\Models\AllowanceType;
use App\Models\Employee;
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

        // Fetch approvers (supervisors) for the employee's unit
        $approvers = Employee::where('employment_status', 'aktif')
            ->when($employee, fn ($q) => $q->where('unit_id', $employee->unit_id))
            ->whereNotNull('supervisor_id')
            ->with('supervisor')
            ->get()
            ->pluck('supervisor')
            ->unique('id')
            ->values()
            ->map(fn ($s) => ['id' => $s->id, 'name' => $s->full_name, 'nip' => $s->nip])
            ->toArray();

        $allowanceTypes = AllowanceType::where('is_active', true)
            ->get(['id', 'name', 'code'])
            ->toArray();

        return Inertia::render('Requests/Index', [
            'leaveRequests' => $leaveQuery->paginate(15)->withQueryString(),
            'overtimeRequests' => $overtimeQuery->paginate(15)->withQueryString(),
            'allowanceRequests' => $allowanceQuery->paginate(15)->withQueryString(),
            'leaveTypes' => LeaveType::where('is_active', true)->get(),
            'approvers' => $approvers,
            'allowanceTypes' => $allowanceTypes,
            'authUserId' => $request->user()->id,
            'isApprover' => $isAdminOrHead || ($employee && $employee->assignments()->exists()),
        ]);
    }
}
