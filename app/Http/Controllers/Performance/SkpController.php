<?php

declare(strict_types=1);

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Performance\SkpEvaluationRequest;
use App\Http\Requests\Performance\SkpTargetRequest;
use App\Models\PerformanceItem;
use App\Models\PerformanceTarget;
use App\Services\Performance\SkpEvaluationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SkpController extends Controller
{
    public function __construct(
        protected SkpEvaluationService $skpService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $this->authorize('viewAny', PerformanceTarget::class);

        $query = PerformanceTarget::with(['employee', 'items'])
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->input('period_label'), fn ($q, $p) => $q->where('period_label', $p));

        if (! $request->user()->hasRole('Admin BSDM') && ! $request->user()->hasRole('Kepala Biro') && ! $request->user()->hasRole('Kepala Bagian') && ! $request->user()->hasRole('Staff HR')) {
            $query->where('employee_id', $employee->id);
        }

        return response()->json(['success' => true, 'data' => $query->paginate()]);
    }

    public function storeTarget(SkpTargetRequest $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $this->authorize('create', PerformanceTarget::class);

        $target = $this->skpService->createTarget(
            $employee,
            $request->validated('period_label'),
            new \DateTimeImmutable($request->validated('period_start')),
            new \DateTimeImmutable($request->validated('period_end')),
            (int) $request->validated('appraiser_id'),
        );

        return response()->json(['success' => true, 'data' => $target], 201);
    }

    public function evaluate(SkpEvaluationRequest $request, PerformanceTarget $target): JsonResponse
    {
        $this->authorize('evaluate', $target);

        $evaluator = $request->user()->employee;
        if (! $evaluator) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        foreach ($request->validated('performance_items') as $itemData) {
            PerformanceItem::create(array_merge($itemData, ['target_id' => $target->id]));
        }

        $updated = $this->skpService->evaluateTarget($target, $evaluator);

        return response()->json(['success' => true, 'data' => $updated]);
    }

    public function reject(Request $request, PerformanceTarget $target): JsonResponse
    {
        $this->authorize('reject', $target);

        $request->validate(['decision_note' => ['required', 'string']]);

        $evaluator = $request->user()->employee;
        if (! $evaluator) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $updated = $this->skpService->rejectTarget($target, $evaluator, $request->input('decision_note'));

        return response()->json(['success' => true, 'data' => $updated]);
    }
}
