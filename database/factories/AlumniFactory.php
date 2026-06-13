<?php

namespace Database\Factories;

use App\Models\Alumni;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alumni>
 */
class AlumniFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'student_number' => fake()->boolean(70) ? fake()->unique()->numerify('96#####') : null,
            'full_name' => fake()->name(),
            'nickname' => fake()->optional()->firstName(),
            'email' => fake()->optional()->safeEmail(),
            'city' => null,
            'country' => null,
            'latitude' => null,
            'longitude' => null,
            'alumni_status' => 'active',
            'rsvp_status' => 'pending',
            'brings_private_vehicle' => null,
            'is_profile_completed' => false,
        ];
    }
}
