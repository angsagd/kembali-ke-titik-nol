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

test('landing page exposes social sharing metadata', function () {
    $title = 'Reuni 30 Tahun Geodesi 96 - Kembali ke Titik Nol';
    $description = 'Ngalibrasi 30 Taon Paseduluran. Reuni alumni Teknik Geodesi UGM angkatan 96 untuk pulang, bertemu, mengenang, dan kembali ke titik nol bersama.';
    $url = 'https://geodesiugm96.web.id';
    $image = 'https://geodesiugm96.web.id/images/brand/sticker-kembali-ke-titik-nol.png';

    $this->get(route('home'))
        ->assertOk()
        ->assertSee("<title>{$title}</title>", false)
        ->assertSee('<meta name="description" content="'.$description.'">', false)
        ->assertSee('<link rel="canonical" href="'.$url.'">', false)
        ->assertSee('<meta property="og:site_name" content="Kembali ke Titik Nol">', false)
        ->assertSee('<meta property="og:title" content="'.$title.'">', false)
        ->assertSee('<meta property="og:description" content="'.$description.'">', false)
        ->assertSee('<meta property="og:url" content="'.$url.'">', false)
        ->assertSee('<meta property="og:image" content="'.$image.'">', false)
        ->assertSee('<meta property="og:image:type" content="image/png">', false)
        ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
        ->assertSee('<meta name="twitter:image" content="'.$image.'">', false);
});

test('landing page footer exposes instagram and whatsapp committee contacts', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Kontak Panitia')
        ->assertSee('https://www.instagram.com/titiknol.tgd96')
        ->assertSee('@titiknol.tgd96')
        ->assertSee('https://wa.me/6281931720792?text=Halo%20panitia%20reuni%20tgd96')
        ->assertSee('+6281931720792');
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
