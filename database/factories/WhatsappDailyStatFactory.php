<?php

namespace Database\Factories;

use App\Models\WhatsappDailyStat;
use App\Models\WhatsappImport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WhatsappDailyStat>
 */
class WhatsappDailyStatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'whatsapp_import_id' => WhatsappImport::factory(),
            'stat_date' => fake()->date(),
            'total_activities' => fake()->numberBetween(1, 200),
            'total_messages' => fake()->numberBetween(1, 200),
            'total_system_events' => fake()->numberBetween(0, 20),
            'total_media' => fake()->numberBetween(0, 20),
            'total_links' => fake()->numberBetween(0, 20),
            'total_emojis' => fake()->numberBetween(0, 20),
            'total_deleted' => fake()->numberBetween(0, 5),
        ];
    }
}
