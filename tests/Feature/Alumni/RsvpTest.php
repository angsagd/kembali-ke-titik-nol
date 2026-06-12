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
        ->set('shirt_size', 'L')
        ->set('shirt_type', 'male')
        ->call('saveRsvp')
        ->assertHasNoErrors();

    expect($profile->refresh()->rsvp_status)->toBe('attending');
    expect($profile->shirt_size)->toBe('L');
    expect($profile->shirt_type)->toBe('male');
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

test('alumni users can submit family rsvp shirt data', function () {
    $profile = Alumni::factory()->create(['rsvp_status' => 'pending']);

    $this->actingAs($profile->user);

    Livewire::test('pages::alumni.rsvp')
        ->set('rsvp_status', 'attending')
        ->set('rsvp_party_type', 'family')
        ->set('family_members_count', 2)
        ->set('shirt_size', 'XL')
        ->set('shirt_type', 'male')
        ->set('family_members.0.shirt_size', 'M')
        ->set('family_members.0.shirt_type', 'female')
        ->set('family_members.1.shirt_size', 'S')
        ->set('family_members.1.shirt_type', 'child')
        ->call('saveRsvp')
        ->assertHasNoErrors();

    $profile->refresh();

    expect($profile->rsvp_status)->toBe('attending');
    expect($profile->rsvp_party_type)->toBe('family');
    expect($profile->family_members_count)->toBe(2);
    expect($profile->shirt_size)->toBe('XL');
    expect($profile->shirt_type)->toBe('male');
    expect($profile->rsvpGuests()->count())->toBe(2);
    $this->assertDatabaseHas('alumni_rsvp_guests', [
        'alumni_id' => $profile->id,
        'sequence' => 1,
        'shirt_size' => 'M',
        'shirt_type' => 'female',
    ]);
    $this->assertDatabaseHas('alumni_rsvp_guests', [
        'alumni_id' => $profile->id,
        'sequence' => 2,
        'shirt_size' => 'S',
        'shirt_type' => 'child',
    ]);
});

test('shirt data is required when alumni rsvp to attending', function () {
    $profile = Alumni::factory()->create(['rsvp_status' => 'pending']);

    $this->actingAs($profile->user);

    Livewire::test('pages::alumni.rsvp')
        ->set('rsvp_status', 'attending')
        ->call('saveRsvp')
        ->assertHasErrors(['shirt_size', 'shirt_type']);

    expect($profile->refresh()->rsvp_status)->toBe('pending');
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
