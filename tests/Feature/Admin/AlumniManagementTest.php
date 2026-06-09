<?php

use App\Models\Alumni;
use App\Models\AlumniTimeline;
use App\Models\AuditLog;
use App\Models\City;
use App\Models\Country;
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
    $country = Country::factory()->create(['name' => 'Indonesia', 'code' => 'ID']);
    $city = City::factory()->create(['country_id' => $country->id, 'name' => 'Yogyakarta']);

    AlumniTimeline::factory()->create([
        'alumni_id' => $profile->id,
        'year' => 1996,
        'month' => 8,
        'country_id' => $country->id,
        'city_id' => $city->id,
        'notes' => 'Mulai kuliah.',
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
        ->assertSee('Ade Chandra')
        ->assertSee('Timeline Lokasi')
        ->assertSee('Agustus 1996')
        ->assertSee('Yogyakarta')
        ->assertSee('Mulai kuliah.');
});

test('administrator users can update core alumni data', function () {
    $country = Country::factory()->create(['name' => 'Indonesia', 'code' => 'ID']);
    $city = City::factory()->create(['country_id' => $country->id, 'name' => 'Yogyakarta']);
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
        ->set('current_country_id', $country->id)
        ->set('current_city_id', $city->id)
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
    expect($profile->current_country_id)->toBe($country->id);
    expect($profile->current_city_id)->toBe($city->id);
    expect($profile->is_profile_completed)->toBeTrue();
    expect($profile->user->name)->toBe('Nama Baru');
    expect($profile->user->whatsapp_number)->toBe('6281211112222');
    expect($profile->user->email)->toBe('baru@example.test');
});

test('superadmin users can update linked user role from alumni detail', function () {
    $superadminRole = Role::factory()->create([
        'name' => 'superadmin',
        'description' => 'Pengelola teknis sistem',
    ]);
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Panitia pelaksana reuni',
    ]);

    $superadmin = User::factory()->create(['role_id' => $superadminRole->id]);
    $profile = Alumni::factory()->create([
        'full_name' => 'Alumni Calon Admin',
    ]);

    $this->actingAs($superadmin)
        ->get(route('admin.alumni.show', $profile))
        ->assertOk()
        ->assertSee('Ubah role akun')
        ->assertSee('Simpan Role');

    Livewire::actingAs($superadmin)
        ->test('pages::admin.alumni.show', ['alumni' => $profile])
        ->set('role_id', $administratorRole->id)
        ->call('updateRole')
        ->assertHasNoErrors();

    $profile->user->refresh()->load('role');

    expect($profile->user->role?->name)->toBe('administrator');
    expect(AuditLog::query()
        ->where('action', 'user.role_updated')
        ->where('entity_type', $profile->user->getMorphClass())
        ->where('entity_id', $profile->user->id)
        ->exists())->toBeTrue();
});

test('administrator users cannot update linked user roles', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $bendaharaRole = Role::factory()->create([
        'name' => 'bendahara',
        'description' => 'Pengelola pembayaran dan donasi',
    ]);

    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);
    $profile = Alumni::factory()->create();

    $this->actingAs($administrator)
        ->get(route('admin.alumni.show', $profile))
        ->assertOk()
        ->assertDontSee('Ubah role akun')
        ->assertDontSee('Simpan Role');

    Livewire::actingAs($administrator)
        ->test('pages::admin.alumni.show', ['alumni' => $profile])
        ->set('role_id', $bendaharaRole->id)
        ->call('updateRole')
        ->assertForbidden();

    expect($profile->user->refresh()->role_id)->not->toBe($bendaharaRole->id);
});

test('superadmin users cannot remove the last superadmin role', function () {
    $superadminRole = Role::factory()->create([
        'name' => 'superadmin',
        'description' => 'Pengelola teknis sistem',
    ]);
    $alumniRole = Role::factory()->create([
        'name' => 'alumni',
        'description' => 'Anggota alumni',
    ]);

    $superadmin = User::factory()->create(['role_id' => $superadminRole->id]);
    $profile = Alumni::factory()->create([
        'user_id' => $superadmin->id,
    ]);

    Livewire::actingAs($superadmin)
        ->test('pages::admin.alumni.show', ['alumni' => $profile])
        ->set('role_id', $alumniRole->id)
        ->call('updateRole')
        ->assertHasErrors(['role_id']);

    expect($superadmin->refresh()->role_id)->toBe($superadminRole->id);
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

test('administrator alumni update rejects city outside selected country', function () {
    $indonesia = Country::factory()->create(['name' => 'Indonesia', 'code' => 'ID']);
    $malaysia = Country::factory()->create(['name' => 'Malaysia', 'code' => 'MY']);
    $kualaLumpur = City::factory()->create(['country_id' => $malaysia->id, 'name' => 'Kuala Lumpur']);
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);

    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);
    $profile = Alumni::factory()->create([
        'current_country_id' => null,
        'current_city_id' => null,
    ]);

    $this->actingAs($administrator);

    Livewire::test('pages::admin.alumni.show', ['alumni' => $profile])
        ->set('current_country_id', $indonesia->id)
        ->set('current_city_id', $kualaLumpur->id)
        ->call('updateAlumni')
        ->assertHasErrors([
            'current_city_id' => 'exists',
        ]);

    $profile->refresh();

    expect($profile->current_country_id)->toBeNull();
    expect($profile->current_city_id)->toBeNull();
});
