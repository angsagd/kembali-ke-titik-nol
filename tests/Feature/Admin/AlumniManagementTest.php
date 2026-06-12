<?php

use App\Models\Alumni;
use App\Models\AlumniTimeline;
use App\Models\AuditLog;
use App\Models\City;
use App\Models\Country;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
        ->assertDontSee("sort('student_number')", false)
        ->assertDontSee("sort('alumni_status')", false)
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

test('superadmin users can see role column on alumni management table', function () {
    $superadminRole = Role::factory()->create([
        'name' => 'superadmin',
        'description' => 'Pengelola teknis sistem',
    ]);
    $bendaharaRole = Role::factory()->create([
        'name' => 'bendahara',
        'description' => 'Pengelola pembayaran dan donasi',
    ]);

    $superadmin = User::factory()->create(['role_id' => $superadminRole->id]);
    $bendaharaUser = User::factory()->create([
        'role_id' => $bendaharaRole->id,
        'name' => 'Bendahara Alumni',
    ]);
    Alumni::factory()->create([
        'user_id' => $bendaharaUser->id,
        'full_name' => 'Bendahara Alumni',
    ]);

    $this->actingAs($superadmin)
        ->get(route('admin.alumni.index'))
        ->assertOk()
        ->assertSee('Role')
        ->assertSee('bendahara');
});

test('administrator users can sort alumni management table by clicking sortable columns', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);

    Alumni::factory()->create([
        'full_name' => 'Aaa Alumni Sort',
        'student_number' => 'D096001',
    ]);
    Alumni::factory()->create([
        'full_name' => 'Zzz Alumni Sort',
        'student_number' => 'D096999',
    ]);

    $response = $this->actingAs($administrator)
        ->get(route('admin.alumni.index', [
            'sort' => 'full_name',
            'direction' => 'desc',
        ]))
        ->assertOk()
        ->assertSee('Nama')
        ->assertSee('Zzz Alumni Sort')
        ->assertSee('Aaa Alumni Sort');

    $content = $response->getContent();

    expect(strpos($content, 'Zzz Alumni Sort'))->toBeLessThan(strpos($content, 'Aaa Alumni Sort'));
});

test('superadmin users can sort alumni management table by role column', function () {
    $superadminRole = Role::factory()->create([
        'name' => 'superadmin',
        'description' => 'Pengelola teknis sistem',
    ]);
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $bendaharaRole = Role::factory()->create([
        'name' => 'bendahara',
        'description' => 'Pengelola pembayaran dan donasi',
    ]);

    $superadmin = User::factory()->create(['role_id' => $superadminRole->id]);
    $administratorUser = User::factory()->create([
        'role_id' => $administratorRole->id,
        'name' => 'Admin Role Alumni',
    ]);
    $bendaharaUser = User::factory()->create([
        'role_id' => $bendaharaRole->id,
        'name' => 'Bendahara Role Alumni',
    ]);

    Alumni::factory()->create([
        'user_id' => $administratorUser->id,
        'full_name' => 'Admin Role Alumni',
    ]);
    Alumni::factory()->create([
        'user_id' => $bendaharaUser->id,
        'full_name' => 'Bendahara Role Alumni',
    ]);

    $response = $this->actingAs($superadmin)
        ->get(route('admin.alumni.index', [
            'sort' => 'role',
            'direction' => 'asc',
        ]))
        ->assertOk()
        ->assertSee('Role')
        ->assertSee('Admin Role Alumni')
        ->assertSee('Bendahara Role Alumni');

    $content = $response->getContent();

    expect(strpos($content, 'Admin Role Alumni'))->toBeLessThan(strpos($content, 'Bendahara Role Alumni'));
});

test('administrator users do not see role column on alumni management table', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $bendaharaRole = Role::factory()->create([
        'name' => 'bendahara',
        'description' => 'Pengelola pembayaran dan donasi',
    ]);

    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);
    $bendaharaUser = User::factory()->create([
        'role_id' => $bendaharaRole->id,
        'name' => 'Bendahara Alumni',
    ]);
    Alumni::factory()->create([
        'user_id' => $bendaharaUser->id,
        'full_name' => 'Bendahara Alumni',
    ]);

    $this->actingAs($administrator)
        ->get(route('admin.alumni.index'))
        ->assertOk()
        ->assertDontSee('Role')
        ->assertDontSee('bendahara');
});

test('administrator users can create alumni from alumni management', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $alumniRole = Role::factory()->alumni()->create();
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);

    $this->actingAs($administrator)
        ->get(route('admin.alumni.index'))
        ->assertOk()
        ->assertSee('Tambah Alumni');

    Livewire::actingAs($administrator)
        ->test('pages::admin.alumni.index')
        ->call('showCreateForm')
        ->assertSet('show_create_form', true)
        ->set('full_name', 'Alumni Baru')
        ->set('whatsapp_number', '+62 812-2222-3333')
        ->set('student_number', 'D096777')
        ->set('email', 'alumni-baru@example.test')
        ->set('alumni_status', 'active')
        ->call('createAlumni')
        ->assertHasNoErrors();

    $user = User::query()
        ->where('whatsapp_number', '6281222223333')
        ->firstOrFail();
    $profile = Alumni::query()
        ->where('user_id', $user->id)
        ->firstOrFail();

    expect($user->name)->toBe('Alumni Baru');
    expect($user->email)->toBe('alumni-baru@example.test');
    expect($user->role_id)->toBe($alumniRole->id);
    expect(Hash::check('tgd3333', $user->password))->toBeTrue();
    expect($profile->full_name)->toBe('Alumni Baru');
    expect($profile->student_number)->toBe('D096777');
    expect($profile->rsvp_status)->toBe('pending');
    expect($profile->is_profile_completed)->toBeFalse();
    expect(AuditLog::query()
        ->where('action', 'alumni.created')
        ->where('entity_type', $profile->getMorphClass())
        ->where('entity_id', $profile->id)
        ->exists())->toBeTrue();
});

test('administrator alumni creation validates unique identifiers', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);
    $existing = Alumni::factory()->create([
        'student_number' => 'D096123',
    ]);

    $this->actingAs($administrator);

    Livewire::test('pages::admin.alumni.index')
        ->call('showCreateForm')
        ->set('full_name', 'Duplikat Alumni')
        ->set('whatsapp_number', $existing->user->whatsapp_number)
        ->set('student_number', 'D096123')
        ->set('email', $existing->user->email)
        ->call('createAlumni')
        ->assertHasErrors([
            'whatsapp_number' => 'unique',
            'student_number' => 'unique',
            'email' => 'unique',
        ]);
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

test('administrator users can upload alumni memory book photos', function () {
    Storage::fake('public');

    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);
    $profile = Alumni::factory()->create();

    Livewire::actingAs($administrator)
        ->test('pages::admin.alumni.show', ['alumni' => $profile])
        ->set('college_photo', UploadedFile::fake()->image('kuliah.jpg', 800, 1000))
        ->set('current_photo', UploadedFile::fake()->image('sekarang.jpg', 800, 1000))
        ->call('updateMemoryBookPhotos')
        ->assertHasNoErrors();

    $profile->refresh();

    Storage::disk('public')->assertExists($profile->college_photo_path);
    Storage::disk('public')->assertExists($profile->current_photo_path);
    expect(AuditLog::query()
        ->where('action', 'alumni.memory_book_photos_updated')
        ->where('entity_type', $profile->getMorphClass())
        ->where('entity_id', $profile->id)
        ->exists())->toBeTrue();
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

test('superadmin users can reset linked user password from alumni detail', function () {
    $superadminRole = Role::factory()->create([
        'name' => 'superadmin',
        'description' => 'Pengelola teknis sistem',
    ]);

    $superadmin = User::factory()->create(['role_id' => $superadminRole->id]);
    $profile = Alumni::factory()->create();

    $this->actingAs($superadmin)
        ->get(route('admin.alumni.show', $profile))
        ->assertOk()
        ->assertSee('Reset Password')
        ->assertSee('Simpan Password');

    Livewire::actingAs($superadmin)
        ->test('pages::admin.alumni.show', ['alumni' => $profile])
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword')
        ->assertHasNoErrors()
        ->assertSet('password', '')
        ->assertSet('password_confirmation', '');

    $profile->user->refresh();
    $auditLog = AuditLog::query()
        ->where('action', 'user.password_updated')
        ->where('entity_type', $profile->user->getMorphClass())
        ->where('entity_id', $profile->user->id)
        ->firstOrFail();

    expect(Hash::check('new-password', $profile->user->password))->toBeTrue();
    expect($auditLog->new_values)->toBe(['password_updated' => true]);
});

test('administrator users cannot reset linked user passwords', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);

    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);
    $profile = Alumni::factory()->create();
    $originalPassword = $profile->user->password;

    $this->actingAs($administrator)
        ->get(route('admin.alumni.show', $profile))
        ->assertOk()
        ->assertDontSee('Reset Password')
        ->assertDontSee('Simpan Password');

    Livewire::actingAs($administrator)
        ->test('pages::admin.alumni.show', ['alumni' => $profile])
        ->set('password', 'new-password')
        ->set('password_confirmation', 'new-password')
        ->call('updatePassword')
        ->assertForbidden();

    expect($profile->user->refresh()->password)->toBe($originalPassword);
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
