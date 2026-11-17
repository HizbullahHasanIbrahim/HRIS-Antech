<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Employee;
use App\Models\WorkloadReport;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkloadReportFactory extends Factory
{
    protected $model = WorkloadReport::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'academic_year' => '2025/2026',
            'semester' => 'gasal',
            'teaching_credit' => 6.00,
            'research_credit' => 3.00,
            'service_credit' => 2.00,
            'support_credit' => 1.00,
            'status' => 'disetujui',
            'validator_id' => Employee::factory(),
            'validated_at' => now(),
            'version' => 1,
            'is_current' => true,
            'revision_reason' => null,
        ];
    }
}
