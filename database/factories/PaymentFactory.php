<?php

namespace Database\Factories;

use App\Models\Alumni;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['unpaid', 'pending_verification', 'paid']);

        return [
            'alumni_id' => Alumni::factory(),
            'amount' => fake()->optional()->numberBetween(500000, 1500000),
            'status' => $status,
            'payment_date' => $status === 'unpaid' ? null : fake()->date(),
            'verified_by' => $status === 'paid' ? User::factory() : null,
            'verified_at' => $status === 'paid' ? fake()->dateTime() : null,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
