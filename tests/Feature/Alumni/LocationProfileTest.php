<?php

use App\Models\City;
use App\Models\Country;
use Database\Seeders\LocationSeeder;

test('location seeder creates countries and cities for alumni profiles', function () {
    $this->seed(LocationSeeder::class);

    $indonesia = Country::query()->where('name', 'Indonesia')->firstOrFail();
    $yogyakarta = City::query()
        ->where('country_id', $indonesia->id)
        ->where('name', 'Yogyakarta')
        ->firstOrFail();

    expect($indonesia->iso_code)->toBe('ID');
    expect($yogyakarta->country->is($indonesia))->toBeTrue();
});
