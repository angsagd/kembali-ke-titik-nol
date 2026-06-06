<?php

namespace Database\Factories;

use App\Models\Alumni;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Donation>
 */
class DonationFactory extends Factory
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
            'amount' => fake()->optional()->numberBetween(100000, 5000000),
            'publication_status' => fake()->randomElement(['show_name', 'anonymous']),
            'notes' => fake()->optional()->sentence(),
            'managed_by' => User::factory(),
        ];
    }
}
