<?php

namespace App\Models;

use Database\Factories\AlumniFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'student_number',
    'full_name',
    'nickname',
    'email',
    'city',
    'country',
    'latitude',
    'longitude',
    'company',
    'job_title',
    'alumni_status',
    'rsvp_status',
    'rsvp_party_type',
    'family_members_count',
    'brings_private_vehicle',
    'shirt_size',
    'shirt_type',
    'special_notes',
    'short_story',
    'memorable_story',
    'message_to_friends',
    'college_photo_path',
    'current_photo_path',
    'is_profile_completed',
])]
class Alumni extends Model
{
    /** @use HasFactory<AlumniFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'alumni';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_profile_completed' => 'boolean',
            'family_members_count' => 'integer',
            'brings_private_vehicle' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    /**
     * Get the user account associated with the alumni profile.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the location timeline entries for the alumni profile.
     *
     * @return HasMany<AlumniTimeline, $this>
     */
    public function timelines(): HasMany
    {
        return $this->hasMany(AlumniTimeline::class)
            ->orderBy('year')
            ->orderByRaw('month is null')
            ->orderBy('month');
    }

    /**
     * Get additional RSVP family members for this alumni profile.
     *
     * @return HasMany<AlumniRsvpGuest, $this>
     */
    public function rsvpGuests(): HasMany
    {
        return $this->hasMany(AlumniRsvpGuest::class)
            ->orderBy('sequence');
    }

    /**
     * Get the reunion payment for the alumni profile.
     *
     * @return HasOne<Payment, $this>
     */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Get the donation for the alumni profile.
     *
     * @return HasOne<Donation, $this>
     */
    public function donation(): HasOne
    {
        return $this->hasOne(Donation::class);
    }

    /**
     * Get the room assignment for the alumni profile.
     *
     * @return HasOne<RoomAssignment, $this>
     */
    public function roomAssignment(): HasOne
    {
        return $this->hasOne(RoomAssignment::class);
    }

    /**
     * Get media items uploaded by this alumni profile.
     *
     * @return HasMany<MediaItem, $this>
     */
    public function uploadedMediaItems(): HasMany
    {
        return $this->hasMany(MediaItem::class, 'uploaded_by_alumni_id');
    }

    /**
     * Get media items where this alumni profile is tagged.
     *
     * @return BelongsToMany<MediaItem, $this>
     */
    public function taggedMediaItems(): BelongsToMany
    {
        return $this->belongsToMany(MediaItem::class, 'media_item_tags')
            ->withPivot('tagged_by_alumni_id')
            ->withTimestamps();
    }
}
