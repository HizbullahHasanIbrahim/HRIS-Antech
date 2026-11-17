<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Training;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainingFactory extends Factory
{
    protected $model = Training::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'retirement_id' => null,
            'title' => 'Pelatihan ' . fake()->words(3, true),
            'provider' => 'Lembaga Pengembangan Profesi',
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->subDays(3)->toDateString(),
            'hours' => 32,
            'status' => 'selesai',
            'certificate_path' => 'trainings/' . fake()->uuid() . '.pdf',
        ];
    }
}