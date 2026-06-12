<?php

namespace Database\Factories;

use App\Models\EventScheduleItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventScheduleItem>
 */
class EventScheduleItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_day' => fake()->randomElement(['day_one', 'day_two']),
            'start_time' => fake()->time('H:i'),
            'activity' => fake()->sentence(4),
        ];
    }
}
