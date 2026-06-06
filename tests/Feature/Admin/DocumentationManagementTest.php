<?php

use App\Models\Alumni;
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
