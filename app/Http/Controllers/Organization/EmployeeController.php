<?php

declare(strict_types=1);

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Employee::with(['user', 'department', 'supervisor'])
            ->when($request->input('unit_id'), fn ($q, $u) => $q->where('unit_id', $u))
            ->when($request->input('status'), fn ($q, $s) => $q->where('employment_status', $s))
            ->when($request->input('search'), fn ($q, $s) => $q->where('full_name', 'like', "%{$s}%")
                ->orWhere('nip', 'like', "%{$s}%"));

        return response()->json(['success' => true, 'data' => $query->paginate()]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nip' => ['required', 'string', 'max:30', 'unique:employees,nip'],
            'full_name' => ['required', 'string', 'max:150'],
            'category' => ['required', 'string', 'in:dosen,tendik'],
            'gender' => ['required', 'string', 'in:L,P'],
            'birth_place' => ['required', 'string', 'max:100'],
            'birth_date' => ['required', 'date'],
            'email' => ['required', 'email', 'max:150', 'unique:employees,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'highest_education' => ['required', 'string', 'max:10'],
            'unit_id' => ['required', 'integer', 'exists:departments,id'],
            'supervisor_id' => ['nullable', 'integer', 'exists:employees,id'],
            'employment_status' => ['required', 'string', 'in:aktif,nonaktif,pensiun'],
            'join_date' => ['required', 'date'],
        ]);

        $employee = Employee::create($request->validated());

        return response()->json(['success' => true, 'data' => $employee], 201);
    }

    public function show(Employee $employee): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $employee->load(['user', 'department', 'supervisor'])]);
    }

    public function update(Request $request, Employee $employee): JsonResponse
    {
        $request->validate([
            'full_name' => ['sometimes', 'string', 'max:150'],
            'category' => ['sometimes', 'string', 'in:dosen,tendik'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'highest_education' => ['sometimes', 'string', 'max:10'],
            'unit_id' => ['sometimes', 'integer', 'exists:departments,id'],
            'supervisor_id' => ['nullable', 'integer', 'exists:employees,id'],
            'employment_status' => ['sometimes', 'string', 'in:aktif,nonaktif,pensiun'],
        ]);

        $employee->update($request->validated());

        return response()->json(['success' => true, 'data' => $employee->fresh()]);
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $employee->delete();

        return response()->json(['success' => true, 'message' => 'Employee deleted.']);
    }
}
