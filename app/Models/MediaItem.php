<?php

namespace App\Models;

use Database\Factories\MediaItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'type',
    'uploaded_by_alumni_id',
    'title',
    'description',
    'file_path',
    'thumbnail_path',
    'video_url',
    'provider',
    'month',
    'year',
    'visibility',
    'file_size',
    'width',
    'height',
])]
class MediaItem extends Model
{
    /** @use HasFactory<MediaItemFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'month' => 'integer',
            'year' => 'integer',
            'file_size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    /**
     * Get the alumni profile that uploaded the media item.
     *
     * @return BelongsTo<Alumni, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(Alumni::class, 'uploaded_by_alumni_id');
    }

    /**
     * Get alumni tagged in this media item.
     *
     * @return BelongsToMany<Alumni, $this>
     */
    public function taggedAlumni(): BelongsToMany
    {
        return $this->belongsToMany(Alumni::class, 'media_item_tags')
            ->withPivot('tagged_by_alumni_id')
            ->withTimestamps();
    }

    public function isPhoto(): bool
    {
        return $this->type === 'photo';
    }

    public function displayUrl(): ?string
    {
        if ($this->isPhoto()) {
            return $this->file_path ? Storage::disk('public')->url($this->file_path) : null;
        }

        return $this->video_url;
    }
}
