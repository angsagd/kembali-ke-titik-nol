<?php

namespace Database\Factories;

use App\Models\Alumni;
use App\Models\AlumniTimeline;
use App\Models\City;
use App\Models\Country;
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
        $country = Country::factory();

        return [
            'alumni_id' => Alumni::factory(),
            'month' => fake()->optional()->numberBetween(1, 12),
            'year' => fake()->numberBetween(1996, 2026),
            'country_id' => $country,
            'city_id' => City::factory()->for($country),
            'location_source' => 'geocoded',
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
