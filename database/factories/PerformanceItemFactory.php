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
            'activity_name' => fake('id_ID')->sentence(4),
            'target_quantity' => 10,
            'actual_quantity' => 10,
            'target_quality' => 100.00,
            'actual_quality' => 90.00,
            'score' => 90.00,
            'weight' => 25.00,
        ];
    }
}
