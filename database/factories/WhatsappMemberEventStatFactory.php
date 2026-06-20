<?php

namespace Database\Factories;

use App\Models\WhatsappMember;
use App\Models\WhatsappMemberEventStat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WhatsappMemberEventStat>
 */
class WhatsappMemberEventStatFactory extends Factory
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
            'member_added_as_actor' => 0,
            'member_added_as_target' => 0,
            'member_removed_as_actor' => 0,
            'member_removed_as_target' => 0,
            'member_left' => 0,
            'phone_number_changed' => 0,
            'security_code_changed' => 0,
            'group_name_changed' => 0,
            'group_description_changed' => 0,
            'group_icon_changed' => 0,
            'disappearing_message_changed' => 0,
        ];
    }
}
