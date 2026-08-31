<?php

use App\Models\Alumni;
use App\Models\MediaItem;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('directory supports permanent location filters and respectful photo-based memorial cards', function () {
    Storage::fake('public');

    $viewer = Alumni::factory()->create();
    $memorial = Alumni::factory()->create([
        'full_name' => 'Budi Memorial',
        'nickname' => 'Budi',
        'city' => 'Denpasar',
        'country' => 'Indonesia',
        'alumni_status' => 'deceased',
        'current_photo_path' => 'alumni/memory-book/current/budi.webp',
    ]);
    Alumni::factory()->create([
        'full_name' => 'Citra Lestari',
        'city' => 'Melbourne',
        'country' => 'Australia',
    ]);

    Storage::disk('public')->put($memorial->current_photo_path, 'photo');

    $this->actingAs($viewer->user);

    Livewire::test('pages::alumni.directory.index')
        ->set('city', 'Denpasar')
        ->set('country', 'Indonesia')
        ->set('status', 'deceased')
        ->assertSee('Budi Memorial')
        ->assertSee('In Memoriam')
        ->assertSee(Storage::disk('public')->url($memorial->current_photo_path), false)
        ->assertDontSee('Citra Lestari');
});

test('permanent alumni profile presents uploaded and tagged galleries', function () {
    $viewer = Alumni::factory()->create();
    $profile = Alumni::factory()->create([
        'full_name' => 'Dedi Geodesi',
        'alumni_status' => 'deceased',
    ]);
    $uploader = MediaItem::factory()->photo()->create([
        'uploaded_by_alumni_id' => $profile->id,
        'title' => 'Dokumentasi Unggahan Dedi',
    ]);
    $tagged = MediaItem::factory()->photo()->create([
        'title' => 'Dokumentasi yang Menandai Dedi',
    ]);
    $tagged->taggedAlumni()->attach($profile->id, ['tagged_by_alumni_id' => $tagged->uploaded_by_alumni_id]);

    $this->actingAs($viewer->user)
        ->get(route('alumni.directory.show', $profile))
        ->assertOk()
        ->assertSee('In Memoriam')
        ->assertSee('Dokumentasi yang Diunggah')
        ->assertSee($uploader->title)
        ->assertSee('Dokumentasi yang Menandai Alumni')
        ->assertSee($tagged->title)
        ->assertDontSee('Status RSVP');
});

test('marking alumni as deceased disables login account without deleting historical profile', function () {
    $administratorRole = Role::factory()->create(['name' => 'administrator']);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);
    $profile = Alumni::factory()->create([
        'full_name' => 'Eko Alumni',
        'alumni_status' => 'active',
    ]);

    $this->actingAs($administrator);

    Livewire::test('pages::admin.alumni.show', ['alumni' => $profile])
        ->set('alumni_status', 'deceased')
        ->call('updateAlumni')
        ->assertHasNoErrors();

    expect($profile->fresh()->alumni_status)->toBe('deceased')
        ->and($profile->user->fresh()->is_active)->toBeFalse()
        ->and(Alumni::query()->find($profile->id))->not->toBeNull();
});
