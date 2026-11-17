<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    public function definition(): array
    {
        return [
            'actor_id' => User::factory(),
            'action' => 'CREATE',
            'entity_type' => 'Employee',
            'entity_id' => fake()->numberBetween(1, 100),
            'old_status' => null,
            'new_status' => 'aktif',
            'reason' => 'Pencatatan awal',
            'details' => ['source' => 'web_portal'],
            'ip_address' => fake()->ipv4(),
            'created_at' => now(),
        ];
    }
}