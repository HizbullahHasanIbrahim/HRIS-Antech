<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PerformanceItem;
use App\Models\PerformanceTarget;
use Illuminate\Database\Eloquent\Factories\Factory;

class PerformanceItemFactory extends Factory
{
    protected $model = PerformanceItem::class;

    public function definition(): array
    {
        return [
            'target_id' => PerformanceTarget::factory(),
            'description' => fake('id_ID')->sentence(4),
            'unit' => fake()->randomElement(['orang', 'benda', 'program', 'acara']),
            'target_value' => fake()->randomFloat(2, 50, 100),
            'realization_value' => null,
            'weight' => fake()->randomFloat(2, 10, 30),
            'score' => null,
        ];
    }
}
