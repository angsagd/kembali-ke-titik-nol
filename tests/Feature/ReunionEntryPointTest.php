<?php

use App\Models\Alumni;
use App\Models\Donation;
use App\Models\EventScheduleItem;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\User;

test('public reunion entry presents the post-event archive without exposing alumni data', function () {
    EventScheduleItem::factory()->create([
        'event_day' => 'day_one',
        'start_time' => '16:30',
        'activity' => 'Temu Kangen Geodesi 96',
    ]);
    Alumni::factory()->create(['full_name' => 'Nama Alumni Privat']);

    $this->get(route('public.reunion'))
        ->assertOk()
        ->assertSee('Reuni 30 Tahun')
        ->assertSee('Kembali ke Titik Nol')
        ->assertSee('23–24 Agustus 2026')
        ->assertSee('Temu Kangen Geodesi 96')
        ->assertSee('Penginapan Joglo / Kampung Wisata Tembi')
        ->assertSee('Departemen Teknik Geodesi UGM')
        ->assertSee('Arsip perjalanan pulang')
        ->assertSee('href="'.route('public.gallery').'"', false)
        ->assertSee('href="'.route('login').'"', false)
        ->assertDontSee('Nama Alumni Privat')
        ->assertDontSee('Status Pembayaran Saya');
});

test('public navigation uses the dedicated reunion entry point', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('href="'.route('public.reunion').'"', false);
});

test('internal reunion overview requires authentication', function () {
    $this->get(route('reunion.index'))
        ->assertRedirect(route('login'));
});

test('alumni reunion overview consolidates personal status and existing reunion destinations', function () {
    $alumni = Alumni::factory()->create([
        'rsvp_status' => 'attending',
    ]);
    Payment::factory()->create([
        'alumni_id' => $alumni->id,
        'status' => 'paid',
    ]);
    Donation::factory()->create([
        'alumni_id' => $alumni->id,
        'publication_status' => 'anonymous',
    ]);
    $room = Room::factory()->create(['room_name' => 'Joglo Merapi']);
    RoomAssignment::factory()->create([
        'alumni_id' => $alumni->id,
        'room_id' => $room->id,
    ]);
    EventScheduleItem::factory()->create([
        'event_day' => 'day_two',
        'start_time' => '09:00',
        'activity' => 'Kembali ke Kampus',
    ]);

    $this->actingAs($alumni->user)
        ->get(route('reunion.index'))
        ->assertOk()
        ->assertSee('Reuni 30 Tahun')
        ->assertSee('Kembali ke Titik Nol')
        ->assertSee('Hadir')
        ->assertSee('Lunas')
        ->assertSee('Donatur Anonim')
        ->assertSee('Joglo Merapi')
        ->assertSee('Kembali ke Kampus')
        ->assertSee('href="'.route('alumni.rsvp').'"', false)
        ->assertSee('href="'.route('alumni.finance').'"', false)
        ->assertSee('href="'.route('alumni.room').'"', false)
        ->assertSee('href="'.route('documentation.index').'"', false);
});

test('staff without an alumni profile cannot open the private reunion overview', function () {
    $role = Role::factory()->create(['name' => 'bendahara']);
    $bendahara = User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($bendahara)
        ->get(route('reunion.index'))
        ->assertForbidden();
});

test('legacy personal reunion routes remain canonical and protected', function () {
    $alumni = Alumni::factory()->create();

    expect(route('alumni.rsvp', absolute: false))->toBe('/alumni/rsvp')
        ->and(route('alumni.finance', absolute: false))->toBe('/alumni/finance')
        ->and(route('alumni.room', absolute: false))->toBe('/alumni/room');

    $this->actingAs($alumni->user)->get(route('alumni.rsvp'))->assertOk();
    $this->actingAs($alumni->user)->get(route('alumni.finance'))->assertOk();
    $this->actingAs($alumni->user)->get(route('alumni.room'))->assertOk();
});
