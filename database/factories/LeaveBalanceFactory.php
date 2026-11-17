<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveBalanceFactory extends Factory
{
    protected $model = LeaveBalance::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'leave_type_id' => LeaveType::factory(),
            'year' => (int) date('Y'),
            'quota_amount' => 12,
            'used_amount' => 0,
            'remaining_amount' => 12,
            'version' => 1,
        ];
    }
}
