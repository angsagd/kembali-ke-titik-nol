<?php

namespace App\Models;

use Database\Factories\WhatsappMemberEventStatFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'whatsapp_import_id',
    'whatsapp_member_id',
    'alumni_id',
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
])]
class WhatsappMemberEventStat extends Model
{
    /** @use HasFactory<WhatsappMemberEventStatFactory> */
    use HasFactory;

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
