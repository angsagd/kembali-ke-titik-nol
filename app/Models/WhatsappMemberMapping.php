<?php

namespace App\Models;

use Database\Factories\WhatsappMemberMappingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'alumni_id',
    'display_name',
    'normalized_name',
    'notes',
])]
class WhatsappMemberMapping extends Model
{
    /** @use HasFactory<WhatsappMemberMappingFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Alumni, $this>
     */
    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class);
    }

    /**
     * @return HasMany<WhatsappMember, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(WhatsappMember::class);
    }
}
