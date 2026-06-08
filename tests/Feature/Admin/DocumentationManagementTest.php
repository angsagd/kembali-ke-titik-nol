<?php

use App\Models\Alumni;
use App\Models\AuditLog;
use App\Models\MediaItem;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected from documentation management', function () {
    $this->get(route('admin.documentation.index'))
        ->assertRedirect(route('login'));
});

test('alumni users cannot access documentation management', function () {
    $profile = Alumni::factory()->create();

    $this->actingAs($profile->user)
        ->get(route('admin.documentation.index'))
        ->assertForbidden();
});

test('administrator users can view documentation management', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);
    $uploader = Alumni::factory()->create(['full_name' => 'Ade Chandra']);

    MediaItem::factory()->photo()->create([
        'uploaded_by_alumni_id' => $uploader->id,
        'title' => 'Foto Reuni',
        'visibility' => 'public',
    ]);
    MediaItem::factory()->video()->create([
        'uploaded_by_alumni_id' => $uploader->id,
        'title' => 'Video Reuni',
        'visibility' => 'internal',
    ]);

    $this->actingAs($administrator)
        ->get(route('admin.documentation.index'))
        ->assertOk()
        ->assertSee('Manajemen Dokumentasi')
        ->assertSee('Total Foto')
        ->assertSee('Total Video')
        ->assertSee('Metadata')
        ->assertSee('YouTube')
        ->assertSee('Foto Reuni')
        ->assertSee('Video Reuni')
        ->assertSee('Ade Chandra');
});

test('administrator users can filter documentation by type', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);

    MediaItem::factory()->photo()->create(['title' => 'Foto Reuni']);
    MediaItem::factory()->video()->create(['title' => 'Video Reuni']);

    $this->actingAs($administrator);

    Livewire::test('pages::admin.documentation.index')
        ->set('type', 'photo')
        ->assertSee('Foto Reuni')
        ->assertDontSee('Video Reuni');
});

test('administrator users can search documentation by uploader', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);
    $ade = Alumni::factory()->create(['full_name' => 'Ade Chandra']);
    $budi = Alumni::factory()->create(['full_name' => 'Budi Santoso']);

    MediaItem::factory()->photo()->create([
        'uploaded_by_alumni_id' => $ade->id,
        'title' => 'Foto Ade',
    ]);
    MediaItem::factory()->photo()->create([
        'uploaded_by_alumni_id' => $budi->id,
        'title' => 'Foto Budi',
    ]);

    $this->actingAs($administrator);

    Livewire::test('pages::admin.documentation.index')
        ->set('search', 'Ade')
        ->assertSee('Foto Ade')
        ->assertDontSee('Foto Budi');
});

test('administrator users can view archived documentation', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);

    $activeMedia = MediaItem::factory()->photo()->create(['title' => 'Foto Aktif']);
    $archivedMedia = MediaItem::factory()->photo()->create(['title' => 'Foto Arsip']);
    $archivedMedia->delete();

    $this->actingAs($administrator);

    Livewire::test('pages::admin.documentation.index')
        ->assertSee('Foto Aktif')
        ->assertDontSee('Foto Arsip')
        ->set('status', 'archived')
        ->assertSee('Foto Arsip')
        ->assertDontSee('Foto Aktif')
        ->assertSee('Diarsipkan');
});

test('administrator users can restore archived documentation', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);
    $mediaItem = MediaItem::factory()->photo()->create(['title' => 'Foto Arsip']);
    $mediaItem->delete();

    $this->actingAs($administrator);

    Livewire::test('pages::admin.documentation.index')
        ->set('status', 'archived')
        ->call('restoreMedia', $mediaItem->id)
        ->assertHasNoErrors();

    expect(MediaItem::query()->find($mediaItem->id))->not->toBeNull();
    expect(AuditLog::query()->where('action', 'media.restored')->exists())->toBeTrue();
});

test('administrator users can update documentation visibility', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);
    $mediaItem = MediaItem::factory()->photo()->create([
        'title' => 'Foto Internal',
        'visibility' => 'internal',
    ]);

    $this->actingAs($administrator);

    Livewire::test('pages::admin.documentation.index')
        ->call('setVisibility', $mediaItem->id, 'public')
        ->assertHasNoErrors();

    expect($mediaItem->fresh()->visibility)->toBe('public');
    expect(AuditLog::query()->where('action', 'media.visibility_updated')->exists())->toBeTrue();
});
