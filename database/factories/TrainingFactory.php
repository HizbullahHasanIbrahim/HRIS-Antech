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
            'title' => 'Pelatihan '.fake()->words(3, true),
            'training_type' => fake()->randomElement(['prajabatan', 'teknis', 'kepemimpinan', 'fungsional']),
            'organizer' => 'Lembaga Pengembangan Profesi',
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->subDays(3)->toDateString(),
            'duration_hours' => 32,
            'status' => 'selesai',
            'certificate_number' => 'CERT-TRN/'.fake()->numerify('###/2026'),
            'certificate_path' => 'trainings/'.fake()->uuid().'.pdf',
        ];
    }
}
