<?php

test('public landing page is displayed', function () {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('Kembali ke Titik Nol')
        ->assertSee('Ngalibrasi 30 Taon Paseduluran')
        ->assertSee('Rangkaian Acara')
        ->assertSee('Kembali ke bagian atas halaman');
});
