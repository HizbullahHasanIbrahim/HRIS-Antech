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
            'code' => 'TUK-'.fake()->unique()->numerify('###'),
            'name' => fake()->unique()->randomElement(['Tunjangan Jabatan', 'Tunjangan Fungsional', 'Tunjangan Kehadiran', 'Tunjangan Kinerja']),
            'requires_doctorate' => false,
            'period_type' => 'bulanan',
            'description' => fake('id_ID')->sentence(),
            'is_active' => true,
        ];
    }
}
