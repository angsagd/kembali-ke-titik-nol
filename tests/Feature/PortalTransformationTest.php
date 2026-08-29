<?php

use App\Models\Alumni;
use App\Models\MediaItem;
use App\Models\News;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomAssignment;

test('public home is an alumni portal with contained post-event reunion context', function () {
    News::factory()->published()->create([
        'title' => 'Kabar Silaturahmi Geodesi 96',
        'excerpt' => 'Cerita terbaru dari teman seangkatan.',
    ]);
    MediaItem::factory()->photo()->create([
        'title' => 'Jejak Praktikum 1998',
        'visibility' => 'public',
        'year' => 1998,
    ]);
    MediaItem::factory()->photo()->create([
        'title' => 'Dokumentasi Internal Privat',
        'visibility' => 'internal',
    ]);
    Alumni::factory()->create([
        'full_name' => 'Nama Alumni Privat',
        'email' => 'privat@example.test',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSeeInOrder([
            'Portal Alumni Geodesi 96',
            'Tentang Geodesi 96',
            'Kabar Alumni Terbaru',
            'Dokumentasi Pilihan',
            'Reuni 30 Tahun',
            'Masuk sebagai Alumni',
        ])
        ->assertSee('Jejak, cerita, dan perjalanan yang terus terhubung')
        ->assertSee('Kabar Silaturahmi Geodesi 96')
        ->assertSee('Jejak Praktikum 1998')
        ->assertSee('Kembali ke Titik Nol')
        ->assertSee('href="'.route('public.reunion').'"', false)
        ->assertSee('href="'.route('login').'"', false)
        ->assertDontSee('data-countdown-target', false)
        ->assertDontSee('Dokumentasi Internal Privat')
        ->assertDontSee('Nama Alumni Privat')
        ->assertDontSee('privat@example.test');
});

test('alumni dashboard prioritizes profile community journey archive news and reunion summary', function () {
    $alumni = Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'nickname' => 'Ade',
        'city' => 'Denpasar',
        'country' => 'Indonesia',
        'rsvp_status' => 'attending',
    ]);
    Alumni::factory()->create([
        'city' => 'Yogyakarta',
        'country' => 'Indonesia',
        'alumni_status' => 'deceased',
    ]);
    Payment::factory()->create([
        'alumni_id' => $alumni->id,
        'status' => 'paid',
    ]);
    $room = Room::factory()->create(['room_name' => 'Joglo Merapi']);
    RoomAssignment::factory()->create([
        'alumni_id' => $alumni->id,
        'room_id' => $room->id,
    ]);
    News::factory()->published()->create(['title' => 'Kabar Teman Seangkatan']);
    MediaItem::factory()->photo()->create([
        'title' => 'Kenangan Kampus Geodesi',
        'visibility' => 'internal',
        'uploaded_by_alumni_id' => $alumni->id,
    ]);

    $this->actingAs($alumni->user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeInOrder([
            'Selamat datang, Ade',
            'Profil Saya',
            'Arsip Reuni 30 Tahun',
            'Kabar Alumni Terbaru',
            'Perjalanan Alumni',
            'Dokumentasi & Kenangan',
            'Nostalgia Geodesi 96',
        ])
        ->assertSee('Kabar Teman Seangkatan')
        ->assertSee('Kenangan Kampus Geodesi')
        ->assertSee('Joglo Merapi')
        ->assertSee('2 alumni')
        ->assertSee('2 kota')
        ->assertSee('1 negara')
        ->assertSee('href="'.route('reunion.index').'"', false)
        ->assertSee('href="'.route('alumni.profile').'"', false)
        ->assertSee('href="'.route('alumni.distribution.index').'"', false)
        ->assertSee('href="'.route('documentation.index').'"', false)
        ->assertDontSee('Total Dana Terkumpul');
});
