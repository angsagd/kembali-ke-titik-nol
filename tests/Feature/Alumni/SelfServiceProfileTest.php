<?php

use App\Models\Alumni;
use App\Models\City;
use App\Models\Country;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected from alumni profile page', function () {
    $this->get(route('alumni.profile'))
        ->assertRedirect(route('login'));
});

test('users without alumni profile cannot access alumni profile page', function () {
    $role = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);

    $user = User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($user)
        ->get(route('alumni.profile'))
        ->assertForbidden();
});

test('alumni users can view their self service profile page', function () {
    $profile = Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'rsvp_status' => 'pending',
    ]);

    $this->actingAs($profile->user)
        ->get(route('alumni.profile'))
        ->assertOk()
        ->assertSee('Profil Alumni')
        ->assertSee('Ade Chandra')
        ->assertSee('Simpan Profil');
});

test('alumni users can update their own profile and rsvp', function () {
    $country = Country::factory()->create(['name' => 'Indonesia', 'iso_code' => 'ID']);
    $city = City::factory()->create(['country_id' => $country->id, 'name' => 'Makassar']);
    $profile = Alumni::factory()->create([
        'full_name' => 'Nama Lama',
        'student_number' => 'D096010',
        'email' => 'lama@example.test',
        'rsvp_status' => 'pending',
        'is_profile_completed' => false,
    ]);

    $this->actingAs($profile->user);

    Livewire::test('pages::alumni.profile')
        ->set('full_name', 'Nama Baru')
        ->set('nickname', 'Baru')
        ->set('student_number', 'D096777')
        ->set('email', 'baru@example.test')
        ->set('whatsapp_number', '+62 812-3333-4444')
        ->set('rsvp_status', 'attending')
        ->set('company', 'PT Titik Nol')
        ->set('job_title', 'Surveyor')
        ->set('current_country_id', $country->id)
        ->set('current_city_id', $city->id)
        ->set('short_story', 'Sekarang tinggal di Makassar.')
        ->set('memorable_story', 'Kenangan praktikum lapangan.')
        ->set('message_to_friends', 'Sampai jumpa di reuni.')
        ->call('updateProfile')
        ->assertHasNoErrors();

    $profile->refresh();
    $profile->user->refresh();

    expect($profile->full_name)->toBe('Nama Baru');
    expect($profile->student_number)->toBe('D096777');
    expect($profile->rsvp_status)->toBe('attending');
    expect($profile->current_country_id)->toBe($country->id);
    expect($profile->current_city_id)->toBe($city->id);
    expect($profile->short_story)->toBe('Sekarang tinggal di Makassar.');
    expect($profile->is_profile_completed)->toBeTrue();
    expect($profile->user->name)->toBe('Nama Baru');
    expect($profile->user->whatsapp_number)->toBe('6281233334444');
    expect($profile->user->email)->toBe('baru@example.test');
});

test('alumni profile update validates unique identifiers', function () {
    $existing = Alumni::factory()->create([
        'student_number' => 'D096123',
    ]);

    $profile = Alumni::factory()->create([
        'student_number' => 'D096124',
    ]);

    $this->actingAs($profile->user);

    Livewire::test('pages::alumni.profile')
        ->set('student_number', 'D096123')
        ->set('whatsapp_number', $existing->user->whatsapp_number)
        ->call('updateProfile')
        ->assertHasErrors([
            'student_number' => 'unique',
            'whatsapp_number' => 'unique',
        ]);
});
