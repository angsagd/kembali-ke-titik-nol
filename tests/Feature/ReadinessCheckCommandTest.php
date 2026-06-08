<?php

use App\Models\Alumni;
use App\Models\Role;
use App\Models\User;

test('readiness check passes when automatic go-live items are available', function () {
    $superadminRole = Role::factory()->create(['name' => 'superadmin']);
    Role::factory()->create(['name' => 'administrator']);
    Role::factory()->create(['name' => 'bendahara']);
    Role::factory()->create(['name' => 'alumni']);
    User::factory()->create(['role_id' => $superadminRole->id]);
    Alumni::factory()->create();

    $this->artisan('readiness:check')
        ->assertSuccessful()
        ->expectsOutputToContain('Readiness check lulus');
});

test('readiness check fails when critical go-live items are missing', function () {
    $this->artisan('readiness:check')
        ->assertFailed()
        ->expectsOutputToContain('Readiness check belum lulus');
});
