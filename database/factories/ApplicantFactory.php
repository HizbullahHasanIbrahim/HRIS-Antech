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
            'nik' => fake('id_ID')->unique()->numerify('3404############'),
        ];
    }
}