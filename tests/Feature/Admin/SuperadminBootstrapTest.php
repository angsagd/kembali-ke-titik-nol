<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SuperadminSeeder;
use Illuminate\Support\Facades\Hash;

test('superadmin seeder creates initial administrative account', function () {
    $this->seed([
        RoleSeeder::class,
        SuperadminSeeder::class,
    ]);

    $user = User::query()->where('whatsapp_number', '620000000001')->firstOrFail();

    expect($user->role->name)->toBe('superadmin');
    expect($user->canManageAlumni())->toBeTrue();
    expect(Hash::check('tgd0001', $user->password))->toBeTrue();

    $this->actingAs($user)
        ->get(route('admin.alumni.index'))
        ->assertOk();
});

test('superadmin seeder creates initial non alumni administrator account', function () {
    $this->seed([
        RoleSeeder::class,
        SuperadminSeeder::class,
    ]);

    $user = User::query()->where('whatsapp_number', '628100000002')->firstOrFail();

    expect($user->role->name)->toBe('administrator');
    expect($user->alumni)->toBeNull();
    expect($user->canManageAlumni())->toBeTrue();
    expect(Hash::check('tgd0002', $user->password))->toBeTrue();

    $this->actingAs($user)
        ->get(route('admin.alumni.index'))
        ->assertOk();
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
