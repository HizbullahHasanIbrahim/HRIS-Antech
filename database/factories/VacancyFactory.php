<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Department;
use App\Models\Vacancy;
use Illuminate\Database\Eloquent\Factories\Factory;

class VacancyFactory extends Factory
{
    protected $model = Vacancy::class;

    public function definition(): array
    {
        return [
            'unit_id' => Department::factory(),
            'title' => 'Rekrutmen Dosen / Tenaga Kependidikan '.fake()->jobTitle(),
            'position_type' => fake()->randomElement(['dosen', 'tendik']),
            'quota' => fake()->numberBetween(1, 5),
            'open_date' => now()->toDateString(),
            'close_date' => now()->addMonth()->toDateString(),
            'status' => 'buka',
        ];
    }
}
