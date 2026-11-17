<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AllowanceRequest;
use App\Models\AllowanceType;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class AllowanceRequestFactory extends Factory
{
    protected $model = AllowanceRequest::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'allowance_type_id' => AllowanceType::factory(),
            'period_label' => date('Y-m'),
            'amount' => 1500000.00,
            'status' => 'diajukan',
            'approver_id' => Employee::factory(),
            'submitted_at' => now(),
            'decided_at' => null,
            'decision_note' => null,
            'version' => 1,
        ];
    }
}