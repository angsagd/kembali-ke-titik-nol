<?php

use App\Models\Alumni;
use App\Models\Donation;
use App\Models\MediaItem;
use App\Models\User;

test('public landing page does not expose sensitive alumni finance or whatsapp data', function () {
    $user = User::factory()->create([
        'name' => 'Donatur Terbuka',
        'whatsapp_number' => '6281234567890',
    ]);
    $publicAlumni = Alumni::factory()->create([
        'user_id' => $user->id,
        'full_name' => 'Donatur Terbuka',
    ]);
    $anonymousAlumni = Alumni::factory()->create(['full_name' => 'Donatur Rahasia']);

    Donation::factory()->create([
        'alumni_id' => $publicAlumni->id,
        'amount' => 1234567,
        'publication_status' => 'show_name',
    ]);
    Donation::factory()->create([
        'alumni_id' => $anonymousAlumni->id,
        'amount' => 87654321,
        'publication_status' => 'anonymous',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Donatur Terbuka')
        ->assertDontSee('6281234567890')
        ->assertDontSee('1234567')
        ->assertDontSee('87654321')
        ->assertDontSee('Donatur Rahasia');
});

test('public gallery only exposes public documentation', function () {
    MediaItem::factory()->photo()->create([
        'title' => 'Foto Publik Aman',
        'visibility' => 'public',
    ]);
    MediaItem::factory()->photo()->create([
        'title' => 'Foto Internal Privat',
        'visibility' => 'internal',
    ]);

    $this->get(route('public.gallery'))
        ->assertOk()
        ->assertSee('Foto Publik Aman')
        ->assertDontSee('Foto Internal Privat');
});

test('public pages do not expose raw whatsapp chat content', function () {
    $rawChatLine = '01/01/2026, 08:00 - Ade: pesan privat grup';

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee($rawChatLine)
        ->assertDontSee('pesan privat grup');

    $this->get(route('public.gallery'))
        ->assertOk()
        ->assertDontSee($rawChatLine)
        ->assertDontSee('pesan privat grup');
});
