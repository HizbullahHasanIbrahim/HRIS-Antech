<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Department;
use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkScheduleFactory extends Factory
{
    protected $model = WorkSchedule::class;

    public function definition(): array
    {
        return [
            'unit_id' => Department::factory(),
            'category' => fake()->randomElement(['reguler', 'shift', 'fleksibel']),
            'valid_from' => now()->startOfYear()->toDateString(),
            'valid_until' => null,
            'is_published' => false,
            'published_at' => null,
            'created_by' => User::factory(),
        ];
    }
}
