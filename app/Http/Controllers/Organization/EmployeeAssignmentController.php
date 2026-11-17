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
        $query = EmployeeAssignment::with(['employee', 'unit'])
            ->when($request->input('employee_id'), fn ($q, $e) => $q->where('employee_id', $e))
            ->when($request->input('is_active'), fn ($q, $a) => $q->where('is_active', $a));

        return response()->json(['success' => true, 'data' => $query->paginate()]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'unit_id' => ['required', 'integer', 'exists:departments,id'],
            'position_title' => ['required', 'string', 'max:100'],
            'assignment_type' => ['required', 'string', 'max:50'],
            'start_date' => ['required', 'date'],
            'sk_number' => ['nullable', 'string', 'max:100'],
        ]);

        $employee = Employee::findOrFail($request->input('employee_id'));
        $assignment = $this->assignmentService->assign(
            $employee,
            (int) $request->input('unit_id'),
            $request->input('position_title'),
            $request->input('assignment_type'),
            new \DateTimeImmutable($request->input('start_date')),
            $request->input('sk_number')
        );

        return response()->json(['success' => true, 'data' => $assignment], 201);
    }

    public function terminate(Request $request, EmployeeAssignment $assignment): JsonResponse
    {
        $request->validate(['end_date' => ['required', 'date']]);

        $this->assignmentService->terminate($assignment, new \DateTimeImmutable($request->input('end_date')));

        return response()->json(['success' => true, 'message' => 'Assignment terminated.']);
    }
}
