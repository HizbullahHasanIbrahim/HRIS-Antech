<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        $gender = fake()->randomElement(['L', 'P']);
        return [
            'nip' => fake()->unique()->numerify('1990########00#'),
            'nidn' => fake()->unique()->numerify('05########'),
            'full_name' => fake('id_ID')->name($gender === 'L' ? 'male' : 'female'),
            'category' => fake()->randomElement(['dosen', 'tendik']),
            'gender' => $gender,
            'birth_place' => fake('id_ID')->city(),
            'birth_date' => fake()->date('Y-m-d', '-25 years'),
            'email' => fake('id_ID')->unique()->safeEmail(),
            'phone' => fake('id_ID')->phoneNumber(),
            'address' => fake('id_ID')->address(),
            'highest_education' => fake()->randomElement(['S1', 'S2', 'S3']),
            'unit_id' => Department::factory(),
            'supervisor_id' => null,
            'application_id' => null,
            'employment_status' => 'aktif',
            'join_date' => fake()->date('Y-m-d', '-5 years'),
            'end_date' => null,
        ];
    }
}