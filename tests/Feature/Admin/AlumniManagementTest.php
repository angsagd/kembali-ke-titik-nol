<?php

use App\Models\Alumni;
use App\Models\Role;
use App\Models\User;

test('guests are redirected from alumni management', function () {
    $this->get(route('admin.alumni.index'))
        ->assertRedirect(route('login'));
});

test('alumni users cannot access alumni management', function () {
    $alumniRole = Role::factory()->alumni()->create();
    $user = User::factory()->create(['role_id' => $alumniRole->id]);

    $this->actingAs($user)
        ->get(route('admin.alumni.index'))
        ->assertForbidden();
});

test('administrator users can browse and search alumni', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);

    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);
    $profile = Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'student_number' => 'D096001',
        'rsvp_status' => 'pending',
    ]);

    Alumni::factory()->create([
        'full_name' => 'Budi Santoso',
        'student_number' => 'D096002',
    ]);

    $this->actingAs($administrator)
        ->get(route('admin.alumni.index', ['q' => 'Ade']))
        ->assertOk()
        ->assertSee('Manajemen Alumni')
        ->assertSee('Ade Chandra')
        ->assertSee('D096001')
        ->assertDontSee('Budi Santoso');

    $this->actingAs($administrator)
        ->get(route('admin.alumni.show', $profile))
        ->assertOk()
        ->assertSee('Detail data alumni')
        ->assertSee('Ade Chandra');
});
