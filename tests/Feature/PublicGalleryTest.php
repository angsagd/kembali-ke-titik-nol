<?php

use App\Models\MediaItem;
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
