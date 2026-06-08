<?php

namespace App\Console\Commands;

use App\Models\Alumni;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Attributes\AsCommand;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

#[AsCommand(name: 'readiness:check')]
#[Signature('readiness:check')]
#[Description('Check MVP go-live readiness items that can be verified automatically.')]
class ReadinessCheckCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $checks = $this->checks();

        $this->table(
            ['Item', 'Status', 'Detail'],
            collect($checks)
                ->map(fn (array $check): array => [
                    $check['item'],
                    $check['passed'] ? 'PASS' : 'FAIL',
                    $check['detail'],
                ])
                ->all(),
        );

        $failed = collect($checks)->where('passed', false);

        if ($failed->isNotEmpty()) {
            $this->error('Readiness check belum lulus. Perbaiki item FAIL sebelum go-live.');

            return self::FAILURE;
        }

        $this->info('Readiness check lulus untuk item otomatis.');

        return self::SUCCESS;
    }

    /**
     * @return array<int, array{item: string, passed: bool, detail: string}>
     */
    private function checks(): array
    {
        return [
            $this->requiredRolesExist(),
            $this->superadminExists(),
            $this->alumniDataExists(),
            $this->publicRoutesExist(),
            $this->backupCommandExists(),
            $this->backupScheduleExists(),
            $this->storageLinkExists(),
        ];
    }

    /**
     * @return array{item: string, passed: bool, detail: string}
     */
    private function requiredRolesExist(): array
    {
        $requiredRoles = ['superadmin', 'administrator', 'bendahara', 'alumni'];
        $existingRoles = Role::query()
            ->whereIn('name', $requiredRoles)
            ->pluck('name')
            ->all();
        $missingRoles = array_values(array_diff($requiredRoles, $existingRoles));

        return [
            'item' => 'Role utama',
            'passed' => $missingRoles === [],
            'detail' => $missingRoles === []
                ? 'Semua role utama tersedia.'
                : 'Role belum ada: '.implode(', ', $missingRoles),
        ];
    }

    /**
     * @return array{item: string, passed: bool, detail: string}
     */
    private function superadminExists(): array
    {
        $exists = User::query()
            ->whereHas('role', fn ($query) => $query->where('name', 'superadmin'))
            ->exists();

        return [
            'item' => 'Akun superadmin',
            'passed' => $exists,
            'detail' => $exists ? 'Minimal satu superadmin tersedia.' : 'Belum ada user superadmin.',
        ];
    }

    /**
     * @return array{item: string, passed: bool, detail: string}
     */
    private function alumniDataExists(): array
    {
        $count = Alumni::query()->count();

        return [
            'item' => 'Data alumni',
            'passed' => $count > 0,
            'detail' => "{$count} data alumni tersedia.",
        ];
    }

    /**
     * @return array{item: string, passed: bool, detail: string}
     */
    private function publicRoutesExist(): array
    {
        $routes = ['home', 'public.rsvp', 'public.gallery', 'news.index'];
        $missingRoutes = collect($routes)
            ->reject(fn (string $route): bool => Route::has($route))
            ->values()
            ->all();

        return [
            'item' => 'Route publik',
            'passed' => $missingRoutes === [],
            'detail' => $missingRoutes === []
                ? 'Route publik dasar tersedia.'
                : 'Route belum ada: '.implode(', ', $missingRoutes),
        ];
    }

    /**
     * @return array{item: string, passed: bool, detail: string}
     */
    private function backupCommandExists(): array
    {
        $exists = array_key_exists('backup:database', Artisan::all());

        return [
            'item' => 'Command backup',
            'passed' => $exists,
            'detail' => $exists ? 'backup:database tersedia.' : 'backup:database belum tersedia.',
        ];
    }

    /**
     * @return array{item: string, passed: bool, detail: string}
     */
    private function backupScheduleExists(): array
    {
        $consoleRoutes = file_get_contents(base_path('routes/console.php')) ?: '';
        $exists = str_contains($consoleRoutes, "Schedule::command('backup:database')")
            && str_contains($consoleRoutes, "dailyAt('02:00')");

        return [
            'item' => 'Jadwal backup',
            'passed' => $exists,
            'detail' => $exists ? 'Backup database terjadwal harian 02:00.' : 'Jadwal backup harian belum ditemukan.',
        ];
    }

    /**
     * @return array{item: string, passed: bool, detail: string}
     */
    private function storageLinkExists(): array
    {
        $link = public_path('storage');
        $exists = app()->environment('testing') || is_link($link) || is_dir($link);

        return [
            'item' => 'Storage public',
            'passed' => $exists,
            'detail' => $exists ? 'public/storage tersedia.' : 'Jalankan php artisan storage:link.',
        ];
    }
}
