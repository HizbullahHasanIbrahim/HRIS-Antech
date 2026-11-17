<?php

declare(strict_types=1);

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\OvertimeRequestRequest;
use App\Models\OvertimeRequest;
use App\Services\Attendance\OvertimeRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OvertimeRequestController extends Controller
{
    public function __construct(
        protected OvertimeRequestService $overtimeService
    ) {}

    public function store(OvertimeRequestRequest $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $this->authorize('create', OvertimeRequest::class);

        $overtime = $this->overtimeService->createRequest(
            $employee,
            new \DateTimeImmutable($request->validated('work_date')),
            $request->validated('start_time'),
            $request->validated('end_time'),
            $request->validated('reason'),
            (int) $request->validated('approver_id')
        );

        return response()->json(['success' => true, 'data' => $overtime], 201);
    }

    public function approve(Request $request, OvertimeRequest $overtimeRequest): JsonResponse
    {
        $this->authorize('approve', $overtimeRequest);

        $approver = $request->user()->employee;
        if (! $approver) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $updated = $this->overtimeService->approveRequest($overtimeRequest, $approver, $request->input('decision_note'));

        return response()->json(['success' => true, 'data' => $updated]);
    }

    public function reject(Request $request, OvertimeRequest $overtimeRequest): JsonResponse
    {
        $this->authorize('reject', $overtimeRequest);

        $request->validate(['decision_note' => ['required', 'string']]);

        $approver = $request->user()->employee;
        if (! $approver) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $updated = $this->overtimeService->rejectRequest($overtimeRequest, $approver, $request->input('decision_note'));

        return response()->json(['success' => true, 'data' => $updated]);
    }

    public function cancel(OvertimeRequest $overtimeRequest): JsonResponse
    {
        $this->authorize('cancel', $overtimeRequest);

        $updated = $this->overtimeService->cancelRequest($overtimeRequest);

        return response()->json(['success' => true, 'data' => $updated]);
    }
}
