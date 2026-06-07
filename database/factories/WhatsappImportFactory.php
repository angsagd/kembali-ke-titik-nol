<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WhatsappImport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WhatsappImport>
 */
class WhatsappImportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uploaded_by' => User::factory(),
            'file_name' => 'whatsapp-chat.txt',
            'file_path' => 'whatsapp-imports/'.fake()->uuid().'.txt',
            'import_start_date' => now()->subYears(2)->toDateString(),
            'import_end_date' => now()->toDateString(),
            'total_messages' => fake()->numberBetween(100, 5000),
            'total_participants' => fake()->numberBetween(5, 80),
            'status' => fake()->randomElement(['uploaded', 'completed']),
            'processed_at' => now(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
