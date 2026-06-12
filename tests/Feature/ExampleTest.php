<?php

test('public landing page is displayed', function () {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('Kembali ke Titik Nol')
        ->assertSee('Ngalibrasi 30 Taon Paseduluran')
        ->assertSee('Rangkaian Acara')
        ->assertSee('src="'.asset('videos/titiknol.mp4').'"', false)
        ->assertSee('poster="'.asset('videos/titiknol-movie-poster.webp').'"', false)
        ->assertSee('autoplay', false)
        ->assertSee('muted', false)
        ->assertSee('loop', false)
        ->assertSee('playsinline', false)
        ->assertSee('motion-reduce:hidden', false)
        ->assertSee('motion-reduce:block', false)
        ->assertSee('Kembali ke bagian atas halaman');

    expect(public_path('videos/titiknol.mp4'))->toBeFile()
        ->and(public_path('videos/titiknol-movie-poster.webp'))->toBeFile();
});
