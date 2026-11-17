<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'leave_type_id' => LeaveType::factory(),
            'start_date' => now()->addDays(2)->toDateString(),
            'end_date' => now()->addDays(4)->toDateString(),
            'total_days' => 3,
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