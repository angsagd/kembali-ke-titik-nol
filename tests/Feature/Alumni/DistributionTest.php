<?php

use App\Models\Alumni;
use App\Models\AlumniTimeline;
use App\Models\City;
use App\Models\Country;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected from alumni distribution', function () {
    $this->get(route('alumni.distribution.index'))
        ->assertRedirect(route('login'));
});

test('users without alumni profile cannot access alumni distribution', function () {
    $role = Role::factory()->create([
        'name' => 'bendahara',
        'description' => 'Bendahara reuni',
    ]);

    $user = User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($user)
        ->get(route('alumni.distribution.index'))
        ->assertForbidden();
});

test('alumni users can view distribution statistics', function () {
    $viewer = Alumni::factory()->create();
    $country = Country::factory()->create(['name' => 'Indonesia', 'code' => 'ID']);
    $city = City::factory()->create([
        'country_id' => $country->id,
        'name' => 'Yogyakarta',
        'latitude' => -7.7956,
        'longitude' => 110.3695,
    ]);

    Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'current_country_id' => $country->id,
        'current_city_id' => $city->id,
        'rsvp_status' => 'attending',
        'is_profile_completed' => true,
    ]);

    Alumni::factory()->create([
        'full_name' => 'Budi Santoso',
        'current_country_id' => $country->id,
        'current_city_id' => $city->id,
        'rsvp_status' => 'pending',
    ]);
    AlumniTimeline::factory()->create([
        'alumni_id' => $viewer->id,
        'year' => 1996,
        'month' => 8,
        'country_id' => $country->id,
        'city_id' => $city->id,
        'notes' => 'Mulai kuliah.',
    ]);
    AlumniTimeline::factory()->create([
        'year' => 2001,
        'month' => null,
        'country_id' => $country->id,
        'city_id' => $city->id,
    ]);

    $this->actingAs($viewer->user)
        ->get(route('alumni.distribution.index'))
        ->assertOk()
        ->assertSee('Persebaran Alumni')
        ->assertSee('Total alumni')
        ->assertSee('Peta Persebaran')
        ->assertSee('marker kota')
        ->assertSee('Detail Lokasi')
        ->assertSee('Koordinat: -7.7956, 110.3695')
        ->assertSee('Ade Chandra')
        ->assertSee('Budi Santoso')
        ->assertSee('Status RSVP')
        ->assertSee('Indonesia')
        ->assertSee('Yogyakarta')
        ->assertSee('Catatan timeline')
        ->assertSee('Lintasan Tahun')
        ->assertSee('Titik Historis Teratas')
        ->assertSee('1996')
        ->assertSee('2001');
});

test('alumni users can select a city marker to view location alumni', function () {
    $viewer = Alumni::factory()->create();
    $country = Country::factory()->create(['name' => 'Indonesia', 'code' => 'ID']);
    $yogyakarta = City::factory()->create([
        'country_id' => $country->id,
        'name' => 'Yogyakarta',
        'latitude' => -7.7956,
        'longitude' => 110.3695,
    ]);
    $jakarta = City::factory()->create([
        'country_id' => $country->id,
        'name' => 'Jakarta',
        'latitude' => -6.2088,
        'longitude' => 106.8456,
    ]);

    Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'current_country_id' => $country->id,
        'current_city_id' => $yogyakarta->id,
    ]);
    Alumni::factory()->create([
        'full_name' => 'Citra Lestari',
        'current_country_id' => $country->id,
        'current_city_id' => $jakarta->id,
    ]);

    $this->actingAs($viewer->user);

    Livewire::test('pages::alumni.distribution.index')
        ->call('selectCity', $jakarta->id)
        ->assertSet('selectedCityId', $jakarta->id)
        ->assertSee('Jakarta')
        ->assertSee('Citra Lestari')
        ->assertDontSee('Ade Chandra');
});

test('administrator users can view distribution statistics', function () {
    $role = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);

    $administrator = User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($administrator)
        ->get(route('alumni.distribution.index'))
        ->assertOk()
        ->assertSee('Persebaran Alumni');
});
