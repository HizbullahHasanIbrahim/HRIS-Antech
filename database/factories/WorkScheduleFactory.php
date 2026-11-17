<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\WorkSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkScheduleFactory extends Factory
{
    protected $model = WorkSchedule::class;

    public function definition(): array
    {
        return [
            'name' => 'Jadwal Reguler BSDM 5 Hari',
            'schedule_type' => 'reguler',
            'effective_start' => now()->startOfYear()->toDateString(),
            'effective_end' => null,
            'is_active' => true,
        ];
    }
}
