<?php

use App\Models\Alumni;
use App\Models\Role;
use App\Models\User;

test('guests are redirected from alumni directory', function () {
    $this->get(route('alumni.directory.index'))
        ->assertRedirect(route('login'));
});

test('users without alumni profile cannot access alumni directory', function () {
    $role = Role::factory()->create([
        'name' => 'bendahara',
        'description' => 'Bendahara reuni',
    ]);

    $user = User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($user)
        ->get(route('alumni.directory.index'))
        ->assertForbidden();
});

test('alumni users can browse and search private directory', function () {
    $viewer = Alumni::factory()->create();

    Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'nickname' => 'Ade',
        'company' => 'PT Titik Nol',
        'job_title' => 'Surveyor',
    ]);

    Alumni::factory()->create([
        'full_name' => 'Budi Santoso',
        'company' => 'Geodata Nusantara',
    ]);

    $this->actingAs($viewer->user)
        ->get(route('alumni.directory.index', ['q' => 'Titik Nol']))
        ->assertOk()
        ->assertSee('Direktori Alumni')
        ->assertSee('Ade Chandra')
        ->assertSee('PT Titik Nol')
        ->assertDontSee('Budi Santoso');
});

test('alumni users can read another alumni profile', function () {
    $viewer = Alumni::factory()->create();
    $profile = Alumni::factory()->create([
        'full_name' => 'Citra Lestari',
        'nickname' => 'Citra',
        'student_number' => 'D096333',
        'company' => 'Pemetaan Mandiri',
        'short_story' => 'Sekarang mengelola proyek pemetaan.',
        'memorable_story' => 'Kenangan praktikum lapangan.',
        'message_to_friends' => 'Sampai jumpa di reuni.',
    ]);

    $this->actingAs($viewer->user)
        ->get(route('alumni.directory.show', $profile))
        ->assertOk()
        ->assertSee('Citra Lestari')
        ->assertSee('D096333')
        ->assertSee('Pemetaan Mandiri')
        ->assertSee('Sekarang mengelola proyek pemetaan.')
        ->assertSee('Sampai jumpa di reuni.');
});

test('administrator users can access private alumni directory', function () {
    $role = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);

    $administrator = User::factory()->create(['role_id' => $role->id]);

    Alumni::factory()->create([
        'full_name' => 'Dewi Kartika',
    ]);

    $this->actingAs($administrator)
        ->get(route('alumni.directory.index'))
        ->assertOk()
        ->assertSee('Dewi Kartika');
});
