<?php

namespace Database\Factories;

use App\Models\WhatsappActivity;
use App\Models\WhatsappImport;
use App\Models\WhatsappMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WhatsappActivity>
 */
class WhatsappActivityFactory extends Factory
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
            'whatsapp_member_id' => null,
            'alumni_id' => null,
            'line_number' => fake()->numberBetween(1, 1000),
            'occurred_at_source' => now(),
            'occurred_at_display' => now()->subHour(),
            'activity_type' => 'message',
            'system_event_type' => null,
            'sender_name' => fake()->name(),
            'sender_normalized' => fake()->userName(),
            'target_name' => null,
            'target_normalized' => null,
            'message_text' => fake()->sentence(),
            'has_media' => false,
            'has_sticker' => false,
            'has_link' => false,
            'has_emoji' => false,
            'is_deleted_message' => false,
            'word_count' => fake()->numberBetween(1, 20),
            'character_count' => fake()->numberBetween(10, 200),
            'raw_text' => fake()->sentence(),
        ];
    }

    public function forMember(?WhatsappMember $member = null): static
    {
        return $this->state(function () use ($member): array {
            $member ??= WhatsappMember::factory()->create();

            return [
                'whatsapp_import_id' => $member->whatsapp_import_id,
                'whatsapp_member_id' => $member->id,
                'alumni_id' => $member->alumni_id,
                'sender_name' => $member->display_name,
                'sender_normalized' => $member->normalized_name,
            ];
        });
    }
}
