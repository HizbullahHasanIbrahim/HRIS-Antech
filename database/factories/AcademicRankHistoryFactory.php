<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AcademicRankHistory;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicRankHistoryFactory extends Factory
{
    protected $model = AcademicRankHistory::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'rank_name' => fake()->randomElement(['Asisten Ahli', 'Lektor', 'Lektor Kepala', 'Guru Besar']),
            'credit_points' => fake()->randomFloat(2, 100, 850),
            'is_accelerated' => false,
            'status' => 'disetujui',
            'submitted_at' => now()->subMonth(),
            'approver_id' => Employee::factory(),
            'decided_at' => now(),
            'effective_date' => now()->toDateString(),
            'sk_number' => 'SK-JAFUNG/'.fake()->numerify('###/2026'),
            'sk_date' => now()->toDateString(),
            'decision_note' => fake('id_ID')->sentence(),
            'version' => 1,
        ];
    }
}
