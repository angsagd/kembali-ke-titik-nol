<?php

use App\Models\Alumni;
use App\Models\City;
use App\Models\Country;
use App\Models\MediaItem;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected from memory book', function () {
    $this->get(route('memory-book.index'))
        ->assertRedirect(route('login'));
});

test('users without alumni profile cannot access memory book', function () {
    $role = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $user = User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($user)
        ->get(route('memory-book.index'))
        ->assertForbidden();
});

test('alumni users can browse memory book', function () {
    $viewer = Alumni::factory()->create();
    Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'nickname' => 'Ade',
        'short_story' => 'Menjadi surveyor yang tetap pulang ke titik nol.',
        'memorable_story' => 'Praktikum lapangan yang tidak terlupakan.',
        'message_to_friends' => 'Sampai jumpa di reuni.',
        'is_profile_completed' => true,
    ]);

    $this->actingAs($viewer->user)
        ->get(route('memory-book.index'))
        ->assertOk()
        ->assertSee('Buku Kenangan Digital')
        ->assertSee('Ade Chandra')
        ->assertSee('Menjadi surveyor')
        ->assertSee('Baca Buku Kenangan');
});

test('alumni users can filter memory book by search city and status', function () {
    $viewer = Alumni::factory()->create();
    $country = Country::factory()->create(['name' => 'Indonesia']);
    $yogyakarta = City::factory()->create(['country_id' => $country->id, 'name' => 'Yogyakarta']);
    $jakarta = City::factory()->create(['country_id' => $country->id, 'name' => 'Jakarta']);

    Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'current_city_id' => $yogyakarta->id,
        'current_country_id' => $country->id,
        'alumni_status' => 'active',
        'short_story' => 'Cerita dari Jogja.',
    ]);
    Alumni::factory()->create([
        'full_name' => 'Budi Santoso',
        'current_city_id' => $jakarta->id,
        'current_country_id' => $country->id,
        'alumni_status' => 'deceased',
        'short_story' => 'Cerita dari Jakarta.',
    ]);

    $this->actingAs($viewer->user);

    Livewire::test('pages::memory-book.index')
        ->set('search', 'Ade')
        ->assertSee('Ade Chandra')
        ->assertDontSee('Budi Santoso')
        ->set('search', '')
        ->set('cityId', $jakarta->id)
        ->assertSee('Budi Santoso')
        ->assertDontSee('Ade Chandra')
        ->set('cityId', '')
        ->set('status', 'deceased')
        ->assertSee('Budi Santoso')
        ->assertDontSee('Ade Chandra');
});

test('alumni users can filter memory book by story memory message and memorial sections', function () {
    $viewer = Alumni::factory()->create();

    Alumni::factory()->create([
        'full_name' => 'Ade Cerita',
        'short_story' => 'Cerita utama Ade.',
    ]);
    Alumni::factory()->create([
        'full_name' => 'Budi Kenangan',
        'memorable_story' => 'Kenangan utama Budi.',
    ]);
    Alumni::factory()->create([
        'full_name' => 'Citra Pesan',
        'message_to_friends' => 'Pesan utama Citra.',
    ]);
    Alumni::factory()->create([
        'full_name' => 'Dedi Memorial',
        'alumni_status' => 'deceased',
    ]);

    $this->actingAs($viewer->user);

    Livewire::test('pages::memory-book.index')
        ->assertSee('Seluruh Alumni')
        ->assertSee('Cerita Alumni')
        ->assertSee('Kenangan Alumni')
        ->assertSee('Pesan Alumni')
        ->assertSee('Memorial Alumni')
        ->set('section', 'story')
        ->assertSee('Ade Cerita')
        ->assertDontSee('Budi Kenangan')
        ->set('section', 'memory')
        ->assertSee('Budi Kenangan')
        ->assertDontSee('Citra Pesan')
        ->set('section', 'message')
        ->assertSee('Citra Pesan')
        ->assertDontSee('Ade Cerita')
        ->set('section', 'memorial')
        ->assertSee('Dedi Memorial')
        ->assertDontSee('Ade Cerita');
});

test('alumni users can read memory book detail with related documentation', function () {
    $viewer = Alumni::factory()->create();
    $profile = Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'student_number' => '96A001',
        'email' => 'ade@example.test',
        'short_story' => 'Cerita singkat Ade.',
        'memorable_story' => 'Kenangan kuliah Ade.',
        'message_to_friends' => 'Pesan untuk teman Ade.',
    ]);
    $taggedMedia = MediaItem::factory()->photo()->create([
        'title' => 'Foto Bareng Ade',
        'year' => 2026,
    ]);
    $taggedMedia->taggedAlumni()->attach($profile->id, ['tagged_by_alumni_id' => $viewer->id]);

    $this->actingAs($viewer->user)
        ->get(route('memory-book.show', $profile))
        ->assertOk()
        ->assertSee('Ade Chandra')
        ->assertSee('96A001')
        ->assertSee($profile->user->whatsapp_number)
        ->assertSee('ade@example.test')
        ->assertSee('Cerita Singkat')
        ->assertSee('Cerita singkat Ade.')
        ->assertSee('Kenangan kuliah Ade.')
        ->assertSee('Pesan untuk teman Ade.')
        ->assertSee('Dokumentasi Terkait')
        ->assertSee('Foto Bareng Ade')
        ->assertSee('Profil Direktori');
});

test('deceased alumni memory book detail is shown as memorial page', function () {
    $viewer = Alumni::factory()->create();
    $profile = Alumni::factory()->create([
        'full_name' => 'Budi Memorial',
        'alumni_status' => 'deceased',
        'message_to_friends' => 'Kenangan tentang Budi.',
    ]);

    $this->actingAs($viewer->user)
        ->get(route('memory-book.show', $profile))
        ->assertOk()
        ->assertSee('Budi Memorial')
        ->assertSee('Halaman Memorial')
        ->assertSee('Profil ini dipertahankan sebagai arsip kenangan bersama.');
});
