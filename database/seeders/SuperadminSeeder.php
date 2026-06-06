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
        $role = Role::query()->firstOrCreate(
            ['name' => 'superadmin'],
            ['description' => 'Pengelola teknis sistem'],
        );

        $whatsappNumber = User::normalizeWhatsappNumber((string) config('kembali-ke-titik-nol.superadmin.whatsapp_number'));

        User::query()->updateOrCreate(
            ['whatsapp_number' => $whatsappNumber],
            [
                'role_id' => $role->id,
                'name' => (string) config('kembali-ke-titik-nol.superadmin.name'),
                'email' => (string) (config('kembali-ke-titik-nol.superadmin.email') ?: "{$whatsappNumber}@kembali-ke-titik-nol.local"),
                'password' => (string) config('kembali-ke-titik-nol.superadmin.password'),
                'is_active' => true,
            ],
        );
    }
}
