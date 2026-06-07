<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response
        ->assertOk()
        ->assertSee('Nomor WhatsApp')
        ->assertDontSee('class="dark"', false)
        ->assertDontSee('Flux.applyAppearance', false);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'whatsapp_number' => $user->whatsapp_number,
        'password' => 'password',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'whatsapp_number' => $user->whatsapp_number,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrorsIn('whatsapp_number');

    $this->assertGuest();
});

test('inactive users can not authenticate', function () {
    $user = User::factory()->create(['is_active' => false]);

    $response = $this->post(route('login.store'), [
        'whatsapp_number' => $user->whatsapp_number,
        'password' => 'password',
    ]);

    $response->assertSessionHasErrorsIn('whatsapp_number');

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect(route('home'));

    $this->assertGuest();
});
