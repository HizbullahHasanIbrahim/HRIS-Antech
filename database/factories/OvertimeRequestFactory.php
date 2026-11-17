<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Employee;
use App\Models\OvertimeRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class OvertimeRequestFactory extends Factory
{
    protected $model = OvertimeRequest::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'work_date' => now()->toDateString(),
            'start_time' => '17:00:00',
            'end_time' => '20:00:00',
            'total_hours' => 3.00,
            'reason' => fake('id_ID')->sentence(),
            'status' => 'diajukan',
            'approver_id' => Employee::factory(),
            'submitted_at' => now(),
            'decided_at' => null,
            'decision_note' => null,
            'version' => 1,
        ];
    }
}
