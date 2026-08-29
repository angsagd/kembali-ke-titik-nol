<?php

test('public landing page is displayed', function () {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('Portal Alumni Geodesi 96')
        ->assertSee('Jejak, cerita, dan perjalanan yang terus terhubung')
        ->assertSee('Tentang Geodesi 96')
        ->assertSee('Kabar Alumni Terbaru')
        ->assertSee('Dokumentasi Pilihan')
        ->assertSee('Kembali ke Titik Nol')
        ->assertSee('Masuk sebagai Alumni')
        ->assertSee('Kembali ke bagian atas halaman');
});
