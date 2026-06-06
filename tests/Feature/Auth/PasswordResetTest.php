<?php

test('email based password reset is not available', function () {
    $this->get('/forgot-password')->assertNotFound();
    $this->post('/forgot-password', ['email' => 'alumni@example.test'])->assertNotFound();
    $this->get('/reset-password/token')->assertNotFound();
    $this->post('/reset-password', [
        'token' => 'token',
        'email' => 'alumni@example.test',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertNotFound();
});
