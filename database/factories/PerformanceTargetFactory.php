<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Employee;
use App\Models\PerformanceTarget;
use Illuminate\Database\Eloquent\Factories\Factory;

class PerformanceTargetFactory extends Factory
{
    protected $model = PerformanceTarget::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'period_label' => '2026',
            'period_start' => now()->startOfYear()->toDateString(),
            'period_end' => now()->endOfYear()->toDateString(),
            'status' => 'draf',
            'appraiser_id' => Employee::factory(),
            'final_score' => null,
            'rating' => null,
            'approved_at' => null,
            'version' => 1,
        ];
    }
}
