<?php

use App\Models\Alumni;
use App\Models\Donation;
use App\Models\MediaItem;
use App\Models\News;
use Livewire\Livewire;

test('guests can view public gallery page', function () {
    MediaItem::factory()->photo()->create([
        'title' => 'Foto Publik Reuni',
        'visibility' => 'public',
        'year' => 2026,
    ]);
    MediaItem::factory()->photo()->create([
        'title' => 'Foto Internal Panitia',
        'visibility' => 'internal',
        'year' => 2026,
    ]);

    $this->get(route('public.gallery'))
        ->assertOk()
        ->assertSee('Galeri Publik')
        ->assertSee('Foto Publik Reuni')
        ->assertDontSee('Foto Internal Panitia');
});

test('public gallery can filter by media type', function () {
    MediaItem::factory()->photo()->create([
        'title' => 'Foto Publik Reuni',
        'visibility' => 'public',
    ]);
    MediaItem::factory()->video()->create([
        'title' => 'Video Publik Reuni',
        'visibility' => 'public',
    ]);

    Livewire::test('pages::public.gallery')
        ->set('type', 'video')
        ->assertSee('Video Publik Reuni')
        ->assertDontSee('Foto Publik Reuni');
});

test('landing page highlights public media only', function () {
    MediaItem::factory()->photo()->create([
        'title' => 'Foto Highlight Publik',
        'visibility' => 'public',
    ]);
    MediaItem::factory()->photo()->create([
        'title' => 'Foto Internal Panitia',
        'visibility' => 'internal',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Lihat Galeri Publik')
        ->assertSee('Foto Highlight Publik')
        ->assertDontSee('Foto Internal Panitia');
});

test('landing page uses dynamic countdown target for reunion date', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('data-countdown-target="2026-08-23T12:00:00+07:00"', false)
        ->assertSee('data-countdown-unit="days"', false)
        ->assertSee('data-countdown-unit="hours"', false)
        ->assertSee('data-countdown-unit="minutes"', false)
        ->assertSee('data-countdown-unit="seconds"', false);
});

test('landing page uses provided icon assets for favicon and header logo', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('/images/icon/favicon48.png')
        ->assertSee('/images/icon/favicon96.png')
        ->assertSee('/images/icon/favicon192.png')
        ->assertSee('/site.webmanifest')
        ->assertSee('alt="Logo Geodesi 96"', false);
});

test('landing page highlights published news only', function () {
    News::factory()->published()->create([
        'title' => 'Rilis Rundown Reuni',
        'excerpt' => 'Rundown resmi sudah tersedia.',
    ]);
    News::factory()->draft()->create([
        'title' => 'Draft Belum Publik',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Berita dan Pengumuman')
        ->assertSee('Rilis Rundown Reuni')
        ->assertSee('Rundown resmi sudah tersedia.')
        ->assertDontSee('Draft Belum Publik');
});

test('landing page lists public donors and hides anonymous donor details', function () {
    $publicAlumni = Alumni::factory()->create(['full_name' => 'Donatur Terbuka']);
    $anonymousAlumni = Alumni::factory()->create(['full_name' => 'Donatur Rahasia']);

    Donation::factory()->create([
        'alumni_id' => $publicAlumni->id,
        'amount' => 1234567,
        'publication_status' => 'show_name',
    ]);
    Donation::factory()->create([
        'alumni_id' => $anonymousAlumni->id,
        'amount' => 87654321,
        'publication_status' => 'anonymous',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Donatur Terbuka')
        ->assertSee('1 donatur memilih ditampilkan sebagai anonim.')
        ->assertDontSee('Donatur Rahasia')
        ->assertDontSee('87654321');
});
