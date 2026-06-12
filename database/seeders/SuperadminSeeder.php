<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class SuperadminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedAdministrativeUser(
            configKey: 'superadmin',
            roleName: 'superadmin',
            defaultDescription: 'Pengelola teknis sistem',
        );

        $this->seedAdministrativeUser(
            configKey: 'administrator',
            roleName: 'administrator',
            defaultDescription: 'Panitia pelaksana reuni',
        );
    }

    private function seedAdministrativeUser(string $configKey, string $roleName, string $defaultDescription): void
    {
        $role = Role::query()->firstOrCreate(
            ['name' => $roleName],
            ['description' => $defaultDescription],
        );

        $whatsappNumber = User::normalizeWhatsappNumber((string) config("kembali-ke-titik-nol.{$configKey}.whatsapp_number"));

        User::query()->updateOrCreate(
            ['whatsapp_number' => $whatsappNumber],
            [
                'role_id' => $role->id,
                'name' => (string) config("kembali-ke-titik-nol.{$configKey}.name"),
                'email' => (string) (config("kembali-ke-titik-nol.{$configKey}.email") ?: "{$whatsappNumber}@kembali-ke-titik-nol.local"),
                'password' => (string) config("kembali-ke-titik-nol.{$configKey}.password"),
                'is_active' => true,
            ],
        );
    }
}
