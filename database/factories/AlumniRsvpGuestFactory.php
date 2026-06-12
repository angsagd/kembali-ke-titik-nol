<?php

namespace Database\Factories;

use App\Models\Alumni;
use App\Models\AlumniRsvpGuest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AlumniRsvpGuest>
 */
class AlumniRsvpGuestFactory extends Factory
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
            'sequence' => fake()->numberBetween(1, 5),
            'shirt_size' => fake()->randomElement(['S', 'M', 'L', 'XL', 'XXL']),
            'shirt_type' => fake()->randomElement(['child', 'male', 'female']),
        ];
    }
}
