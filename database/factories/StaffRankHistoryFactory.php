<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Employee;
use App\Models\StaffRankHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffRankHistoryFactory extends Factory
{
    protected $model = StaffRankHistory::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'rank_level' => fake()->randomElement(['Pengatur Muda / II A', 'Pengatur / II C', 'Penata Muda / III A', 'Penata / III C']),
            'status' => 'disetujui',
            'submitted_at' => now()->subMonth(),
            'approver_id' => Employee::factory(),
            'decided_at' => now(),
            'effective_date' => now()->toDateString(),
            'sk_number' => 'SK-PANGKAT/'.fake()->numerify('###/2026'),
            'sk_date' => now()->toDateString(),
            'decision_note' => fake('id_ID')->sentence(),
            'version' => 1,
        ];
    }
}
