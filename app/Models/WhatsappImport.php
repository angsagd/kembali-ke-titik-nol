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
    'file_name',
    'file_path',
    'import_start_date',
    'import_end_date',
    'total_messages',
    'total_participants',
    'status',
    'processed_at',
    'notes',
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
            'total_messages' => 'integer',
            'total_participants' => 'integer',
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
     * Get statistics generated from this import.
     *
     * @return HasMany<WhatsappStatistic, $this>
     */
    public function statistics(): HasMany
    {
        return $this->hasMany(WhatsappStatistic::class);
    }
}
