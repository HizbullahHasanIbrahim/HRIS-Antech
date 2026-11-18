<?php

declare(strict_types=1);

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeAssignment;
use App\Services\Organization\EmployeeAssignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeAssignmentController extends Controller
{
    public function __construct(
        protected EmployeeAssignmentService $assignmentService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', EmployeeAssignment::class);

        $query = EmployeeAssignment::with(['employee', 'unit'])
            ->when($request->input('employee_id'), fn ($q, $e) => $q->where('employee_id', $e))
            ->when($request->input('category'), fn ($q, $c) => $q->where('category', $c));

        return response()->json(['success' => true, 'data' => $query->paginate()]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', EmployeeAssignment::class);

        $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'unit_id' => ['required', 'integer', 'exists:departments,id'],
            'category' => ['required', 'string', 'max:10'],
            'effective_from' => ['required', 'date'],
            'supervisor_id' => ['nullable', 'integer', 'exists:employees,id'],
            'effective_until' => ['nullable', 'date'],
        ]);

        $employee = Employee::findOrFail($request->input('employee_id'));
        $assignment = $this->assignmentService->assign(
            $employee,
            (int) $request->input('unit_id'),
            $request->input('category'),
            new \DateTimeImmutable($request->input('effective_from')),
            $request->input('supervisor_id'),
            $request->input('effective_until') ? new \DateTimeImmutable($request->input('effective_until')) : null
        );

        return response()->json(['success' => true, 'data' => $assignment], 201);
    }

    public function terminate(Request $request, EmployeeAssignment $assignment): JsonResponse
    {
        $this->authorize('update', $assignment);

        $request->validate(['effective_until' => ['required', 'date']]);

        $this->assignmentService->terminate($assignment, new \DateTimeImmutable($request->input('effective_until')));

        return response()->json(['success' => true, 'message' => 'Assignment terminated.']);
    }
}
