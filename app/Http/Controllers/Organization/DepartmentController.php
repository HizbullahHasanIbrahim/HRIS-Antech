<?php

declare(strict_types=1);

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Department::with('parent')
            ->when($request->input('unit_type'), fn ($q, $t) => $q->where('unit_type', $t))
            ->when($request->input('search'), fn ($q, $s) => $q->where('name', 'like', "%{$s}%"));

        return response()->json(['success' => true, 'data' => $query->paginate()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'unit_type' => ['required', 'string', 'in:unit,bidang,bagian'],
            'parent_id' => ['nullable', 'integer', 'exists:departments,id'],
            'is_active' => ['boolean'],
        ]);

        $dept = Department::create($validated);

        return response()->json(['success' => true, 'data' => $dept], 201);
    }

    public function update(Request $request, Department $department): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'unit_type' => ['sometimes', 'string', 'in:unit,bidang,bagian'],
            'parent_id' => ['nullable', 'integer', 'exists:departments,id'],
            'is_active' => ['boolean'],
        ]);

        $department->update($validated);

        return response()->json(['success' => true, 'data' => $department->fresh()]);
    }

    public function destroy(Department $department): JsonResponse
    {
        $department->delete();

        return response()->json(['success' => true, 'message' => 'Department deleted.']);
    }
}
