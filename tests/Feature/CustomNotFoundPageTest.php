<?php

use App\Models\User;

test('custom 404 page is shown for unknown routes for guests', function () {
    $this->get('/halaman-yang-tidak-ada')
        ->assertNotFound()
        ->assertSee('404: Koordinat', false)
        ->assertSee('Tidak Ditemukan', false)
        ->assertSee('ERR_NOT_FOUND', false)
        ->assertSee('images/errors/404.png', false)
        ->assertSee('Kembali ke Titik Nol', false)
        ->assertDontSee('Kembali ke Dashboard', false);
});

test('custom 404 page is shown with app layout for authenticated users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/halaman-yang-tidak-ada')
        ->assertNotFound()
        ->assertSee('404: Koordinat', false)
        ->assertSee('Tidak Ditemukan', false)
        ->assertSee('ERR_NOT_FOUND', false)
        ->assertSee('images/errors/404.png', false)
        ->assertSee('Kembali ke Titik Nol', false)
        ->assertSee('href="http://localhost/dashboard"', false);
});
