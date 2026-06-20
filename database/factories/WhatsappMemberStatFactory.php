<?php

namespace Database\Factories;

use App\Models\WhatsappMember;
use App\Models\WhatsappMemberStat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WhatsappMemberStat>
 */
class WhatsappMemberStatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $member = WhatsappMember::factory()->create();

        return [
            'whatsapp_import_id' => $member->whatsapp_import_id,
            'whatsapp_member_id' => $member->id,
            'alumni_id' => $member->alumni_id,
            'total_messages' => $member->total_messages,
            'pure_text_messages' => fake()->numberBetween(0, $member->total_messages),
            'emoji_messages' => fake()->numberBetween(0, $member->total_messages),
            'media_messages' => fake()->numberBetween(0, $member->total_messages),
            'sticker_messages' => fake()->numberBetween(0, $member->total_messages),
            'link_messages' => fake()->numberBetween(0, $member->total_messages),
            'location_messages' => 0,
            'deleted_messages' => 0,
            'morning_messages' => 0,
            'working_hour_messages' => 0,
            'after_work_messages' => 0,
            'midnight_messages' => 0,
            'weekend_messages' => 0,
            'active_days' => fake()->numberBetween(1, 30),
            'total_words' => $member->total_words,
            'total_characters' => $member->total_characters,
            'first_message_at' => $member->first_message_at,
            'last_message_at' => $member->last_message_at,
        ];
    }
}
