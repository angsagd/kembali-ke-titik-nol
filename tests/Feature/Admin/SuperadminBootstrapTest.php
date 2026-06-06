<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SuperadminSeeder;

test('superadmin seeder creates initial administrative account', function () {
    $this->seed([
        RoleSeeder::class,
        SuperadminSeeder::class,
    ]);

    $user = User::query()->where('whatsapp_number', '620000000001')->firstOrFail();

    expect($user->role->name)->toBe('superadmin');
    expect($user->canManageAlumni())->toBeTrue();

    $this->post(route('login.store'), [
        'whatsapp_number' => '620000000001',
        'password' => 'tgd0001',
    ])->assertSessionHasNoErrors();

    $this->assertAuthenticatedAs($user);

    $this->get(route('admin.alumni.index'))->assertOk();
});

test('existing user can be promoted to an administrative role by whatsapp number', function () {
    $this->seed(RoleSeeder::class);

    $alumniRole = Role::query()->where('name', 'alumni')->firstOrFail();
    $user = User::factory()->create([
        'role_id' => $alumniRole->id,
        'whatsapp_number' => '6281234567890',
    ]);

    $this->artisan('user:promote-role', [
        'whatsapp_number' => '+62 812-3456-7890',
        'role' => 'administrator',
    ])->assertSuccessful();

    $user->refresh();

    expect($user->role->name)->toBe('administrator');
    expect($user->canManageAlumni())->toBeTrue();
});

test('promote user role command fails for unknown role', function () {
    $this->seed(RoleSeeder::class);

    $this->artisan('user:promote-role', [
        'whatsapp_number' => '6281234567890',
        'role' => 'unknown',
    ])->assertFailed();
});
