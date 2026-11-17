<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Retirement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RetirementFactory extends Factory
{
    protected $model = Retirement::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'pension_type' => fake()->randomElement(['bup', 'dini', 'meninggal']),
            'proposed_date' => now()->toDateString(),
            'effective_date' => now()->addMonths(6)->toDateString(),
            'status' => 'diajukan',
            'sk_number' => null,
            'sk_date' => null,
            'reason' => fake('id_ID')->sentence(),
            'processed_by' => User::factory(),
            'version' => 1,
        ];
    }
}