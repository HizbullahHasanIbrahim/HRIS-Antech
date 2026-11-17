<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Employee;
use App\Models\StudyLeave;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudyLeaveFactory extends Factory
{
    protected $model = StudyLeave::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'degree_level' => fake()->randomElement(['S2', 'S3', 'Postdoc']),
            'university' => fake()->randomElement(['Universitas Gadjah Mada', 'Institut Teknologi Bandung', 'Universitas Ahmad Dahlan', 'Monash University']),
            'study_program' => 'Informatika',
            'start_date' => now()->toDateString(),
            'expected_end_date' => now()->addYears(3)->toDateString(),
            'actual_end_date' => null,
            'status' => 'direncanakan',
            'scholarship_name' => null,
            'scholarship_status' => 'belum',
            'mentoring_notes' => null,
        ];
    }
}
