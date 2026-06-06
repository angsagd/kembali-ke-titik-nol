<?php

use App\Models\User;
use Livewire\Livewire;

test('profile page is displayed', function () {
    $this->actingAs($user = User::factory()->create());

    $this->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Pengaturan Akun')
        ->assertSee('Nomor WhatsApp')
        ->assertDontSee('Delete account');
});

test('account whatsapp number can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('whatsapp_number', '6281234567899')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->whatsapp_number)->toEqual('6281234567899');
});

test('account whatsapp number must be unique', function () {
    $existing = User::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('pages::settings.profile')
        ->set('whatsapp_number', $existing->whatsapp_number)
        ->call('updateProfileInformation');

    $response->assertHasErrors(['whatsapp_number' => 'unique']);
});
