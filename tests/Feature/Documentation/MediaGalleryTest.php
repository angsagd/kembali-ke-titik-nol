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
    $profile = Alumni::factory()->create();
    MediaItem::factory()->photo()->create([
        'uploaded_by_alumni_id' => $profile->id,
        'title' => 'Foto Reuni',
        'year' => 2026,
    ]);

    $this->actingAs($profile->user)
        ->get(route('documentation.index'))
        ->assertOk()
        ->assertSee('Dokumentasi')
        ->assertSee('Foto Reuni')
        ->assertSee('Simpan Dokumentasi');
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
