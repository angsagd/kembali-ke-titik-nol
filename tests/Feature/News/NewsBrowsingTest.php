<?php

use App\Models\News;
use App\Models\User;

test('guests are redirected from news index', function () {
    $this->get(route('news.index'))
        ->assertRedirect(route('login'));
});

test('authenticated users can browse published news', function () {
    $user = User::factory()->create();

    News::factory()->published()->create([
        'title' => 'Pengumuman Reuni',
        'excerpt' => 'Informasi resmi reuni.',
    ]);
    News::factory()->draft()->create(['title' => 'Draft Panitia']);

    $this->actingAs($user)
        ->get(route('news.index'))
        ->assertOk()
        ->assertSee('Berita dan Pengumuman')
        ->assertSee('Pengumuman Reuni')
        ->assertDontSee('Draft Panitia');
});

test('authenticated users can read published news detail', function () {
    $user = User::factory()->create();
    $news = News::factory()->published()->create([
        'title' => 'Pengumuman Reuni',
        'slug' => 'pengumuman-reuni',
        'content' => 'Konten pengumuman resmi.',
    ]);

    $this->actingAs($user)
        ->get(route('news.show', $news))
        ->assertOk()
        ->assertSee('Pengumuman Reuni')
        ->assertSee('Konten pengumuman resmi.');
});

test('draft news detail is not visible to regular authenticated users', function () {
    $user = User::factory()->create();
    $news = News::factory()->draft()->create(['slug' => 'draft-panitia']);

    $this->actingAs($user)
        ->get(route('news.show', $news))
        ->assertNotFound();
});
