<?php

use App\Models\Alumni;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected from rsvp monitoring', function () {
    $this->get(route('admin.rsvp.index'))
        ->assertRedirect(route('login'));
});

test('alumni users cannot access rsvp monitoring', function () {
    $profile = Alumni::factory()->create();

    $this->actingAs($profile->user)
        ->get(route('admin.rsvp.index'))
        ->assertForbidden();
});

test('administrator users can view rsvp monitoring summary', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);

    Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'student_number' => 'D096001',
        'rsvp_status' => 'attending',
    ]);
    Alumni::factory()->create([
        'full_name' => 'Budi Santoso',
        'student_number' => 'D096002',
        'rsvp_status' => 'not_attending',
    ]);
    Alumni::factory()->create([
        'full_name' => 'Citra Lestari',
        'student_number' => 'D096003',
        'rsvp_status' => 'pending',
    ]);

    $this->actingAs($administrator)
        ->get(route('admin.rsvp.index'))
        ->assertOk()
        ->assertSee('Monitoring RSVP')
        ->assertSee('Total Alumni')
        ->assertSee('Response Rate')
        ->assertSee('67%')
        ->assertSee('Ade Chandra')
        ->assertSee('Budi Santoso')
        ->assertSee('Citra Lestari');
});

test('superadmin users can access rsvp monitoring', function () {
    $superadminRole = Role::factory()->create([
        'name' => 'superadmin',
        'description' => 'Pengelola utama sistem',
    ]);
    $superadmin = User::factory()->create(['role_id' => $superadminRole->id]);

    $this->actingAs($superadmin)
        ->get(route('admin.rsvp.index'))
        ->assertOk()
        ->assertSee('Monitoring RSVP');
});

test('administrator users can filter rsvp monitoring by status', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);

    Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'rsvp_status' => 'attending',
    ]);
    Alumni::factory()->create([
        'full_name' => 'Budi Santoso',
        'rsvp_status' => 'not_attending',
    ]);

    $this->actingAs($administrator);

    Livewire::test('pages::admin.rsvp.index')
        ->set('status', 'attending')
        ->assertSee('Ade Chandra')
        ->assertDontSee('Budi Santoso');
});

test('administrator users can search rsvp monitoring', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);

    Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'student_number' => 'D096001',
    ]);
    Alumni::factory()->create([
        'full_name' => 'Budi Santoso',
        'student_number' => 'D096002',
    ]);

    $this->actingAs($administrator);

    Livewire::test('pages::admin.rsvp.index')
        ->set('search', 'D096001')
        ->assertSee('Ade Chandra')
        ->assertDontSee('Budi Santoso');
});
