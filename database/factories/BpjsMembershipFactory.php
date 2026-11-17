<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BpjsMembership;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class BpjsMembershipFactory extends Factory
{
    protected $model = BpjsMembership::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'bpjs_type' => fake()->randomElement(['kesehatan', 'ketenagakerjaan']),
            'membership_number' => fake()->unique()->numerify('0001########'),
            'registered_date' => now()->subYears(2)->toDateString(),
            'status' => 'aktif',
            'end_date' => null,
            'archived_at' => null,
        ];
    }
}
