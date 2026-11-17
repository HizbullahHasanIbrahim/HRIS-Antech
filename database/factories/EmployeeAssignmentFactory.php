<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeAssignmentFactory extends Factory
{
    protected $model = EmployeeAssignment::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'unit_id' => Department::factory(),
            'supervisor_id' => null,
            'category' => fake()->randomElement(['struktural', 'fungsional', 'pelaksana']),
            'effective_from' => now()->toDateString(),
            'effective_until' => null,
        ];
    }
}
