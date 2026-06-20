<?php

namespace App\Models;

use Database\Factories\WhatsappDailyStatFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'whatsapp_import_id',
    'stat_date',
    'total_activities',
    'total_messages',
    'total_system_events',
    'total_media',
    'total_links',
    'total_emojis',
    'total_deleted',
])]
class WhatsappDailyStat extends Model
{
    /** @use HasFactory<WhatsappDailyStatFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stat_date' => 'date',
            'total_activities' => 'integer',
            'total_messages' => 'integer',
            'total_system_events' => 'integer',
            'total_media' => 'integer',
            'total_links' => 'integer',
            'total_emojis' => 'integer',
            'total_deleted' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<WhatsappImport, $this>
     */
    public function whatsappImport(): BelongsTo
    {
        return $this->belongsTo(WhatsappImport::class);
    }
}
