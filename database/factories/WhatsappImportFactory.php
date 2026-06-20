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
            'group_name' => 'Alumni Geodesi 96',
            'file_name' => 'whatsapp-chat.txt',
            'file_path' => 'whatsapp-imports/'.fake()->uuid().'.txt',
            'timezone_source' => 'Asia/Makassar',
            'timezone_display' => 'Asia/Jakarta',
            'total_lines' => fake()->numberBetween(100, 5000),
            'total_activities' => fake()->numberBetween(100, 5000),
            'import_start_date' => now()->subYears(2)->toDateString(),
            'import_end_date' => now()->toDateString(),
            'total_messages' => fake()->numberBetween(100, 5000),
            'total_system_events' => fake()->numberBetween(0, 100),
            'total_participants' => fake()->numberBetween(5, 80),
            'total_words' => fake()->numberBetween(1000, 100000),
            'total_emoji_messages' => fake()->numberBetween(0, 500),
            'total_media_messages' => fake()->numberBetween(0, 500),
            'total_sticker_messages' => fake()->numberBetween(0, 500),
            'total_link_messages' => fake()->numberBetween(0, 500),
            'total_deleted_messages' => fake()->numberBetween(0, 50),
            'first_activity_at' => now()->subYears(2),
            'last_activity_at' => now(),
            'status' => fake()->randomElement(['uploaded', 'completed']),
            'processed_at' => now(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
