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
            'type' => fake()->unique()->randomElement(['Cuti', 'Izin', 'Libur']),
            'name' => fake()->randomElement(['Cuti Tahunan', 'Cuti Melahirkan', 'Cuti Sakit', 'Cuti Alasan Penting', 'Cuti Besar']),
            'annual_quota' => fake()->numberBetween(5, 30),
            'applies_to' => fake()->randomElement(['PNS', 'Honorer', 'Honorer Dosen', 'Honorer Pegawai Negeri Sipil', 'Honorer PNS']),
            'is_active' => true,
        ];
    }
}
