<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['superadmin', 'administrator', 'bendahara', 'alumni']),
            'description' => fake()->sentence(),
        ];
    }

    /**
     * Indicate that the role is an alumni role.
     */
    public function alumni(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'alumni',
            'description' => 'Anggota alumni',
        ]);
    }
}
