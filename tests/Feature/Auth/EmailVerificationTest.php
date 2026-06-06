<?php

test('email verification is not available', function () {
    $this->get('/email/verify')->assertNotFound();
    $this->post('/email/verification-notification')->assertNotFound();
});
