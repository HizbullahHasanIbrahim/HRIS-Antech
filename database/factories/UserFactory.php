<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'employee_id' => null,
            'username' => fake()->unique()->userName(),
            'password_hash' => Hash::make('password'),
            'is_active' => true,
            'last_login_at' => now(),
        ];
    }
}