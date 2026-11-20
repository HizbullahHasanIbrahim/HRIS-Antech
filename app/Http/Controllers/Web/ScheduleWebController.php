<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\WorkSchedule;
use App\Models\WorkScheduleDay;
use App\Services\Audit\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ScheduleWebController extends Controller
{
    public function __construct(
        protected ActivityLogService $activityLog
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $employee = $user?->employee;

        $isAdminOrHead = $user?->hasRole('Admin BSDM') || $user?->hasRole('Kepala Biro');

        $query = WorkSchedule::with(['days', 'unit'])
            ->when(! $isAdminOrHead && $employee, fn ($q) => $q->where('unit_id', $employee->unit_id))
            ->latest('valid_from');

        $schedules = $query->paginate(10)->withQueryString();

        $dayNames = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        return Inertia::render('Schedules/Index', [
            'schedules' => array_merge($schedules->toArray(), [
                'data' => $schedules->getCollection()->map(fn ($s) => [
                    'id' => $s->id,
                    'unit_name' => $s->unit?->name ?? 'Semua Unit',
                    'category' => $s->category,
                    'valid_from' => $s->valid_from?->format('d/m/Y'),
                    'valid_until' => $s->valid_until?->format('d/m/Y'),
                    'is_published' => $s->is_published,
                    'days' => $s->days->map(fn ($d) => [
                        'day_name' => $dayNames[$d->day_of_week] ?? 'Hari '.$d->day_of_week,
                        'start_time' => substr((string) $d->start_time, 0, 5),
                        'end_time' => substr((string) $d->end_time, 0, 5),
                    ]),
                ])->values(),
            ]),
            'departments' => Department::where('is_active', true)->select(['id', 'name'])->orderBy('name')->get(),
            'isAdminOrHead' => $isAdminOrHead,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('Admin BSDM') && ! $user->hasRole('Kepala Biro')) {
            abort(403, 'Hanya Admin BSDM dan Kepala Biro yang berhak mengatur jadwal kerja.');
        }

        $validated = $request->validate([
            'unit_id' => 'nullable|exists:departments,id',
            'category' => 'required|string|in:dosen,tendik',
            'valid_from' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_published' => 'boolean',
            'days' => 'required|array|min:1',
            'days.*.day_of_week' => 'required|integer|between:1,7',
            'days.*.start_time' => 'required|string',
            'days.*.end_time' => 'required|string',
        ]);

        DB::transaction(function () use ($validated, $user) {
            $schedule = WorkSchedule::create([
                'unit_id' => $validated['unit_id'] ?? null,
                'category' => $validated['category'],
                'valid_from' => $validated['valid_from'],
                'valid_until' => $validated['valid_until'] ?? null,
                'is_published' => $validated['is_published'] ?? false,
                'published_at' => ($validated['is_published'] ?? false) ? now() : null,
                'created_by' => $user->id,
            ]);

            foreach ($validated['days'] as $day) {
                WorkScheduleDay::create([
                    'schedule_id' => $schedule->id,
                    'day_of_week' => $day['day_of_week'],
                    'start_time' => $day['start_time'],
                    'end_time' => $day['end_time'],
                ]);
            }

            $this->activityLog->log(
                $user->id,
                'CREATE_WORK_SCHEDULE',
                'WorkSchedule',
                $schedule->id,
                null,
                ($validated['is_published'] ?? false) ? 'dipublikasi' : 'draf',
            );
        });

        return back()->with('success', 'Jadwal kerja berhasil dibuat.');
    }
}
