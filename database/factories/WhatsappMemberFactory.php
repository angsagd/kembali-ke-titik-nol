<?php

namespace Database\Factories;

use App\Models\Alumni;
use App\Models\WhatsappImport;
use App\Models\WhatsappMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WhatsappMember>
 */
class WhatsappMemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();

        return [
            'whatsapp_import_id' => WhatsappImport::factory(),
            'alumni_id' => null,
            'display_name' => $name,
            'normalized_name' => str($name)->lower()->replaceMatches('/[^a-z0-9]+/', '')->toString(),
            'first_message_at' => now()->subDays(5),
            'last_message_at' => now(),
            'total_messages' => fake()->numberBetween(1, 500),
            'total_words' => fake()->numberBetween(1, 5000),
            'total_characters' => fake()->numberBetween(10, 50000),
        ];
    }

    public function linkedToAlumni(?Alumni $alumni = null): static
    {
        return $this->state(fn (): array => [
            'alumni_id' => $alumni?->id ?? Alumni::factory(),
        ]);
    }
}
