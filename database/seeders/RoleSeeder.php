<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect([
            ['name' => 'superadmin', 'description' => 'Pengelola teknis sistem'],
            ['name' => 'administrator', 'description' => 'Panitia pelaksana reuni'],
            ['name' => 'bendahara', 'description' => 'Pengelola pembayaran dan donasi'],
            ['name' => 'alumni', 'description' => 'Anggota alumni'],
        ])->each(fn (array $role): Role => Role::query()->updateOrCreate(
            ['name' => $role['name']],
            ['description' => $role['description']],
        ));
    }
}
