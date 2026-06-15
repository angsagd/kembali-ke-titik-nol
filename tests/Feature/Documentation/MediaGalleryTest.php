<?php

use App\Models\Alumni;
use App\Models\AuditLog;
use App\Models\MediaItem;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('guests are redirected from documentation page', function () {
    $this->get(route('documentation.index'))
        ->assertRedirect(route('login'));
});

test('users without alumni profile cannot access documentation page', function () {
    $role = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $user = User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($user)
        ->get(route('documentation.index'))
        ->assertForbidden();
});

test('alumni users can view documentation gallery', function () {
    $profile = Alumni::factory()->create(['full_name' => 'Pengunggah Galeri']);
    $tagged = Alumni::factory()->create(['full_name' => 'Alumni Tag Hanya Detail']);
    $mediaItem = MediaItem::factory()->photo()->create([
        'uploaded_by_alumni_id' => $profile->id,
        'title' => 'Foto Reuni',
        'description' => 'Deskripsi hanya ditampilkan pada halaman detail.',
        'year' => 2026,
    ]);
    $mediaItem->taggedAlumni()->attach($tagged->id, ['tagged_by_alumni_id' => $profile->id]);

    $this->actingAs($profile->user)
        ->get(route('documentation.index'))
        ->assertOk()
        ->assertSee('Dokumentasi')
        ->assertSee('Foto Reuni')
        ->assertSee('Pengunggah Galeri')
        ->assertSee('data-documentation-gallery-metadata', false)
        ->assertSee('bottom-2 right-2 z-20', false)
        ->assertDontSee('Deskripsi hanya ditampilkan pada halaman detail.')
        ->assertDontSee('Alumni Tag Hanya Detail')
        ->assertSee('Simpan Dokumentasi');

    $this->get(route('documentation.show', $mediaItem))
        ->assertOk()
        ->assertSee('Deskripsi hanya ditampilkan pada halaman detail.')
        ->assertSee('Alumni Tag Hanya Detail');
});

test('alumni users can filter uploaded and tagged documentation', function () {
    $profile = Alumni::factory()->create(['full_name' => 'Ade Chandra']);
    $other = Alumni::factory()->create(['full_name' => 'Budi Santoso']);

    MediaItem::factory()->photo()->create([
        'uploaded_by_alumni_id' => $profile->id,
        'title' => 'Foto Unggahan Saya',
    ]);

    $taggedMedia = MediaItem::factory()->photo()->create([
        'uploaded_by_alumni_id' => $other->id,
        'title' => 'Foto Tag Saya',
    ]);
    $taggedMedia->taggedAlumni()->attach($profile->id, ['tagged_by_alumni_id' => $other->id]);

    MediaItem::factory()->photo()->create([
        'uploaded_by_alumni_id' => $other->id,
        'title' => 'Foto Orang Lain',
    ]);

    $this->actingAs($profile->user);

    Livewire::test('pages::documentation.index')
        ->set('view', 'uploaded')
        ->assertSee('Foto Unggahan Saya')
        ->assertDontSee('Foto Tag Saya')
        ->set('view', 'tagged')
        ->assertSee('Foto Tag Saya')
        ->assertDontSee('Foto Orang Lain');
});

test('alumni users can search add and remove documentation tags', function () {
    $profile = Alumni::factory()->create(['full_name' => 'Ade Chandra']);
    $tagged = Alumni::factory()->create([
        'full_name' => 'Budi Santoso Unik',
        'nickname' => 'Budi',
    ]);
    Alumni::factory()->create(['full_name' => 'Citra Lestari Unik']);

    $this->actingAs($profile->user);

    Livewire::test('pages::documentation.index')
        ->set('alumni_tag_search', 'Budi Santoso')
        ->assertSee('Budi Santoso Unik')
        ->assertDontSee('Citra Lestari Unik')
        ->call('addTaggedAlumni', $tagged->id)
        ->assertSet('tagged_alumni_ids', [$tagged->id])
        ->assertSet('alumni_tag_search', '')
        ->assertSee('Budi Santoso Unik')
        ->call('removeTaggedAlumni', $tagged->id)
        ->assertSet('tagged_alumni_ids', []);
});

test('alumni users can upload photo documentation', function () {
    Storage::fake('public');

    $profile = Alumni::factory()->create(['full_name' => 'Ade Chandra']);
    $tagged = Alumni::factory()->create(['full_name' => 'Budi Santoso']);
    $photo = UploadedFile::fake()->image('reuni.jpg', 1200, 800)->size(512);

    $this->actingAs($profile->user);

    Livewire::test('pages::documentation.index')
        ->set('type', 'photo')
        ->set('photo', $photo)
        ->set('title', 'Foto Reuni')
        ->set('description', 'Dokumentasi acara reuni.')
        ->set('month', 6)
        ->set('year', 2026)
        ->set('visibility', 'public')
        ->set('tagged_alumni_ids', [$tagged->id])
        ->call('saveMedia')
        ->assertHasNoErrors();

    $mediaItem = MediaItem::query()->where('title', 'Foto Reuni')->firstOrFail();

    expect($mediaItem->type)->toBe('photo');
    expect($mediaItem->uploaded_by_alumni_id)->toBe($profile->id);
    expect($mediaItem->visibility)->toBe('public');
    expect($mediaItem->file_size)->toBeGreaterThan(0);
    expect($mediaItem->width)->toBe(1200);
    expect($mediaItem->height)->toBe(800);
    expect($mediaItem->taggedAlumni()->pluck('alumni.id')->all())->toBe([$tagged->id]);

    $auditLog = AuditLog::query()->where('action', 'media.uploaded')->firstOrFail();
    expect($auditLog->user_id)->toBe($profile->user_id);
    expect($auditLog->entity_type)->toBe($mediaItem->getMorphClass());
    expect($auditLog->entity_id)->toBe($mediaItem->id);

    Storage::disk('public')->assertExists($mediaItem->file_path);
});

test('alumni users can add video documentation url', function () {
    $profile = Alumni::factory()->create();
    $tagged = Alumni::factory()->create(['full_name' => 'Budi Santoso']);

    $this->actingAs($profile->user);

    Livewire::test('pages::documentation.index')
        ->set('type', 'video')
        ->set('video_url', 'https://www.youtube.com/watch?v=abcdefghijk')
        ->set('title', 'Video Reuni')
        ->set('description', 'Video dokumentasi acara.')
        ->set('year', 2026)
        ->set('visibility', 'internal')
        ->set('tagged_alumni_ids', [$tagged->id])
        ->call('saveMedia')
        ->assertHasNoErrors();

    $mediaItem = MediaItem::query()->where('title', 'Video Reuni')->firstOrFail();

    expect($mediaItem->type)->toBe('video');
    expect($mediaItem->file_path)->toBeNull();
    expect($mediaItem->video_url)->toBe('https://www.youtube.com/watch?v=abcdefghijk');
    expect($mediaItem->provider)->toBe('youtube');
    expect($mediaItem->taggedAlumni()->pluck('alumni.id')->all())->toBe([$tagged->id]);
});

test('photo documentation requires a photo file', function () {
    $profile = Alumni::factory()->create();

    $this->actingAs($profile->user);

    Livewire::test('pages::documentation.index')
        ->set('type', 'photo')
        ->set('title', 'Foto Reuni')
        ->set('year', 2026)
        ->call('saveMedia')
        ->assertHasErrors(['photo' => ['required']]);
});

test('alumni users can view and update their documentation detail', function () {
    $profile = Alumni::factory()->create(['full_name' => 'Ade Chandra']);
    $tagged = Alumni::factory()->create(['full_name' => 'Budi Santoso']);
    $mediaItem = MediaItem::factory()->photo()->create([
        'uploaded_by_alumni_id' => $profile->id,
        'title' => 'Foto Lama',
        'visibility' => 'internal',
    ]);

    $this->actingAs($profile->user)
        ->get(route('documentation.show', $mediaItem))
        ->assertOk()
        ->assertSee('Foto Lama')
        ->assertSee('Edit Dokumentasi');

    Livewire::test('pages::documentation.show', ['mediaItem' => $mediaItem])
        ->set('alumni_tag_search', 'Budi')
        ->assertSee('Budi Santoso')
        ->call('addTaggedAlumni', $tagged->id)
        ->assertSet('tagged_alumni_ids', [$tagged->id])
        ->set('title', 'Foto Baru')
        ->set('description', 'Cerita foto diperbarui.')
        ->set('month', 8)
        ->set('year', 2026)
        ->set('visibility', 'public')
        ->call('saveDetails')
        ->assertHasNoErrors();

    $mediaItem->refresh();

    expect($mediaItem->title)->toBe('Foto Baru');
    expect($mediaItem->visibility)->toBe('public');
    expect($mediaItem->taggedAlumni()->pluck('alumni.id')->all())->toBe([$tagged->id]);
    expect(AuditLog::query()->where('action', 'media.updated')->exists())->toBeTrue();
});

test('uploaders can soft delete their documentation', function () {
    $profile = Alumni::factory()->create();
    $mediaItem = MediaItem::factory()->photo()->create([
        'uploaded_by_alumni_id' => $profile->id,
        'title' => 'Foto Arsip',
    ]);

    $this->actingAs($profile->user);

    Livewire::test('pages::documentation.index')
        ->call('deleteMedia', $mediaItem->id)
        ->assertHasNoErrors();

    expect(MediaItem::query()->find($mediaItem->id))->toBeNull();
    expect(MediaItem::withTrashed()->find($mediaItem->id)?->trashed())->toBeTrue();
    expect(AuditLog::query()->where('action', 'media.deleted')->exists())->toBeTrue();
});

test('alumni users cannot soft delete another alumni documentation', function () {
    $profile = Alumni::factory()->create();
    $other = Alumni::factory()->create();
    $mediaItem = MediaItem::factory()->photo()->create([
        'uploaded_by_alumni_id' => $other->id,
        'title' => 'Foto Orang Lain',
    ]);

    $this->actingAs($profile->user);

    Livewire::test('pages::documentation.index')
        ->call('deleteMedia', $mediaItem->id)
        ->assertForbidden();

    expect($mediaItem->fresh())->not->toBeNull();
});
