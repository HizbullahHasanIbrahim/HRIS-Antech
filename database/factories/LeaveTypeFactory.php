<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveTypeFactory extends Factory
{
    protected $model = LeaveType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Cuti Tahunan', 'Cuti Melahirkan', 'Cuti Sakit', 'Cuti Alasan Penting', 'Cuti Besar']),
            'default_quota' => 12,
            'is_quota_based' => true,
            'requires_document' => false,
        ];
    }
}
