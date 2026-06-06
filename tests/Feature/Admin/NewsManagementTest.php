<?php

use App\Models\AuditLog;
use App\Models\News;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected from news management', function () {
    $this->get(route('admin.news.index'))
        ->assertRedirect(route('login'));
});

test('alumni users cannot access news management', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.news.index'))
        ->assertForbidden();
});

test('administrator users can view news management', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);

    News::factory()->published()->create([
        'author_id' => $administrator->id,
        'title' => 'Pengumuman Reuni',
    ]);

    $this->actingAs($administrator)
        ->get(route('admin.news.index'))
        ->assertOk()
        ->assertSee('Manajemen Berita')
        ->assertSee('Pengumuman Reuni')
        ->assertSee('Published');
});

test('administrator users can create news', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);

    $this->actingAs($administrator);

    Livewire::test('pages::admin.news.index')
        ->set('title', 'Pengumuman Reuni Titik Nol')
        ->set('slug', 'pengumuman-reuni-titik-nol')
        ->set('excerpt', 'Persiapan reuni dimulai.')
        ->set('content', 'Konten pengumuman untuk alumni.')
        ->set('form_status', 'published')
        ->call('save')
        ->assertHasNoErrors();

    $news = News::query()->where('slug', 'pengumuman-reuni-titik-nol')->firstOrFail();

    expect($news->author_id)->toBe($administrator->id);
    expect($news->status)->toBe('published');
    expect($news->published_at)->not->toBeNull();
    expect(AuditLog::query()->where('action', 'news.created')->exists())->toBeTrue();
});

test('administrator users can archive news', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);
    $news = News::factory()->published()->create(['title' => 'Pengumuman Reuni']);

    $this->actingAs($administrator);

    Livewire::test('pages::admin.news.index')
        ->call('archive', $news->id)
        ->assertHasNoErrors();

    expect($news->fresh()->status)->toBe('archived');
    expect($news->fresh()->published_at)->toBeNull();
    expect(AuditLog::query()->where('action', 'news.status_changed')->exists())->toBeTrue();
});
