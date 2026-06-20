<?php

namespace App\Models;

use Database\Factories\WhatsappMemberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'whatsapp_import_id',
    'whatsapp_member_mapping_id',
    'alumni_id',
    'display_name',
    'normalized_name',
    'first_message_at',
    'last_message_at',
    'total_messages',
    'total_words',
    'total_characters',
])]
class WhatsappMember extends Model
{
    /** @use HasFactory<WhatsappMemberFactory> */
    use HasFactory;

    protected $attributes = [
        'total_messages' => 0,
        'total_words' => 0,
        'total_characters' => 0,
    ];

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
            'total_messages' => 'integer',
            'total_words' => 'integer',
            'total_characters' => 'integer',
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
     * @return BelongsTo<WhatsappMemberMapping, $this>
     */
    public function mapping(): BelongsTo
    {
        return $this->belongsTo(WhatsappMemberMapping::class, 'whatsapp_member_mapping_id');
    }

    /**
     * @return BelongsTo<Alumni, $this>
     */
    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class);
    }

    /**
     * @return HasMany<WhatsappActivity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(WhatsappActivity::class);
    }
}
