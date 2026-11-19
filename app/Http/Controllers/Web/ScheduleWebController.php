<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ScheduleWebController extends Controller
{
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
            'isAdminOrHead' => $isAdminOrHead,
        ]);
    }
}
