<?php

namespace App\Models;

use Database\Factories\WhatsappStatisticFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'whatsapp_import_id',
    'category',
    'label',
    'alumni_id',
    'value',
    'rank',
    'metadata',
])]
class WhatsappStatistic extends Model
{
    /** @use HasFactory<WhatsappStatisticFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'rank' => 'integer',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the import that produced this statistic.
     *
     * @return BelongsTo<WhatsappImport, $this>
     */
    public function whatsappImport(): BelongsTo
    {
        return $this->belongsTo(WhatsappImport::class);
    }

    /**
     * Get the alumni linked to this statistic, when matched.
     *
     * @return BelongsTo<Alumni, $this>
     */
    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class);
    }
}
