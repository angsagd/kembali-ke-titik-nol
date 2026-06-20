<?php

namespace App\Services\WhatsAppAnalyzer;

class WhatsappStatsBuilder
{
    public const PERSONAL_MESSAGE_SERIES = [
        'pure_text_messages',
        'emoji_messages',
        'media_messages',
        'sticker_messages',
        'link_messages',
        'deleted_messages',
    ];

    public const PERSONAL_SYSTEM_EVENT_SERIES = [
        'member_added_as_actor',
        'member_added_as_target',
        'member_removed_as_actor',
        'member_removed_as_target',
        'member_left',
        'phone_number_changed',
        'security_code_changed',
        'group_name_changed',
        'group_description_changed',
        'group_icon_changed',
        'disappearing_message_changed',
    ];
}
