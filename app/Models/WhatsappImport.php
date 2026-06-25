<?php

namespace App\Models;

use Database\Factories\WhatsappImportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'uploaded_by',
    'group_name',
    'file_name',
    'file_path',
    'timezone_source',
    'timezone_display',
    'total_lines',
    'total_activities',
    'import_start_date',
    'import_end_date',
    'total_messages',
    'total_system_events',
    'total_participants',
    'total_words',
    'total_emoji_messages',
    'total_media_messages',
    'total_sticker_messages',
    'total_link_messages',
    'total_deleted_messages',
    'first_activity_at',
    'last_activity_at',
    'longest_active_streak',
    'longest_silent_streak',
    'event_member_left',
    'event_member_added',
    'event_member_removed',
    'event_phone_changed',
    'event_security_code_changed',
    'event_group_name_changed',
    'event_group_description_changed',
    'event_group_icon_changed',
    'status',
    'processed_at',
    'notes',
    'conclusion',
])]
class WhatsappImport extends Model
{
    /** @use HasFactory<WhatsappImportFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'import_start_date' => 'date',
            'import_end_date' => 'date',
            'total_lines' => 'integer',
            'total_activities' => 'integer',
            'total_messages' => 'integer',
            'total_system_events' => 'integer',
            'total_participants' => 'integer',
            'total_words' => 'integer',
            'total_emoji_messages' => 'integer',
            'total_media_messages' => 'integer',
            'total_sticker_messages' => 'integer',
            'total_link_messages' => 'integer',
            'total_deleted_messages' => 'integer',
            'first_activity_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * Get the user who uploaded the import file.
     *
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get members detected in this import.
     *
     * @return HasMany<WhatsappMember, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(WhatsappMember::class);
    }

    /**
     * Get activities detected in this import.
     *
     * @return HasMany<WhatsappActivity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(WhatsappActivity::class);
    }

    /**
     * Get daily stats for this import.
     *
     * @return HasMany<WhatsappDailyStat, $this>
     */
    public function dailyStats(): HasMany
    {
        return $this->hasMany(WhatsappDailyStat::class);
    }

    /**
     * Get member message stats for this import.
     *
     * @return HasMany<WhatsappMemberStat, $this>
     */
    public function memberStats(): HasMany
    {
        return $this->hasMany(WhatsappMemberStat::class);
    }

    /**
     * Get member system event stats for this import.
     *
     * @return HasMany<WhatsappMemberEventStat, $this>
     */
    public function memberEventStats(): HasMany
    {
        return $this->hasMany(WhatsappMemberEventStat::class);
    }
}
