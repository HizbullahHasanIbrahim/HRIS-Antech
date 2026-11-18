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
            'type' => 'Cuti '.fake()->unique()->numberBetween(1, 999999),
            'name' => fake()->randomElement(['Cuti Tahunan', 'Cuti Melahirkan', 'Cuti Sakit', 'Cuti Alasan Penting', 'Cuti Besar']),
            'annual_quota' => fake()->numberBetween(5, 30),
            'applies_to' => fake()->randomElement(['PNS', 'Honorer', 'Dosen', 'Pegawai']),
            'is_active' => true,
        ];
    }
}
