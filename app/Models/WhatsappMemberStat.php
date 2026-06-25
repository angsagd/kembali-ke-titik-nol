<?php

namespace App\Models;

use Database\Factories\WhatsappMemberStatFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'whatsapp_import_id',
    'whatsapp_member_id',
    'alumni_id',
    'total_messages',
    'pure_text_messages',
    'emoji_messages',
    'media_messages',
    'sticker_messages',
    'link_messages',
    'location_messages',
    'deleted_messages',
    'morning_messages',
    'working_hour_messages',
    'after_work_messages',
    'midnight_messages',
    'weekend_messages',
    'active_days',
    'longest_active_streak',
    'longest_silent_streak',
    'total_words',
    'total_characters',
    'first_message_at',
    'last_message_at',
])]
class WhatsappMemberStat extends Model
{
    /** @use HasFactory<WhatsappMemberStatFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'first_message_at' => 'datetime',
            'last_message_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<WhatsappImport, $this>
     */
    public function whatsappImport(): BelongsTo
    {
        return $this->belongsTo(WhatsappImport::class);
    }

    /**
     * @return BelongsTo<WhatsappMember, $this>
     */
    public function whatsappMember(): BelongsTo
    {
        return $this->belongsTo(WhatsappMember::class);
    }

    /**
     * @return BelongsTo<Alumni, $this>
     */
    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class);
    }
}
