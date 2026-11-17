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

    public function storeTarget(SkpTargetRequest $request): JsonResponse
    {
        $employee = $request->user()->employee;
        if (! $employee) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

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
        $request->validate(['decision_note' => ['required', 'string']]);

        $evaluator = $request->user()->employee;
        if (! $evaluator) {
            return response()->json(['message' => 'No employee profile.'], 422);
        }

        $updated = $this->skpService->rejectTarget($target, $evaluator, $request->input('decision_note'));

        return response()->json(['success' => true, 'data' => $updated]);
    }
}
