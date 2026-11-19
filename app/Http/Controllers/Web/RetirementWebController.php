<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Retirement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RetirementWebController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $employee = $user?->employee;

        $isAdminOrHead = $user?->hasRole('Admin BSDM') || $user?->hasRole('Kepala Biro');

        $query = Retirement::with('employee.department')
            ->when(! $isAdminOrHead && $employee, fn ($q) => $q->where('employee_id', $employee->id))
            ->latest('planned_date');

        $retirements = $query->paginate(15)->withQueryString();

        return Inertia::render('Retirement/Index', [
            'retirements' => array_merge($retirements->toArray(), [
                'data' => $retirements->getCollection()->map(fn ($r) => [
                    'id' => $r->id,
                    'employee' => $r->employee?->full_name ?? '-',
                    'nip' => $r->employee?->nip ?? '-',
                    'department' => $r->employee?->department?->name ?? '-',
                    'retirement_age' => $r->retirement_age,
                    'planned_date' => $r->planned_date?->format('d/m/Y'),
                    'proposed_at' => $r->proposed_at?->format('d/m/Y H:i'),
                    'status' => $r->status,
                    'sk_number' => $r->sk_number,
                    'notes' => $r->notes,
                ])->values(),
            ]),
            'isAdminOrHead' => $isAdminOrHead,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'planned_date' => 'required|date',
            'retirement_age' => 'required|integer|min:50|max:75',
            'notes' => 'nullable|string',
        ]);

        $this->authorize('create', Retirement::class);

        Retirement::create([
            'employee_id' => $request->employee_id,
            'retirement_age' => $request->retirement_age,
            'planned_date' => $request->planned_date,
            'proposed_at' => now(),
            'status' => 'diajukan',
            'notes' => $request->notes,
            'version' => 1,
        ]);

        return back()->with('success', 'Usulan pensiun berhasil diajukan.');
    }
}
