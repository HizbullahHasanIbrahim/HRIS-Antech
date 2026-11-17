<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Retirement;
use Illuminate\Database\Eloquent\Factories\Factory;

class RetirementFactory extends Factory
{
    protected $model = Retirement::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'retirement_age' => fake()->randomElement([58, 60, 65]),
            'planned_date' => now()->addMonths(6)->toDateString(),
            'proposed_at' => now(),
            'status' => 'diusulkan',
            'processed_by' => null,
            'sk_number' => null,
            'sk_date' => null,
            'completed_at' => null,
            'notes' => fake('id_ID')->sentence(),
            'version' => 1,
        ];
    }
}
