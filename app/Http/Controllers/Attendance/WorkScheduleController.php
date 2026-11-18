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
        $this->authorize('viewAny', WorkSchedule::class);

        $query = WorkSchedule::with('days')
            ->when($request->input('category'), fn ($q, $c) => $q->where('category', $c));

        return response()->json(['success' => true, 'data' => $query->paginate()]);
    }

    public function store(WorkScheduleRequest $request): JsonResponse
    {
        $this->authorize('create', WorkSchedule::class);

        $schedule = $this->scheduleService->createSchedule(
            $request->validated('category'),
            new \DateTimeImmutable($request->validated('valid_from')),
            new \DateTimeImmutable($request->validated('valid_until')),
            array_map(function ($day) {
                return [
                    'day_of_week' => array_search($day['day_of_week'], ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']) + 1,
                    'start_time' => $day['start_time'],
                    'end_time' => $day['end_time'],
                    'break_start' => $day['break_start'] ?? null,
                    'break_end' => $day['break_end'] ?? null,
                ];
            }, $request->validated('days')),
            $request->validated('is_published'),
            new \DateTimeImmutable(),
            (int) $request->user()->id
        );

        return response()->json(['success' => true, 'data' => $schedule], 201);
    }

    public function destroy(WorkSchedule $workSchedule): JsonResponse
    {
        $this->authorize('delete', $workSchedule);

        $this->scheduleService->deleteSchedule($workSchedule);

        return response()->json(['success' => true, 'message' => 'Work schedule deleted.']);
    }

    public function assign(Request $request, WorkSchedule $workSchedule): JsonResponse
    {
        $this->authorize('assign', $workSchedule);

        $request->validate(['employee_id' => ['required', 'integer', 'exists:employees,id']]);

        $this->scheduleService->assignSchedule((int) $request->input('employee_id'), $workSchedule->id);

        return response()->json(['success' => true, 'message' => 'Schedule assigned successfully.']);
    }
}