<?php

namespace Database\Factories;

use App\Models\Alumni;
use App\Models\WhatsappMemberMapping;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WhatsappMemberMapping>
 */
class WhatsappMemberMappingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->name();

        return [
            'alumni_id' => null,
            'display_name' => $name,
            'normalized_name' => str($name)->lower()->replaceMatches('/[^a-z0-9]+/', '')->toString(),
            'notes' => null,
        ];
    }

    public function linkedToAlumni(?Alumni $alumni = null): static
    {
        return $this->state(fn (): array => [
            'alumni_id' => $alumni?->id ?? Alumni::factory(),
        ]);
    }
}
