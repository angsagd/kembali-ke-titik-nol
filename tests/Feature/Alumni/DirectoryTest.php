<?php

use App\Models\Alumni;
use App\Models\AlumniTimeline;
use App\Models\City;
use App\Models\Country;
use App\Models\Role;
use App\Models\User;

test('guests are redirected from alumni directory', function () {
    $this->get(route('alumni.directory.index'))
        ->assertRedirect(route('login'));
});

test('users without alumni profile cannot access alumni directory', function () {
    $role = Role::factory()->create([
        'name' => 'bendahara',
        'description' => 'Bendahara reuni',
    ]);

    $user = User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($user)
        ->get(route('alumni.directory.index'))
        ->assertForbidden();
});

test('alumni users can browse and search private directory', function () {
    $viewer = Alumni::factory()->create();
    $country = Country::factory()->create(['name' => 'Indonesia', 'code' => 'ID']);
    $city = City::factory()->create(['country_id' => $country->id, 'name' => 'Makassar']);

    Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'nickname' => 'Ade',
        'company' => 'PT Titik Nol',
        'job_title' => 'Surveyor',
        'current_country_id' => $country->id,
        'current_city_id' => $city->id,
    ]);

    Alumni::factory()->create([
        'full_name' => 'Budi Santoso',
        'company' => 'Geodata Nusantara',
    ]);

    $this->actingAs($viewer->user)
        ->get(route('alumni.directory.index', ['q' => 'Makassar']))
        ->assertOk()
        ->assertSee('Direktori Alumni')
        ->assertSee('Ade Chandra')
        ->assertSee('Makassar')
        ->assertSee('Indonesia')
        ->assertDontSee('Budi Santoso');
});

test('alumni users can read another alumni profile', function () {
    $viewer = Alumni::factory()->create();
    $country = Country::factory()->create(['name' => 'Indonesia', 'code' => 'ID']);
    $city = City::factory()->create(['country_id' => $country->id, 'name' => 'Yogyakarta']);
    $profile = Alumni::factory()->create([
        'full_name' => 'Citra Lestari',
        'nickname' => 'Citra',
        'student_number' => 'D096333',
        'company' => 'Pemetaan Mandiri',
        'current_country_id' => $country->id,
        'current_city_id' => $city->id,
        'short_story' => 'Sekarang mengelola proyek pemetaan.',
        'memorable_story' => 'Kenangan praktikum lapangan.',
        'message_to_friends' => 'Sampai jumpa di reuni.',
    ]);
    AlumniTimeline::factory()->create([
        'alumni_id' => $profile->id,
        'year' => 2001,
        'month' => null,
        'country_id' => $country->id,
        'city_id' => $city->id,
        'notes' => 'Mulai bekerja di Yogyakarta.',
    ]);

    $this->actingAs($viewer->user)
        ->get(route('alumni.directory.show', $profile))
        ->assertOk()
        ->assertSee('Citra Lestari')
        ->assertSee('D096333')
        ->assertSee('Pemetaan Mandiri')
        ->assertSee('Yogyakarta')
        ->assertSee('Indonesia')
        ->assertSee('Sekarang mengelola proyek pemetaan.')
        ->assertSee('Sampai jumpa di reuni.')
        ->assertSee('Timeline Lokasi')
        ->assertSee('2001')
        ->assertSee('Mulai bekerja di Yogyakarta.');
});

test('administrator users can access private alumni directory', function () {
    $role = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);

    $administrator = User::factory()->create(['role_id' => $role->id]);

    Alumni::factory()->create([
        'full_name' => 'Dewi Kartika',
    ]);

    $this->actingAs($administrator)
        ->get(route('alumni.directory.index'))
        ->assertOk()
        ->assertSee('Dewi Kartika');
});
