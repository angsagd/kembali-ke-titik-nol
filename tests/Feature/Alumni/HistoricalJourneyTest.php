<?php

use App\Models\Alumni;
use App\Models\AlumniTimeline;
use App\Models\Role;
use App\Models\User;
use App\Services\HistoricalLocationResolver;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

test('current coordinates record their source and manual overrides are protected', function () {
    expect(Schema::hasColumn('alumni', 'coordinate_source'))->toBeTrue();

    $administratorRole = Role::factory()->create(['name' => 'administrator']);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);
    $profile = Alumni::factory()->create([
        'city' => 'Yogyakarta',
        'country' => 'Indonesia',
        'latitude' => -7.7956,
        'longitude' => 110.3695,
        'coordinate_source' => 'geocoded',
    ]);

    $this->actingAs($administrator);
    Livewire::test('pages::admin.alumni.show', ['alumni' => $profile])
        ->set('latitude', -7.8001)
        ->set('longitude', 110.3701)
        ->set('coordinate_source', 'manual')
        ->call('updateAlumni')
        ->assertHasNoErrors();

    $profile->refresh();
    expect($profile->coordinate_source)->toBe('manual');

    $this->actingAs($profile->user);
    Livewire::test('pages::alumni.profile')
        ->set('location_search', 'Denpasar, Indonesia')
        ->set('city', 'Denpasar')
        ->set('country', 'Indonesia')
        ->set('latitude', -8.6500)
        ->set('longitude', 115.2167)
        ->call('updateProfile')
        ->assertHasNoErrors();

    $profile->refresh();
    expect($profile->city)->toBe('Denpasar')
        ->and((string) $profile->latitude)->toBe('-7.8001000')
        ->and((string) $profile->longitude)->toBe('110.3701000')
        ->and($profile->coordinate_source)->toBe('manual');
});

test('historical resolver uses the latest applicable location without assuming early history', function () {
    $traveller = Alumni::factory()->create(['full_name' => 'Alumni Perantau']);
    $lateStarter = Alumni::factory()->create(['full_name' => 'Alumni Tanpa Riwayat Awal']);

    foreach ([
        [1996, 8, 'Yogyakarta', -7.7956, 110.3695],
        [2002, null, 'Jakarta', -6.2088, 106.8456],
        [2015, 6, 'Denpasar', -8.6500, 115.2167],
    ] as [$year, $month, $city, $latitude, $longitude]) {
        AlumniTimeline::factory()->create(compact('year', 'month', 'city', 'latitude', 'longitude') + [
            'alumni_id' => $traveller->id,
            'country' => 'Indonesia',
        ]);
    }

    AlumniTimeline::factory()->create([
        'alumni_id' => $lateStarter->id,
        'year' => 2015,
        'city' => 'Surabaya',
        'country' => 'Indonesia',
        'latitude' => -7.2575,
        'longitude' => 112.7521,
    ]);

    $resolver = app(HistoricalLocationResolver::class);
    $locationsIn2010 = $resolver->resolve(2010);
    $locationsIn2020 = $resolver->resolve(2020);

    expect($locationsIn2010->pluck('city')->all())->toBe(['Jakarta'])
        ->and($locationsIn2010->first()['alumni']->pluck('id')->all())->toBe([$traveller->id])
        ->and($locationsIn2020->pluck('city')->all())->toContain('Denpasar', 'Surabaya');
});

test('historical map can render a selected year with alumni profile links', function () {
    $viewer = Alumni::factory()->create();
    $traveller = Alumni::factory()->create(['full_name' => 'Alumni Historis']);
    AlumniTimeline::factory()->create([
        'alumni_id' => $traveller->id,
        'year' => 1996,
        'city' => 'Yogyakarta',
        'country' => 'Indonesia',
        'latitude' => -7.7956,
        'longitude' => 110.3695,
    ]);

    $this->actingAs($viewer->user);

    Livewire::test('pages::alumni.distribution.index')
        ->set('selectedYear', 2010)
        ->assertSee('Peta Perjalanan Historis')
        ->assertSee('Yogyakarta')
        ->assertSee('Alumni Historis')
        ->assertSee(route('alumni.directory.show', $traveller), false);
});
