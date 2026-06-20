<?php

namespace App\Models;

use Database\Factories\WhatsappActivityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'whatsapp_import_id',
    'whatsapp_member_id',
    'alumni_id',
    'line_number',
    'occurred_at_source',
    'occurred_at_display',
    'activity_type',
    'system_event_type',
    'sender_name',
    'sender_normalized',
    'target_name',
    'target_normalized',
    'message_text',
    'has_media',
    'has_sticker',
    'has_link',
    'has_emoji',
    'is_deleted_message',
    'word_count',
    'character_count',
    'raw_text',
])]
class WhatsappActivity extends Model
{
    /** @use HasFactory<WhatsappActivityFactory> */
    use HasFactory;

    protected $attributes = [
        'has_media' => false,
        'has_sticker' => false,
        'has_link' => false,
        'has_emoji' => false,
        'is_deleted_message' => false,
        'word_count' => 0,
        'character_count' => 0,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'line_number' => 'integer',
            'occurred_at_source' => 'datetime',
            'occurred_at_display' => 'datetime',
            'has_media' => 'boolean',
            'has_sticker' => 'boolean',
            'has_link' => 'boolean',
            'has_emoji' => 'boolean',
            'is_deleted_message' => 'boolean',
            'word_count' => 'integer',
            'character_count' => 'integer',
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
