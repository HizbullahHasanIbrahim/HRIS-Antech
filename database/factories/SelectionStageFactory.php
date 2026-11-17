<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Application;
use App\Models\SelectionStage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SelectionStageFactory extends Factory
{
    protected $model = SelectionStage::class;

    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'stage_name' => fake()->randomElement(['seleksi_berkas', 'tes_kompetensi', 'wawancara', 'aipt_microteaching']),
            'score' => fake()->randomFloat(2, 60, 100),
            'status' => 'lulus',
            'scheduled_date' => now()->toDateString(),
            'assessed_by' => User::factory(),
            'assessed_at' => now(),
            'notes' => fake('id_ID')->sentence(),
        ];
    }
}
