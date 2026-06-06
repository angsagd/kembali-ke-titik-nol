<?php

test('two factor authentication challenge is not available', function () {
    $this->get('/two-factor-challenge')->assertNotFound();
    $this->post('/two-factor-challenge', ['code' => '123456'])->assertNotFound();
});
