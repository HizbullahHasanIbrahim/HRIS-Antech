<?php

declare(strict_types=1);

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\WorkScheduleRequest;
use App\Models\WorkSchedule;
use App\Services\Attendance\WorkScheduleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkScheduleController extends Controller
{
    public function __construct(
        protected WorkScheduleService $scheduleService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = WorkSchedule::with('days')
            ->when($request->input('type'), fn ($q, $t) => $q->where('schedule_type', $t));

        return response()->json(['success' => true, 'data' => $query->paginate()]);
    }

    public function store(WorkScheduleRequest $request): JsonResponse
    {
        $schedule = $this->scheduleService->createSchedule(
            $request->validated('name'),
            $request->validated('schedule_type'),
            new \DateTimeImmutable($request->validated('effective_start')),
            new \DateTimeImmutable($request->validated('effective_end')),
            $request->validated('days')
        );

        return response()->json(['success' => true, 'data' => $schedule], 201);
    }

    public function destroy(WorkSchedule $workSchedule): JsonResponse
    {
        $this->scheduleService->deleteSchedule($workSchedule);

        return response()->json(['success' => true, 'message' => 'Work schedule deleted.']);
    }

    public function assign(Request $request, WorkSchedule $workSchedule): JsonResponse
    {
        $request->validate(['employee_id' => ['required', 'integer', 'exists:employees,id']]);

        $this->scheduleService->assignSchedule((int) $request->input('employee_id'), $workSchedule->id);

        return response()->json(['success' => true, 'message' => 'Schedule assigned successfully.']);
    }
}
