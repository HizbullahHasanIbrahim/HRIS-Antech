<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Department;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Database\Eloquent\Factories\Factory;

class VacancyFactory extends Factory
{
    protected $model = Vacancy::class;

    public function definition(): array
    {
        return [
            'unit_id' => Department::factory(),
            'title' => 'Rekrutmen '.fake()->jobTitle(),
            'category' => fake()->randomElement(['dosen', 'tendik']),
            'description' => fake('id_ID')->paragraph(),
            'min_qualification' => fake()->randomElement(['S2', 'S3']),
            'quota' => fake()->numberBetween(1, 5),
            'open_date' => now()->toDateString(),
            'close_date' => now()->addMonth()->toDateString(),
            'status' => 'draf',
            'created_by' => User::factory(),
        ];
    }
}
