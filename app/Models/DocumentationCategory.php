<?php

namespace App\Models;

use Database\Factories\DocumentationCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'sort_order', 'is_active'])]
class DocumentationCategory extends Model
{
    /** @use HasFactory<DocumentationCategoryFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_active' => 'boolean'];
    }

    /** @return HasMany<MediaItem, $this> */
    public function mediaItems(): HasMany
    {
        return $this->hasMany(MediaItem::class);
    }
}
