<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'parent_id' => null,
            'name' => fake('id_ID')->unique()->words(2, true) . ' BSDM',
            'unit_type' => fake()->randomElement(['biro', 'bidang', 'urusan', 'unit']),
            'is_active' => true,
        ];
    }
}