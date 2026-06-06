<?php

use App\Models\Alumni;
use App\Models\City;
use App\Models\Country;
use App\Models\Role;
use App\Models\User;

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
    $country = Country::factory()->create(['name' => 'Indonesia', 'iso_code' => 'ID']);
    $city = City::factory()->create(['country_id' => $country->id, 'name' => 'Yogyakarta']);

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

    $this->actingAs($viewer->user)
        ->get(route('alumni.distribution.index'))
        ->assertOk()
        ->assertSee('Persebaran Alumni')
        ->assertSee('Total alumni')
        ->assertSee('Status RSVP')
        ->assertSee('Indonesia')
        ->assertSee('Yogyakarta');
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
