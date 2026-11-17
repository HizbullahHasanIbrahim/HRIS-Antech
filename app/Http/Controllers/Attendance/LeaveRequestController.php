<?php

declare(strict_types=1);

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\LeaveRequestRequest;
use App\Models\LeaveRequest;
use App\Services\Attendance\LeaveRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function __construct(
        protected LeaveRequestService $leaveRequestService
    ) {}

    public function store(LeaveRequestRequest $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'Authenticated user has no employee profile.'], 422);
        }

        $this->authorize('create', LeaveRequest::class);

        $leaveRequest = $this->leaveRequestService->createRequest(
            $employee,
            (int) $request->validated('leave_type_id'),
            new \DateTimeImmutable($request->validated('start_date')),
            new \DateTimeImmutable($request->validated('end_date')),
            $request->validated('reason'),
            (int) $request->validated('approver_id')
        );

        return response()->json([
            'success' => true,
            'data' => $leaveRequest,
        ], 201);
    }

    public function approve(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('approve', $leaveRequest);

        $approver = $request->user()->employee;
        if (! $approver) {
            return response()->json(['message' => 'Authenticated user has no employee profile.'], 422);
        }

        $updated = $this->leaveRequestService->approveRequest(
            $leaveRequest,
            $approver,
            $request->input('decision_note')
        );

        return response()->json([
            'success' => true,
            'data' => $updated,
        ]);
    }

    public function reject(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('reject', $leaveRequest);

        $request->validate(['decision_note' => ['required', 'string']]);

        $approver = $request->user()->employee;
        if (! $approver) {
            return response()->json(['message' => 'Authenticated user has no employee profile.'], 422);
        }

        $updated = $this->leaveRequestService->rejectRequest(
            $leaveRequest,
            $approver,
            $request->input('decision_note')
        );

        return response()->json([
            'success' => true,
            'data' => $updated,
        ]);
    }

    public function cancel(LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('cancel', $leaveRequest);

        $updated = $this->leaveRequestService->cancelRequest($leaveRequest);

        return response()->json([
            'success' => true,
            'data' => $updated,
        ]);
    }
}
