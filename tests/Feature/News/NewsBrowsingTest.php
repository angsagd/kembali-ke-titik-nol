<?php

use App\Models\News;
use App\Models\User;

test('guests can browse published news', function () {
    News::factory()->published()->create([
        'title' => 'Pengumuman Reuni',
        'excerpt' => 'Informasi resmi reuni.',
    ]);
    News::factory()->draft()->create(['title' => 'Draft Panitia']);

    $this->get(route('news.index'))
        ->assertOk()
        ->assertSee('Berita dan Pengumuman')
        ->assertSee('Tentang')
        ->assertSee('Rundown')
        ->assertSee('Galeri')
        ->assertDontSee('>Publik<', false)
        ->assertSee('Donatur')
        ->assertSee('fixed inset-x-0 top-0', false)
        ->assertSee('Pengumuman Reuni')
        ->assertDontSee('Draft Panitia');
});

test('guests can read published news detail', function () {
    $news = News::factory()->published()->create([
        'title' => 'Pengumuman Reuni',
        'slug' => 'pengumuman-reuni',
        'content' => "## Agenda Reuni\n\n**Konten pengumuman resmi.**\n\n<script>alert('xss')</script>\n\n[Tautan berbahaya](javascript:alert('xss'))",
    ]);

    $this->get(route('news.show', $news->slug))
        ->assertOk()
        ->assertSee('Tentang')
        ->assertSee('Rundown')
        ->assertSee('Galeri')
        ->assertDontSee('>Publik<', false)
        ->assertSee('Donatur')
        ->assertSee('fixed inset-x-0 top-0', false)
        ->assertSee('Pengumuman Reuni')
        ->assertSee('<h2>Agenda Reuni</h2>', false)
        ->assertSee('<strong>Konten pengumuman resmi.</strong>', false)
        ->assertDontSee("alert('xss')")
        ->assertDontSee('href="javascript:', false);
});

test('draft news detail is not visible to guests', function () {
    $news = News::factory()->draft()->create(['slug' => 'draft-panitia']);

    $this->get(route('news.show', $news->slug))
        ->assertNotFound();
});

test('authenticated users can still browse public news', function () {
    $user = User::factory()->create();

    News::factory()->published()->create([
        'title' => 'Info Registrasi',
        'excerpt' => 'Registrasi reuni sudah dibuka.',
    ]);

    $this->actingAs($user)
        ->get(route('news.index'))
        ->assertOk()
        ->assertSee('Info Registrasi');
});
