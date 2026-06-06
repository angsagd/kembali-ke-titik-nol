<?php

use App\Models\Alumni;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected from alumni management', function () {
    $this->get(route('admin.alumni.index'))
        ->assertRedirect(route('login'));
});

test('alumni users cannot access alumni management', function () {
    $alumniRole = Role::factory()->alumni()->create();
    $user = User::factory()->create(['role_id' => $alumniRole->id]);

    $this->actingAs($user)
        ->get(route('admin.alumni.index'))
        ->assertForbidden();
});

test('administrator users can browse and search alumni', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);

    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);
    $profile = Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'student_number' => 'D096001',
        'rsvp_status' => 'pending',
    ]);

    Alumni::factory()->create([
        'full_name' => 'Budi Santoso',
        'student_number' => 'D096002',
    ]);

    $this->actingAs($administrator)
        ->get(route('admin.alumni.index', ['q' => 'Ade']))
        ->assertOk()
        ->assertSee('Manajemen Alumni')
        ->assertSee('Ade Chandra')
        ->assertSee('D096001')
        ->assertDontSee('Budi Santoso');

    $this->actingAs($administrator)
        ->get(route('admin.alumni.show', $profile))
        ->assertOk()
        ->assertSee('Detail data alumni')
        ->assertSee('Ade Chandra');
});

test('administrator users can update core alumni data', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);

    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);
    $profile = Alumni::factory()->create([
        'full_name' => 'Nama Lama',
        'student_number' => 'D096010',
        'email' => 'lama@example.test',
        'company' => null,
        'job_title' => null,
        'special_notes' => null,
    ]);

    $this->actingAs($administrator);

    Livewire::test('pages::admin.alumni.show', ['alumni' => $profile])
        ->set('full_name', 'Nama Baru')
        ->set('nickname', 'Baru')
        ->set('student_number', 'D096999')
        ->set('email', 'baru@example.test')
        ->set('whatsapp_number', '+62 812-1111-2222')
        ->set('alumni_status', 'active')
        ->set('rsvp_status', 'attending')
        ->set('company', 'PT Titik Nol')
        ->set('job_title', 'Surveyor')
        ->set('special_notes', 'Data dikonfirmasi admin.')
        ->set('is_profile_completed', true)
        ->call('updateAlumni')
        ->assertHasNoErrors();

    $profile->refresh();
    $profile->user->refresh();

    expect($profile->full_name)->toBe('Nama Baru');
    expect($profile->student_number)->toBe('D096999');
    expect($profile->email)->toBe('baru@example.test');
    expect($profile->rsvp_status)->toBe('attending');
    expect($profile->company)->toBe('PT Titik Nol');
    expect($profile->is_profile_completed)->toBeTrue();
    expect($profile->user->name)->toBe('Nama Baru');
    expect($profile->user->whatsapp_number)->toBe('6281211112222');
    expect($profile->user->email)->toBe('baru@example.test');
});

test('administrator alumni update validates unique identifiers', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);

    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);
    $existing = Alumni::factory()->create([
        'student_number' => 'D096123',
    ]);

    $profile = Alumni::factory()->create([
        'student_number' => 'D096124',
    ]);

    $this->actingAs($administrator);

    Livewire::test('pages::admin.alumni.show', ['alumni' => $profile])
        ->set('student_number', 'D096123')
        ->set('whatsapp_number', $existing->user->whatsapp_number)
        ->call('updateAlumni')
        ->assertHasErrors([
            'student_number' => 'unique',
            'whatsapp_number' => 'unique',
        ]);
});
