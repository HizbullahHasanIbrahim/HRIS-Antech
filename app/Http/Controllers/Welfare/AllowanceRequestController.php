<?php

declare(strict_types=1);

namespace App\Http\Controllers\Welfare;

use App\Http\Controllers\Controller;
use App\Http\Requests\Welfare\AllowanceRequestRequest;
use App\Models\AllowanceRequest;
use App\Models\AllowanceType;
use App\Services\Welfare\AllowanceRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AllowanceRequestController extends Controller
{
    public function __construct(
        protected AllowanceRequestService $allowanceService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $query = AllowanceRequest::with('allowanceType')
            ->where('employee_id', $employee->id)
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s));

        return response()->json(['success' => true, 'data' => $query->paginate()]);
    }

    public function store(AllowanceRequestRequest $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $allowance = $this->allowanceService->createRequest(
            $employee,
            AllowanceType::findOrFail($request->validated('allowance_type_id')),
            $request->validated('period_label'),
            (float) $request->validated('amount'),
            now(),
            (int) $request->validated('approver_id')
        );

        return response()->json(['success' => true, 'data' => $allowance], 201);
    }

    public function approve(Request $request, AllowanceRequest $allowanceRequest): JsonResponse
    {
        $approver = $request->user()->employee;
        if (! $approver) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $allowanceRequest = $this->allowanceService->approveRequest($allowanceRequest, $approver);

        return response()->json(['success' => true, 'data' => $allowanceRequest]);
    }

    public function cancel(AllowanceRequest $allowanceRequest): JsonResponse
    {
        $this->allowanceService->cancelRequest($allowanceRequest);

        return response()->json(['success' => true, 'message' => 'Allowance request cancelled.']);
    }
}
