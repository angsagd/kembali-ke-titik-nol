<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['role_id', 'name', 'email', 'whatsapp_number', 'password', 'is_active', 'last_login_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Get the role assigned to the user.
     *
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the alumni profile associated with the user account.
     *
     * @return HasOne<Alumni, $this>
     */
    public function alumni(): HasOne
    {
        return $this->hasOne(Alumni::class);
    }

    /**
     * Get news items authored by the user.
     *
     * @return HasMany<News, $this>
     */
    public function news(): HasMany
    {
        return $this->hasMany(News::class, 'author_id');
    }

    /**
     * Get audit log entries performed by the user.
     *
     * @return HasMany<AuditLog, $this>
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get WhatsApp chat imports uploaded by the user.
     *
     * @return HasMany<WhatsappImport, $this>
     */
    public function whatsappImports(): HasMany
    {
        return $this->hasMany(WhatsappImport::class, 'uploaded_by');
    }

    /**
     * Determine if the user has one of the given roles.
     *
     * @param  array<int, string>|string  $roles
     */
    public function hasRole(array|string $roles): bool
    {
        $roles = (array) $roles;

        return in_array($this->role?->name, $roles, true);
    }

    /**
     * Determine if the user can access administrative alumni management.
     */
    public function canManageAlumni(): bool
    {
        return $this->hasRole(['superadmin', 'administrator']);
    }

    /**
     * Determine if the user can manage payments and donations.
     */
    public function canManageFinance(): bool
    {
        return $this->hasRole(['superadmin', 'bendahara']);
    }

    /**
     * Determine if the user can view audit logs.
     */
    public function canViewAuditLogs(): bool
    {
        return $this->hasRole('superadmin');
    }

    /**
     * Determine if the user can manage user role assignments.
     */
    public function canManageUserRoles(): bool
    {
        return $this->hasRole('superadmin');
    }

    /**
     * Determine if the user can reset other user passwords.
     */
    public function canManageUserPasswords(): bool
    {
        return $this->hasRole('superadmin');
    }

    /**
     * Determine if the user can import WhatsApp chat analytics.
     */
    public function canImportWhatsappAnalytics(): bool
    {
        return $this->hasRole('superadmin');
    }

    /**
     * Determine if the user can view WhatsApp analytics.
     */
    public function canViewWhatsappAnalytics(): bool
    {
        return $this->canManageAlumni() || $this->alumni()->exists();
    }

    /**
     * Normalize a WhatsApp number to digits only.
     */
    public static function normalizeWhatsappNumber(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = preg_replace('/[^0-9]/', '', $value);

        return $normalized === '' ? null : $normalized;
    }

    /**
     * Normalize WhatsApp numbers before persisting them.
     *
     * @return Attribute<string|null, string|null>
     */
    protected function whatsappNumber(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value): ?string => $value === null
                ? null
                : self::normalizeWhatsappNumber($value),
        );
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}
