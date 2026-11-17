<?php

declare(strict_types=1);

namespace App\Http\Controllers\Welfare;

use App\Http\Controllers\Controller;
use App\Http\Requests\Welfare\BpjsMembershipRequest;
use App\Models\BpjsMembership;
use App\Services\Welfare\BpjsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BpjsController extends Controller
{
    public function __construct(
        protected BpjsService $bpjsService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        // Mask membership numbers for privacy (RULE-06)
        $query = BpjsMembership::where('employee_id', $employee->id);

        if ($request->input('status')) {
            $query->where('status', $request->input('status'));
        }

        $memberships = $query->get();

        // Mask membership numbers in response
        $memberships->each(function ($membership) {
            $membership->membership_number = substr($membership->membership_number, -4) === mb_substr($membership->membership_number, -4)
                ? '********'.substr($membership->membership_number, -4)
                : '********';
        });

        return response()->json(['success' => true, 'data' => $memberships]);
    }

    public function store(BpjsMembershipRequest $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $membership = $this->bpjsService->registerMembership(
            $employee,
            $request->validated('bpjs_type'),
            $request->validated('membership_number'),
            new \DateTimeImmutable($request->validated('registered_date')),
        );

        // Mask in response
        $membership->membership_number = '********'.substr($membership->membership_number, -4);

        return response()->json(['success' => true, 'data' => $membership], 201);
    }

    public function cancel(BpjsMembership $membership): JsonResponse
    {
        $this->bpjsService->cancelMembership($membership);

        return response()->json(['success' => true, 'message' => 'Membership cancelled.']);
    }
}
