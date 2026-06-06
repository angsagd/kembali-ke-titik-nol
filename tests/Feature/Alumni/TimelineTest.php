<?php

use App\Models\Alumni;
use App\Models\AlumniTimeline;
use App\Models\City;
use App\Models\Country;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

test('guests are redirected from alumni timeline', function () {
    $this->get(route('alumni.timeline.index'))
        ->assertRedirect(route('login'));
});

test('users without alumni profile cannot access alumni timeline', function () {
    $role = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);

    $user = User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($user)
        ->get(route('alumni.timeline.index'))
        ->assertForbidden();
});

test('alumni users can view their timeline page', function () {
    $profile = Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
    ]);

    $this->actingAs($profile->user)
        ->get(route('alumni.timeline.index'))
        ->assertOk()
        ->assertSee('Timeline Lokasi')
        ->assertSee('Tambah Lokasi');
});

test('alumni users can create update and delete their own timeline entries', function () {
    $profile = Alumni::factory()->create();
    $country = Country::factory()->create(['name' => 'Indonesia', 'code' => 'ID']);
    $city = City::factory()->create(['country_id' => $country->id, 'name' => 'Yogyakarta']);

    $this->actingAs($profile->user);

    Livewire::test('pages::alumni.timeline.index')
        ->set('year', 1996)
        ->set('month', 8)
        ->set('country_id', $country->id)
        ->set('city_id', $city->id)
        ->set('notes', 'Mulai kuliah di Geodesi.')
        ->call('saveTimeline')
        ->assertHasNoErrors();

    $timeline = AlumniTimeline::query()->where('alumni_id', $profile->id)->firstOrFail();

    expect($timeline->year)->toBe(1996);
    expect($timeline->month)->toBe(8);
    expect($timeline->city_id)->toBe($city->id);
    expect($timeline->country_id)->toBe($country->id);

    Livewire::test('pages::alumni.timeline.index')
        ->call('editTimeline', $timeline->id)
        ->set('year', 2001)
        ->set('month', null)
        ->set('notes', 'Pindah kerja pertama.')
        ->call('saveTimeline')
        ->assertHasNoErrors();

    $timeline->refresh();

    expect($timeline->year)->toBe(2001);
    expect($timeline->month)->toBeNull();
    expect($timeline->notes)->toBe('Pindah kerja pertama.');

    Livewire::test('pages::alumni.timeline.index')
        ->call('deleteTimeline', $timeline->id);

    expect(AlumniTimeline::query()->whereKey($timeline->id)->exists())->toBeFalse();
});

test('alumni users cannot mutate another alumni timeline entry', function () {
    $viewer = Alumni::factory()->create();
    $other = Alumni::factory()->create();
    $timeline = AlumniTimeline::factory()->create([
        'alumni_id' => $other->id,
        'year' => 1996,
    ]);

    $this->actingAs($viewer->user);

    expect(fn () => Livewire::test('pages::alumni.timeline.index')
        ->call('editTimeline', $timeline->id))
        ->toThrow(ModelNotFoundException::class);

    expect(fn () => Livewire::test('pages::alumni.timeline.index')
        ->call('deleteTimeline', $timeline->id))
        ->toThrow(ModelNotFoundException::class);

    expect($timeline->fresh())->not->toBeNull();
});

test('alumni timeline rejects city outside selected country', function () {
    $profile = Alumni::factory()->create();
    $indonesia = Country::factory()->create(['name' => 'Indonesia', 'code' => 'ID']);
    $malaysia = Country::factory()->create(['name' => 'Malaysia', 'code' => 'MY']);
    $kualaLumpur = City::factory()->create(['country_id' => $malaysia->id, 'name' => 'Kuala Lumpur']);

    $this->actingAs($profile->user);

    Livewire::test('pages::alumni.timeline.index')
        ->set('year', 2001)
        ->set('country_id', $indonesia->id)
        ->set('city_id', $kualaLumpur->id)
        ->call('saveTimeline')
        ->assertHasErrors([
            'city_id' => 'exists',
        ]);

    expect(AlumniTimeline::query()->where('alumni_id', $profile->id)->exists())->toBeFalse();
});
