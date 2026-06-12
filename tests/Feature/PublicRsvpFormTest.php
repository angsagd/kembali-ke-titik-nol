<?php

use App\Models\Alumni;
use App\Models\ApplicationSetting;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

test('guests can view public rsvp form when it is open', function () {
    $this->get(route('public.rsvp'))
        ->assertOk()
        ->assertSee('Konfirmasi Data Alumni')
        ->assertSee('Nomor WhatsApp terdaftar');
});

test('public rsvp form loads alumni data by whatsapp number', function () {
    $user = User::factory()->create(['whatsapp_number' => '6281234567890']);
    Alumni::factory()->create([
        'user_id' => $user->id,
        'full_name' => 'Bambang Geodesi',
        'nickname' => 'Bamgeo',
        'rsvp_status' => 'pending',
    ]);

    Livewire::test('pages::public.rsvp')
        ->set('lookup_whatsapp_number', '+62 812-3456-7890')
        ->call('verifyWhatsappNumber')
        ->assertHasNoErrors()
        ->assertSet('full_name', 'Bambang Geodesi')
        ->assertSet('nickname', 'Bamgeo')
        ->assertSet('whatsapp_number', '6281234567890')
        ->assertDontSee('Bersama keluarga')
        ->assertSee('Ukuran kaos alumni')
        ->assertSee('Jenis kaos alumni')
        ->assertSee('Data Diri dan RSVP');
});

test('public rsvp form does not expose alumni data for unknown whatsapp number', function () {
    $user = User::factory()->create(['whatsapp_number' => '6281234567890']);
    Alumni::factory()->create([
        'user_id' => $user->id,
        'full_name' => 'Nama Tidak Boleh Bocor',
    ]);

    Livewire::test('pages::public.rsvp')
        ->set('lookup_whatsapp_number', '6280000000000')
        ->call('verifyWhatsappNumber')
        ->assertHasErrors('lookup_whatsapp_number')
        ->assertSet('alumni', null)
        ->assertDontSee('Nama Tidak Boleh Bocor');
});

test('public rsvp hides family controls but keeps shirt controls when not attending', function () {
    $user = User::factory()->create(['whatsapp_number' => '6281234567890']);
    Alumni::factory()->create([
        'user_id' => $user->id,
        'rsvp_status' => 'attending',
        'rsvp_party_type' => 'family',
        'family_members_count' => 2,
    ]);

    Livewire::test('pages::public.rsvp')
        ->set('lookup_whatsapp_number', '6281234567890')
        ->call('verifyWhatsappNumber')
        ->set('rsvp_status', 'not_attending')
        ->assertSet('rsvp_party_type', 'self')
        ->assertSet('family_members_count', 0)
        ->assertDontSee('Bersama keluarga')
        ->assertSee('Ukuran kaos alumni')
        ->assertSee('Jenis kaos alumni');
});

test('public rsvp form updates alumni and user data', function () {
    $user = User::factory()->create([
        'name' => 'Nama Lama',
        'email' => 'lama@example.test',
        'whatsapp_number' => '6281234567890',
    ]);
    $alumni = Alumni::factory()->create([
        'user_id' => $user->id,
        'full_name' => 'Nama Lama',
        'rsvp_status' => 'pending',
    ]);

    Livewire::test('pages::public.rsvp')
        ->set('lookup_whatsapp_number', '6281234567890')
        ->call('verifyWhatsappNumber')
        ->set('full_name', 'Nama Baru Alumni')
        ->set('nickname', 'Baru')
        ->set('student_number', '960001')
        ->set('email', 'baru@example.test')
        ->set('whatsapp_number', '6289999999999')
        ->set('rsvp_status', 'attending')
        ->set('rsvp_party_type', 'family')
        ->set('family_members_count', 2)
        ->set('shirt_size', 'XL')
        ->set('shirt_type', 'male')
        ->set('family_members.0.shirt_size', 'M')
        ->set('family_members.0.shirt_type', 'female')
        ->set('family_members.1.shirt_size', 'S')
        ->set('family_members.1.shirt_type', 'child')
        ->set('company', 'PT Titik Nol')
        ->set('job_title', 'Surveyor')
        ->set('short_story', 'Masih aktif berkarya.')
        ->set('special_notes', 'Butuh menu vegetarian.')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('saved', true);

    $alumni->refresh();
    $user->refresh();

    expect($alumni->full_name)->toBe('Nama Baru Alumni');
    expect($alumni->student_number)->toBe('960001');
    expect($alumni->rsvp_status)->toBe('attending');
    expect($alumni->rsvp_party_type)->toBe('family');
    expect($alumni->family_members_count)->toBe(2);
    expect($alumni->shirt_size)->toBe('XL');
    expect($alumni->shirt_type)->toBe('male');
    expect($alumni->company)->toBe('PT Titik Nol');
    expect($alumni->special_notes)->toBe('Butuh menu vegetarian.');
    expect($alumni->is_profile_completed)->toBeTrue();
    expect($user->name)->toBe('Nama Baru Alumni');
    expect($user->email)->toBe('baru@example.test');
    expect($user->whatsapp_number)->toBe('6289999999999');
    expect($alumni->rsvpGuests()->count())->toBe(2);
    $this->assertDatabaseHas('alumni_rsvp_guests', [
        'alumni_id' => $alumni->id,
        'sequence' => 1,
        'shirt_size' => 'M',
        'shirt_type' => 'female',
    ]);
    $this->assertDatabaseHas('alumni_rsvp_guests', [
        'alumni_id' => $alumni->id,
        'sequence' => 2,
        'shirt_size' => 'S',
        'shirt_type' => 'child',
    ]);
});

test('public rsvp form shows closed message when disabled by setting', function () {
    ApplicationSetting::setBoolean(ApplicationSetting::PUBLIC_RSVP_FORM_ENABLED, false);

    $this->get(route('public.rsvp'))
        ->assertOk()
        ->assertSee('Form RSVP sedang ditutup')
        ->assertDontSee('Nomor WhatsApp terdaftar');
});

test('administrator can toggle public rsvp form setting', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);

    Livewire::actingAs($administrator)
        ->test('pages::admin.rsvp.index')
        ->assertSet('public_rsvp_form_enabled', true)
        ->call('togglePublicRsvpForm')
        ->assertSet('public_rsvp_form_enabled', false)
        ->assertSee('Ditutup');

    expect(ApplicationSetting::boolean(ApplicationSetting::PUBLIC_RSVP_FORM_ENABLED, true))->toBeFalse();
    expect(AuditLog::query()->where('action', 'settings.public_rsvp_form_updated')->exists())->toBeTrue();
});
