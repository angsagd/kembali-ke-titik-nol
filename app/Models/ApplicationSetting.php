<?php

namespace App\Models;

use Database\Factories\ApplicationSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'key',
    'value',
])]
class ApplicationSetting extends Model
{
    public const PUBLIC_RSVP_FORM_ENABLED = 'public_rsvp_form_enabled';

    /** @use HasFactory<ApplicationSettingFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    public static function boolean(string $key, bool $default = false): bool
    {
        $setting = self::query()
            ->where('key', $key)
            ->first();

        if ($setting === null) {
            return $default;
        }

        return (bool) data_get($setting->value, 'enabled', $default);
    }

    public static function setBoolean(string $key, bool $enabled): self
    {
        return self::query()->updateOrCreate(
            ['key' => $key],
            ['value' => ['enabled' => $enabled]],
        );
    }
}
