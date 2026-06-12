<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SuperadminSeeder;

function loginPayload(array $attributes = []): array
{
    $token = csrf_token();

    return array_merge(['_token' => $token], $attributes);
}

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response
        ->assertOk()
        ->assertSee('Nomor WhatsApp')
        ->assertSee('hero-kontur')
        ->assertDontSee('class="dark"', false)
        ->assertDontSee('Flux.applyAppearance', false);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();
    $this->get(route('login'));

    $response = $this->post(route('login.store'), loginPayload([
        'whatsapp_number' => $user->whatsapp_number,
        'password' => 'password',
    ]));

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
});

test('configured bootstrap administrator can authenticate using the login screen', function () {
    $this->seed([
        RoleSeeder::class,
        SuperadminSeeder::class,
    ]);

    $user = User::query()->where('whatsapp_number', '628100000002')->firstOrFail();
    $this->get(route('login'));

    $response = $this->post(route('login.store'), loginPayload([
        'whatsapp_number' => '628100000002',
        'password' => 'tgd0002',
    ]));

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticatedAs($user);
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();
    $this->get(route('login'));

    $response = $this->post(route('login.store'), loginPayload([
        'whatsapp_number' => $user->whatsapp_number,
        'password' => 'wrong-password',
    ]));

    $response->assertSessionHasErrorsIn('whatsapp_number');

    $this->assertGuest();
});

test('inactive users can not authenticate', function () {
    $user = User::factory()->create(['is_active' => false]);
    $this->get(route('login'));

    $response = $this->post(route('login.store'), loginPayload([
        'whatsapp_number' => $user->whatsapp_number,
        'password' => 'password',
    ]));

    $response->assertSessionHasErrorsIn('whatsapp_number');

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();
    $this->get(route('login'));

    $response = $this->actingAs($user)->post(route('logout'), loginPayload());

    $response->assertRedirect(route('home'));

    $this->assertGuest();
});
