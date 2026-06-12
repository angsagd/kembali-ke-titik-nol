<?php

namespace App\Models;

use Database\Factories\AlumniRsvpGuestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['alumni_id', 'sequence', 'shirt_size', 'shirt_type'])]
class AlumniRsvpGuest extends Model
{
    /** @use HasFactory<AlumniRsvpGuestFactory> */
    use HasFactory;

    /**
     * Get the alumni profile that owns this RSVP guest.
     *
     * @return BelongsTo<Alumni, $this>
     */
    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class);
    }
}
