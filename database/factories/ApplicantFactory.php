<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Applicant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicantFactory extends Factory
{
    protected $model = Applicant::class;

    public function definition(): array
    {
        return [
            'full_name' => fake('id_ID')->name(),
            'email' => fake('id_ID')->unique()->safeEmail(),
            'phone' => fake('id_ID')->phoneNumber(),
            'birth_date' => fake()->date('Y-m-d', '-22 years'),
            'highest_education' => fake()->randomElement(['S1', 'S2', 'S3']),
            'cv_path' => null,
        ];
    }
}
