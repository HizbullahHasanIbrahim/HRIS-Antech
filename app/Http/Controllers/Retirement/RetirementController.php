<?php

declare(strict_types=1);

namespace App\Http\Controllers\Retirement;

use App\Http\Controllers\Controller;
use App\Models\Retirement;
use App\Services\Retirement\RetirementProcessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RetirementController extends Controller
{
    public function __construct(
        protected RetirementProcessService $retirementService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Retirement::class);

        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $query = Retirement::with('employee')
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($employee->id, fn ($q) => $q->where('employee_id', $employee->id));

        return response()->json(['success' => true, 'data' => $query->paginate()]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Retirement::class);

        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $request->validate([
            'retirement_age' => ['required', 'integer', 'min:55', 'max:65'],
            'planned_date' => ['required', 'date', 'after:today'],
            'notes' => ['nullable', 'string'],
        ]);

        $retirement = $this->retirementService->proposeRetirement(
            $employee,
            (int) $request->input('retirement_age'),
            new \DateTimeImmutable($request->input('planned_date')),
            $request->input('notes', ''),
            (int) $request->user()->id
        );

        return response()->json(['success' => true, 'data' => $retirement], 201);
    }

    public function show(Retirement $retirement): JsonResponse
    {
        $this->authorize('view', $retirement);

        return response()->json(['success' => true, 'data' => $retirement->load('employee')]);
    }

    public function process(Request $request, Retirement $retirement): JsonResponse
    {
        $processedBy = $request->user()->id;
        $retirement = $this->retirementService->processRetirement($retirement, $processedBy, $request->input('notes'));

        return response()->json(['success' => true, 'data' => $retirement]);
    }

    public function complete(Request $request, Retirement $retirement): JsonResponse
    {
        $request->validate([
            'sk_number' => ['required', 'string', 'max:100'],
            'sk_date' => ['required', 'date'],
        ]);

        $retirement = $this->retirementService->completeRetirement(
            $retirement,
            $request->input('sk_number'),
            new \DateTimeImmutable($request->input('sk_date'))
        );

        return response()->json(['success' => true, 'data' => $retirement]);
    }

    public function cancel(Request $request, Retirement $retirement): JsonResponse
    {
        $this->authorize('cancel', $retirement);

        $request->validate([
            'reason' => ['required', 'string'],
        ]);

        $retirement = $this->retirementService->cancelRetirement(
            $retirement,
            $request->user()->id,
            $request->input('reason')
        );

        return response()->json(['success' => true, 'data' => $retirement]);
    }
}
