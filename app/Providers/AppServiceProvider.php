<?php

namespace App\Providers;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureAuthorization();
        $this->configureDefaults();
        $this->guardDestructiveCommandsInLocal();
    }

    /**
     * Configure application authorization gates.
     */
    protected function configureAuthorization(): void
    {
        Gate::define('manage-alumni', fn (User $user): bool => $user->canManageAlumni());
        Gate::define('manage-finance', fn (User $user): bool => $user->canManageFinance());
        Gate::define('view-audit-logs', fn (User $user): bool => $user->canViewAuditLogs());
        Gate::define('manage-user-roles', fn (User $user): bool => $user->canManageUserRoles());
        Gate::define('manage-user-passwords', fn (User $user): bool => $user->canManageUserPasswords());
        Gate::define('import-whatsapp-analytics', fn (User $user): bool => $user->canImportWhatsappAnalytics());
        Gate::define('view-whatsapp-analytics', fn (User $user): bool => $user->canViewWhatsappAnalytics());
        Gate::define('update-own-alumni-profile', fn (User $user): bool => $user->alumni()->exists());
        Gate::define('view-alumni-directory', fn (User $user): bool => $user->canManageAlumni() || $user->alumni()->exists());
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Block destructive database commands in local unless explicitly acknowledged.
     */
    protected function guardDestructiveCommandsInLocal(): void
    {
        if (! app()->runningInConsole() || ! app()->environment('local')) {
            return;
        }

        $arguments = $_SERVER['argv'] ?? [];

        if (! in_array('migrate:fresh', $arguments, true)) {
            return;
        }

        $isTestingEnvironment = in_array('--env=testing', $arguments, true);
        if (! $isTestingEnvironment) {
            $envOptionIndex = array_search('--env', $arguments, true);
            $isTestingEnvironment = $envOptionIndex !== false
                && ($arguments[$envOptionIndex + 1] ?? null) === 'testing';
        }

        $hasExplicitForce = in_array('--force', $arguments, true);

        if ($isTestingEnvironment || $hasExplicitForce) {
            return;
        }

        throw new RuntimeException(
            'Command "migrate:fresh" diblokir di APP_ENV=local untuk mencegah kehilangan data. '
            .'Gunakan --force jika memang sengaja, atau jalankan pada --env=testing.'
        );
    }
}
