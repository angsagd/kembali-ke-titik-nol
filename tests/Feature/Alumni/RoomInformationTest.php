<?php

use App\Models\Alumni;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\User;

test('guests are redirected from room information page', function () {
    $this->get(route('alumni.room'))
        ->assertRedirect(route('login'));
});

test('users without alumni profile cannot access room information page', function () {
    $role = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $user = User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($user)
        ->get(route('alumni.room'))
        ->assertForbidden();
});

test('alumni users see empty room information when unassigned', function () {
    $profile = Alumni::factory()->create();

    $this->actingAs($profile->user)
        ->get(route('alumni.room'))
        ->assertOk()
        ->assertSee('Kamar Saya')
        ->assertSee('Belum ada data kamar');
});

test('alumni users can view assigned room and roommates', function () {
    $profile = Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'student_number' => 'D096001',
    ]);
    $roommate = Alumni::factory()->create([
        'full_name' => 'Budi Santoso',
        'student_number' => 'D096002',
    ]);
    $room = Room::factory()->create([
        'room_name' => 'Kamar 01',
        'room_type' => 'Twin Share',
        'capacity' => 2,
        'location_notes' => 'Lantai 2',
        'notes' => 'Dekat lift.',
    ]);

    RoomAssignment::factory()->create([
        'room_id' => $room->id,
        'alumni_id' => $profile->id,
        'notes' => 'Check-in bersama panitia.',
    ]);
    RoomAssignment::factory()->create([
        'room_id' => $room->id,
        'alumni_id' => $roommate->id,
    ]);

    $this->actingAs($profile->user)
        ->get(route('alumni.room'))
        ->assertOk()
        ->assertSee('Kamar 01')
        ->assertSee('Twin Share')
        ->assertSee('Lantai 2')
        ->assertSee('Dekat lift.')
        ->assertSee('Check-in bersama panitia.')
        ->assertSee('Ade Chandra')
        ->assertSee('Budi Santoso');
});
