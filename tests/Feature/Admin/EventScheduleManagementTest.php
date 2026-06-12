<?php

use App\Models\AuditLog;
use App\Models\EventScheduleItem;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\EventScheduleItemSeeder;
use Livewire\Livewire;

test('guests are redirected from event schedule management', function () {
    $this->get(route('admin.event-schedule.index'))
        ->assertRedirect(route('login'));
});

test('alumni users cannot access event schedule management', function () {
    $alumniRole = Role::factory()->alumni()->create();
    $alumni = User::factory()->create(['role_id' => $alumniRole->id]);

    $this->actingAs($alumni)
        ->get(route('admin.event-schedule.index'))
        ->assertForbidden();
});

test('administrator users can view fixed event dates and locations', function () {
    $administratorRole = Role::factory()->create(['name' => 'administrator']);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);

    $this->actingAs($administrator)
        ->get(route('admin.event-schedule.index'))
        ->assertOk()
        ->assertSee('Rangkaian Acara')
        ->assertSee('Minggu, 23 Agustus 2026')
        ->assertSee('Penginapan Joglo / Kampung Wisata Tembi')
        ->assertSee('Senin, 24 Agustus 2026')
        ->assertSee('Departemen Teknik Geodesi UGM');
});

test('administrator users can create update and delete event schedule items', function () {
    $administratorRole = Role::factory()->create(['name' => 'administrator']);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);

    Livewire::actingAs($administrator)
        ->test('pages::admin.event-schedule.index')
        ->set('event_day', 'day_one')
        ->set('start_time', '16:30')
        ->set('activity', 'Pembukaan Reuni')
        ->call('save')
        ->assertHasNoErrors();

    $scheduleItem = EventScheduleItem::query()->firstOrFail();

    expect($scheduleItem->event_day)->toBe('day_one');
    expect(substr($scheduleItem->start_time, 0, 5))->toBe('16:30');
    expect($scheduleItem->activity)->toBe('Pembukaan Reuni');
    expect(AuditLog::query()->where('action', 'event_schedule.created')->exists())->toBeTrue();

    Livewire::actingAs($administrator)
        ->test('pages::admin.event-schedule.index')
        ->call('edit', $scheduleItem->id)
        ->set('event_day', 'day_two')
        ->set('start_time', '10:15')
        ->set('activity', 'Campus Tour')
        ->call('save')
        ->assertHasNoErrors();

    $scheduleItem->refresh();

    expect($scheduleItem->event_day)->toBe('day_two');
    expect(substr($scheduleItem->start_time, 0, 5))->toBe('10:15');
    expect($scheduleItem->activity)->toBe('Campus Tour');
    expect(AuditLog::query()->where('action', 'event_schedule.updated')->exists())->toBeTrue();

    Livewire::actingAs($administrator)
        ->test('pages::admin.event-schedule.index')
        ->call('delete', $scheduleItem->id)
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('event_schedule_items', ['id' => $scheduleItem->id]);
    expect(AuditLog::query()->where('action', 'event_schedule.deleted')->exists())->toBeTrue();
});

test('event schedule form validates day time and activity', function () {
    $administratorRole = Role::factory()->create(['name' => 'administrator']);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);

    Livewire::actingAs($administrator)
        ->test('pages::admin.event-schedule.index')
        ->set('event_day', 'invalid')
        ->set('start_time', '25:00')
        ->set('activity', '')
        ->call('save')
        ->assertHasErrors(['event_day', 'start_time', 'activity']);
});

test('event schedule seeder inserts the existing landing rundown once', function () {
    $this->seed(EventScheduleItemSeeder::class);
    $this->seed(EventScheduleItemSeeder::class);

    $this->assertDatabaseCount('event_schedule_items', 6);
    $this->assertDatabaseHas('event_schedule_items', [
        'event_day' => 'day_one',
        'activity' => 'Check-in & Registrasi',
    ]);
    $this->assertDatabaseHas('event_schedule_items', [
        'event_day' => 'day_two',
        'activity' => 'Gala Dinner',
    ]);
});

test('landing page displays dynamic event schedule ordered by time', function () {
    EventScheduleItem::factory()->create([
        'event_day' => 'day_one',
        'start_time' => '20:00',
        'activity' => 'Kegiatan Malam',
    ]);
    EventScheduleItem::factory()->create([
        'event_day' => 'day_one',
        'start_time' => '08:00',
        'activity' => 'Kegiatan Pagi',
    ]);
    EventScheduleItem::factory()->create([
        'event_day' => 'day_two',
        'start_time' => '13:00',
        'activity' => 'Kegiatan Hari Kedua',
    ]);

    $response = $this->get(route('home'))
        ->assertOk()
        ->assertSee('Minggu, 23 Agustus')
        ->assertSee('Penginapan Joglo / Kampung Wisata Tembi')
        ->assertSee('08.00')
        ->assertSee('Kegiatan Pagi')
        ->assertSee('20.00')
        ->assertSee('Kegiatan Malam')
        ->assertSee('Kegiatan Hari Kedua');

    expect(strpos($response->getContent(), 'Kegiatan Pagi'))
        ->toBeLessThan(strpos($response->getContent(), 'Kegiatan Malam'));
});
