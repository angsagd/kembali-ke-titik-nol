<?php

test('custom 404 page is shown for unknown routes', function () {
    $this->get('/halaman-yang-tidak-ada')
        ->assertNotFound()
        ->assertSee('404: Koordinat', false)
        ->assertSee('Tidak Ditemukan', false)
        ->assertSee('ERR_NOT_FOUND', false)
        ->assertSee('images/errors/404.png', false);
});
