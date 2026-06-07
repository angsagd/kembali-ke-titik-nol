<?php

namespace Database\Factories;

use App\Models\Alumni;
use App\Models\WhatsappImport;
use App\Models\WhatsappStatistic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WhatsappStatistic>
 */
class WhatsappStatisticFactory extends Factory
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
            'category' => fake()->randomElement(['active_member', 'link_poster', 'word_cloud']),
            'label' => fake()->name(),
            'alumni_id' => fake()->optional()->randomElement([Alumni::factory(), null]),
            'value' => fake()->numberBetween(1, 1000),
            'rank' => fake()->numberBetween(1, 10),
            'metadata' => null,
        ];
    }
}
