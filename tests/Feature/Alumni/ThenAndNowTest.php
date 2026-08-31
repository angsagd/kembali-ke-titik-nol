<?php

use App\Models\Alumni;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('then and now is restricted to the internal alumni portal', function () {
    $this->get(route('alumni.then-now.index'))->assertRedirect(route('login'));
});

test('then and now presents every eligible photo combination and links to profiles', function () {
    Storage::fake('public');

    $viewer = Alumni::factory()->create();
    $both = Alumni::factory()->create([
        'full_name' => 'Alumni Dua Foto',
        'college_photo_path' => 'alumni/college/both.webp',
        'current_photo_path' => 'alumni/current/both.webp',
    ]);
    $oldOnly = Alumni::factory()->create([
        'full_name' => 'Alumni Foto Lama',
        'college_photo_path' => 'alumni/college/old.webp',
    ]);
    $currentOnly = Alumni::factory()->create([
        'full_name' => 'Alumni Foto Kini',
        'current_photo_path' => 'alumni/current/current.webp',
    ]);
    Alumni::factory()->create(['full_name' => 'Alumni Tanpa Foto']);
    $memorial = Alumni::factory()->create([
        'full_name' => 'Alumni Memorial',
        'alumni_status' => 'deceased',
        'college_photo_path' => 'alumni/college/memorial.webp',
    ]);

    $this->actingAs($viewer->user)
        ->get(route('alumni.then-now.index'))
        ->assertOk()
        ->assertSee('Dulu &amp; Sekarang', false)
        ->assertSee('Alumni Dua Foto')
        ->assertSee('Alumni Foto Lama')
        ->assertSee('Alumni Foto Kini')
        ->assertSee('Alumni Memorial')
        ->assertSee('In Memoriam')
        ->assertDontSee('Alumni Tanpa Foto')
        ->assertSee(route('alumni.directory.show', $both), false)
        ->assertSee(route('alumni.directory.show', $oldOnly), false)
        ->assertSee(route('alumni.directory.show', $currentOnly), false)
        ->assertSee(route('alumni.directory.show', $memorial), false);
});

test('then and now filters by status and search term', function () {
    $viewer = Alumni::factory()->create();
    Alumni::factory()->create(['full_name' => 'Aktif Bandung', 'current_photo_path' => 'active.webp']);
    Alumni::factory()->create(['full_name' => 'Memorial Makassar', 'alumni_status' => 'deceased', 'college_photo_path' => 'memorial.webp']);

    $this->actingAs($viewer->user);

    Livewire::test('pages::alumni.then-now.index')
        ->set('status', 'deceased')
        ->assertSee('Memorial Makassar')
        ->assertDontSee('Aktif Bandung')
        ->set('search', 'tidak ditemukan')
        ->assertSee('Belum ada foto yang cocok');
});
