<?php

test('self registration is not available', function () {
    $this->get('/register')->assertNotFound();

    $this->post('/register', [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'whatsapp_number' => '6281234567890',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertNotFound();
});
