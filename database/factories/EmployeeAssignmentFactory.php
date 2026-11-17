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
            'position_title' => fake('id_ID')->jobTitle(),
            'assignment_type' => 'struktural',
            'start_date' => now()->toDateString(),
            'end_date' => null,
            'sk_number' => 'SK/'.fake()->numerify('###/UAD/2026'),
            'is_active' => true,
        ];
    }
}
