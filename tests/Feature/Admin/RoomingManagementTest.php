<?php

use App\Models\Alumni;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected from rooming management', function () {
    $this->get(route('admin.rooming.index'))
        ->assertRedirect(route('login'));
});

test('alumni users cannot access rooming management', function () {
    $profile = Alumni::factory()->create();

    $this->actingAs($profile->user)
        ->get(route('admin.rooming.index'))
        ->assertForbidden();
});

test('administrator users can view rooming management', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);

    Room::factory()->create([
        'room_name' => 'Kamar 01',
        'room_type' => 'Twin Share',
        'capacity' => 2,
    ]);

    $this->actingAs($administrator)
        ->get(route('admin.rooming.index'))
        ->assertOk()
        ->assertSee('Rooming')
        ->assertSee('Kamar 01')
        ->assertSee('Twin Share');
});

test('administrator users can create and update rooms', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);

    $this->actingAs($administrator);

    Livewire::test('pages::admin.rooming.index')
        ->set('room_name', 'Kamar 01')
        ->set('room_type', 'Twin Share')
        ->set('capacity', 2)
        ->set('location_notes', 'Lantai 2')
        ->set('notes', 'Dekat lift.')
        ->call('saveRoom')
        ->assertHasNoErrors();

    $room = Room::query()->where('room_name', 'Kamar 01')->firstOrFail();

    expect($room->room_type)->toBe('Twin Share');
    expect($room->capacity)->toBe(2);

    Livewire::test('pages::admin.rooming.index')
        ->call('selectRoom', $room->id)
        ->set('room_name', 'Kamar 01A')
        ->set('capacity', 3)
        ->call('saveRoom')
        ->assertHasNoErrors();

    expect($room->refresh()->room_name)->toBe('Kamar 01A');
    expect($room->capacity)->toBe(3);
});

test('administrator users can assign and remove alumni from rooms', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);
    $room = Room::factory()->create(['capacity' => 2]);
    $profile = Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'alumni_status' => 'active',
    ]);

    $this->actingAs($administrator);

    Livewire::test('pages::admin.rooming.index')
        ->call('selectRoom', $room->id)
        ->set('assignment_alumni_id', $profile->id)
        ->set('assignment_notes', 'Teman sekamar disepakati panitia.')
        ->call('assignAlumni')
        ->assertHasNoErrors();

    $assignment = RoomAssignment::query()->where('alumni_id', $profile->id)->firstOrFail();

    expect($assignment->room_id)->toBe($room->id);
    expect($assignment->assigned_by)->toBe($administrator->id);

    Livewire::test('pages::admin.rooming.index')
        ->call('selectRoom', $room->id)
        ->call('removeAssignment', $assignment->id)
        ->assertHasNoErrors();

    expect(RoomAssignment::query()->whereKey($assignment->id)->exists())->toBeFalse();
});

test('room assignment cannot exceed capacity', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);
    $room = Room::factory()->create(['capacity' => 1]);
    $firstProfile = Alumni::factory()->create(['alumni_status' => 'active']);
    $secondProfile = Alumni::factory()->create(['alumni_status' => 'active']);

    RoomAssignment::factory()->create([
        'room_id' => $room->id,
        'alumni_id' => $firstProfile->id,
        'assigned_by' => $administrator->id,
    ]);

    $this->actingAs($administrator);

    Livewire::test('pages::admin.rooming.index')
        ->call('selectRoom', $room->id)
        ->set('assignment_alumni_id', $secondProfile->id)
        ->call('assignAlumni')
        ->assertHasErrors(['assignment_alumni_id']);

    expect(RoomAssignment::query()->where('alumni_id', $secondProfile->id)->exists())->toBeFalse();
});

test('room capacity cannot be lower than current occupants', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);
    $room = Room::factory()->create(['capacity' => 2]);

    RoomAssignment::factory()->create([
        'room_id' => $room->id,
        'alumni_id' => Alumni::factory()->create()->id,
    ]);
    RoomAssignment::factory()->create([
        'room_id' => $room->id,
        'alumni_id' => Alumni::factory()->create()->id,
    ]);

    $this->actingAs($administrator);

    Livewire::test('pages::admin.rooming.index')
        ->call('selectRoom', $room->id)
        ->set('capacity', 1)
        ->call('saveRoom')
        ->assertHasErrors(['capacity']);

    expect($room->refresh()->capacity)->toBe(2);
});
