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
            'status' => 'disetujui',
            'appraiser_id' => Employee::factory(),
            'submitted_at' => now()->subMonth(),
            'approved_at' => now(),
            'final_score' => 88.50,
            'evaluation_summary' => fake('id_ID')->paragraph(),
            'version' => 1,
        ];
    }
}
