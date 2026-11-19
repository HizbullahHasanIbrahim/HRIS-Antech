<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AllowanceRequest;
use App\Models\AllowanceType;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Services\Attendance\LeaveRequestService;
use App\Services\Attendance\OvertimeRequestService;
use App\Services\Welfare\AllowanceRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RequestActionController extends Controller
{
    public function __construct(
        protected LeaveRequestService $leaveRequestService,
        protected OvertimeRequestService $overtimeRequestService,
        protected AllowanceRequestService $allowanceRequestService,
    ) {}

    // === LEAVE REQUEST CREATE ===
    public function storeLeave(Request $request): RedirectResponse
    {
        $user = $request->user();
        $employee = $user?->employee;

        if (! $employee) {
            abort(422, 'User has no employee profile.');
        }

        $validated = $request->validate([
            'leave_type_id' => ['required', 'integer'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'min:3'],
            'approver_id' => ['required', 'integer'],
        ]);

        $this->leaveRequestService->createRequest(
            $employee,
            (int) $validated['leave_type_id'],
            new \DateTimeImmutable($validated['start_date']),
            new \DateTimeImmutable($validated['end_date']),
            $validated['reason'],
            (int) $validated['approver_id']
        );

        return back()->with('success', 'Permintaan cuti telah diajukan.');
    }

    // === LEAVE REQUEST ACTIONS ===
    public function approveLeave(LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('approve', $leaveRequest);

        $user = request()->user();
        $approver = $user?->employee;

        if (! $approver) {
            abort(403, 'User has no employee profile.');
        }

        $this->leaveRequestService->approveRequest(
            $leaveRequest,
            $approver,
            request()->input('decision_note')
        );

        return back()->with('success', 'Permintaan cuti telah disetujui.');
    }

    public function rejectLeave(LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('reject', $leaveRequest);

        $user = request()->user();
        $approver = $user?->employee;

        if (! $approver) {
            abort(403, 'User has no employee profile.');
        }

        $validated = request()->validate([
            'decision_note' => ['required', 'string', 'min:3'],
        ]);

        $this->leaveRequestService->rejectRequest(
            $leaveRequest,
            $approver,
            $validated['decision_note']
        );

        return back()->with('success', 'Permintaan cuti telah ditolak.');
    }

    public function cancelLeave(LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('cancel', $leaveRequest);

        $this->leaveRequestService->cancelRequest($leaveRequest);

        return back()->with('success', 'Permintaan cuti telah dibatalkan.');
    }

    // === OVERTIME REQUEST CREATE ===
    public function storeOvertime(Request $request): RedirectResponse
    {
        $user = $request->user();
        $employee = $user?->employee;

        if (! $employee) {
            abort(422, 'User has no employee profile.');
        }

        $validated = $request->validate([
            'work_date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'reason' => ['required', 'string', 'min:3'],
            'approver_id' => ['required', 'integer'],
        ]);

        $this->overtimeRequestService->createRequest(
            $employee,
            new \DateTimeImmutable($validated['work_date']),
            $validated['start_time'],
            $validated['end_time'],
            $validated['reason'],
            (int) $validated['approver_id']
        );

        return back()->with('success', 'Permintaan lembur telah diajukan.');
    }

    // === OVERTIME REQUEST ACTIONS ===
    public function approveOvertime(OvertimeRequest $overtimeRequest): RedirectResponse
    {
        $this->authorize('approve', $overtimeRequest);

        $user = request()->user();
        $approver = $user?->employee;

        if (! $approver) {
            abort(403, 'User has no employee profile.');
        }

        $this->overtimeRequestService->approveRequest(
            $overtimeRequest,
            $approver,
            request()->input('decision_note')
        );

        return back()->with('success', 'Permintaan lembur telah disetujui.');
    }

    public function rejectOvertime(OvertimeRequest $overtimeRequest): RedirectResponse
    {
        $this->authorize('reject', $overtimeRequest);

        $user = request()->user();
        $approver = $user?->employee;

        if (! $approver) {
            abort(403, 'User has no employee profile.');
        }

        $validated = request()->validate([
            'decision_note' => ['required', 'string', 'min:3'],
        ]);

        $this->overtimeRequestService->rejectRequest(
            $overtimeRequest,
            $approver,
            $validated['decision_note']
        );

        return back()->with('success', 'Permintaan lembur telah ditolak.');
    }

    public function cancelOvertime(OvertimeRequest $overtimeRequest): RedirectResponse
    {
        $this->authorize('cancel', $overtimeRequest);

        $this->overtimeRequestService->cancelRequest($overtimeRequest);

        return back()->with('success', 'Permintaan lembur telah dibatalkan.');
    }

    // === ALLOWANCE REQUEST CREATE ===
    public function storeAllowance(Request $request): RedirectResponse
    {
        $user = $request->user();
        $employee = $user?->employee;

        if (! $employee) {
            abort(422, 'User has no employee profile.');
        }

        $validated = $request->validate([
            'allowance_type_id' => ['required', 'integer'],
            'period_label' => ['required', 'string'],
            'amount' => ['required', 'numeric'],
            'approver_id' => ['required', 'integer'],
        ]);

        $allowanceType = AllowanceType::findOrFail((int) $validated['allowance_type_id']);

        $this->allowanceRequestService->createRequest(
            $employee,
            $allowanceType,
            $validated['period_label'],
            (float) $validated['amount'],
            now(),
            (int) $validated['approver_id']
        );

        return back()->with('success', 'Permintaan tunjangan telah diajukan.');
    }

    // === ALLOWANCE REQUEST ACTIONS ===
    public function approveAllowance(AllowanceRequest $allowanceRequest): RedirectResponse
    {
        $this->authorize('approve', $allowanceRequest);

        $user = request()->user();
        $approver = $user?->employee;

        if (! $approver) {
            abort(403, 'User has no employee profile.');
        }

        $this->allowanceRequestService->approveRequest(
            $allowanceRequest,
            $approver,
            request()->input('decision_note')
        );

        return back()->with('success', 'Permintaan tunjangan telah disetujui.');
    }

    public function rejectAllowance(AllowanceRequest $allowanceRequest): RedirectResponse
    {
        $this->authorize('reject', $allowanceRequest);

        $user = request()->user();
        $approver = $user?->employee;

        if (! $approver) {
            abort(403, 'User has no employee profile.');
        }

        $validated = request()->validate([
            'decision_note' => ['required', 'string', 'min:3'],
        ]);

        $this->allowanceRequestService->rejectRequest(
            $allowanceRequest,
            $approver,
            $validated['decision_note']
        );

        return back()->with('success', 'Permintaan tunjangan telah ditolak.');
    }

    public function cancelAllowance(AllowanceRequest $allowanceRequest): RedirectResponse
    {
        $this->authorize('cancel', $allowanceRequest);

        $this->allowanceRequestService->cancelRequest($allowanceRequest);

        return back()->with('success', 'Permintaan tunjangan telah dibatalkan.');
    }
}
