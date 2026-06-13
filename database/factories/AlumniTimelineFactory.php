<?php

namespace Database\Factories;

use App\Models\Alumni;
use App\Models\AlumniTimeline;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AlumniTimeline>
 */
class AlumniTimelineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'alumni_id' => Alumni::factory(),
            'month' => fake()->optional()->numberBetween(1, 12),
            'year' => fake()->numberBetween(1996, 2026),
            'city' => fake()->city(),
            'country' => fake()->country(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'location_source' => 'geocoded',
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
