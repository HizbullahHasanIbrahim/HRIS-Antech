<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AllowanceType;
use Illuminate\Database\Eloquent\Factories\Factory;

class AllowanceTypeFactory extends Factory
{
    protected $model = AllowanceType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Tunjangan Jabatan', 'Tunjangan Fungsional', 'Tunjangan Kehadiran', 'Tunjangan Kinerja']),
            'category' => fake()->randomElement(['fungsional', 'struktural', 'umum']),
            'is_active' => true,
        ];
    }
}
