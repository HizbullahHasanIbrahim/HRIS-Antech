<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\WorkSchedule;
use App\Models\WorkScheduleDay;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkScheduleDayFactory extends Factory
{
    protected $model = WorkScheduleDay::class;

    public function definition(): array
    {
        return [
            'schedule_id' => WorkSchedule::factory(),
            'day_of_week' => fake()->numberBetween(1, 5),
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
            'is_work_day' => true,
        ];
    }
}