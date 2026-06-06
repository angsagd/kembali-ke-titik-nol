<?php

namespace App\Models;

use Database\Factories\AlumniFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'user_id',
    'student_number',
    'full_name',
    'nickname',
    'email',
    'current_city_id',
    'current_country_id',
    'company',
    'job_title',
    'alumni_status',
    'rsvp_status',
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
     * Get the current city of the alumni profile.
     *
     * @return BelongsTo<City, $this>
     */
    public function currentCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'current_city_id');
    }

    /**
     * Get the current country of the alumni profile.
     *
     * @return BelongsTo<Country, $this>
     */
    public function currentCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'current_country_id');
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
}
