<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Applicant;
use App\Models\Application;
use App\Models\Vacancy;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    public function definition(): array
    {
        return [
            'vacancy_id' => Vacancy::factory(),
            'applicant_id' => Applicant::factory(),
            'status' => 'diajukan',
            'applied_at' => now(),
        ];
    }
}
