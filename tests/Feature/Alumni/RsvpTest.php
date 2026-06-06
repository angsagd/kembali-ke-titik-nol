<?php

use App\Models\Alumni;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected from rsvp page', function () {
    $this->get(route('alumni.rsvp'))
        ->assertRedirect(route('login'));
});

test('users without alumni profile cannot access rsvp page', function () {
    $role = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $user = User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($user)
        ->get(route('alumni.rsvp'))
        ->assertForbidden();
});

test('alumni users can view rsvp page', function () {
    $profile = Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'student_number' => 'D096001',
        'rsvp_status' => 'pending',
    ]);

    $this->actingAs($profile->user)
        ->get(route('alumni.rsvp'))
        ->assertOk()
        ->assertSee('RSVP')
        ->assertSee('Ade Chandra')
        ->assertSee('Belum Merespon')
        ->assertSee('Simpan RSVP');
});

test('rsvp status is required', function () {
    $profile = Alumni::factory()->create(['rsvp_status' => 'pending']);

    $this->actingAs($profile->user);

    Livewire::test('pages::alumni.rsvp')
        ->call('saveRsvp')
        ->assertHasErrors(['rsvp_status' => ['required']]);

    expect($profile->refresh()->rsvp_status)->toBe('pending');
});

test('alumni users can update rsvp to attending', function () {
    $profile = Alumni::factory()->create(['rsvp_status' => 'pending']);

    $this->actingAs($profile->user);

    Livewire::test('pages::alumni.rsvp')
        ->set('rsvp_status', 'attending')
        ->call('saveRsvp')
        ->assertHasNoErrors();

    expect($profile->refresh()->rsvp_status)->toBe('attending');
});

test('alumni users can update rsvp to not attending', function () {
    $profile = Alumni::factory()->create(['rsvp_status' => 'attending']);

    $this->actingAs($profile->user);

    Livewire::test('pages::alumni.rsvp')
        ->set('rsvp_status', 'not_attending')
        ->call('saveRsvp')
        ->assertHasNoErrors();

    expect($profile->refresh()->rsvp_status)->toBe('not_attending');
});

test('invalid rsvp status is rejected', function () {
    $profile = Alumni::factory()->create(['rsvp_status' => 'pending']);

    $this->actingAs($profile->user);

    Livewire::test('pages::alumni.rsvp')
        ->set('rsvp_status', 'pending')
        ->call('saveRsvp')
        ->assertHasErrors(['rsvp_status' => ['in']]);

    expect($profile->refresh()->rsvp_status)->toBe('pending');
});
