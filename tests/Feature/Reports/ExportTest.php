<?php

use App\Models\Alumni;
use App\Models\AlumniRsvpGuest;
use App\Models\City;
use App\Models\Country;
use App\Models\Donation;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\User;

test('guests are redirected from report exports', function (string $route) {
    $this->get(route($route))
        ->assertRedirect(route('login'));
})->with([
    'alumni' => 'reports.alumni.export',
    'rsvp' => 'reports.rsvp.export',
    'rooming export' => 'reports.rooming.export',
    'rooming print' => 'reports.rooming.print',
    'payments' => 'reports.payments.export',
    'donations' => 'reports.donations.export',
]);

test('alumni users cannot export reports', function (string $route) {
    $profile = Alumni::factory()->create();

    $this->actingAs($profile->user)
        ->get(route($route))
        ->assertForbidden();
})->with([
    'alumni' => 'reports.alumni.export',
    'rsvp' => 'reports.rsvp.export',
    'rooming export' => 'reports.rooming.export',
    'rooming print' => 'reports.rooming.print',
    'payments' => 'reports.payments.export',
    'donations' => 'reports.donations.export',
]);

test('administrator users can export alumni report', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);
    $country = Country::factory()->create(['name' => 'Indonesia']);
    $city = City::factory()->create([
        'country_id' => $country->id,
        'name' => 'Yogyakarta',
    ]);
    $profile = Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'student_number' => 'D096001',
        'nickname' => 'Ade',
        'email' => 'ade@example.test',
        'current_city_id' => $city->id,
        'current_country_id' => $country->id,
        'company' => 'Geo Nusantara',
        'job_title' => 'Survey Manager',
        'rsvp_status' => 'attending',
        'is_profile_completed' => true,
    ]);
    Payment::factory()->create([
        'alumni_id' => $profile->id,
        'status' => 'paid',
    ]);
    Donation::factory()->create(['alumni_id' => $profile->id]);

    $response = $this->actingAs($administrator)
        ->get(route('reports.alumni.export'));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    expect($response->streamedContent())
        ->toContain('Nama,NIM,"Nama Panggilan",WhatsApp,Email,Kota,Negara,Perusahaan,Jabatan,"Status Alumni","Status RSVP","Status Pembayaran","Status Donasi",Kamar,"Profil Lengkap","Terakhir Diperbarui"')
        ->toContain('"Ade Chandra"')
        ->toContain('D096001')
        ->toContain('Yogyakarta')
        ->toContain('Indonesia')
        ->toContain('Hadir')
        ->toContain('Lunas')
        ->toContain('"Ada Donasi"')
        ->toContain('Ya');
});

test('administrator users can export rsvp report', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);

    $profile = Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'student_number' => 'D096001',
        'rsvp_status' => 'attending',
        'rsvp_party_type' => 'family',
        'family_members_count' => 2,
        'shirt_size' => 'XL',
        'shirt_type' => 'male',
    ]);
    AlumniRsvpGuest::factory()->create([
        'alumni_id' => $profile->id,
        'sequence' => 1,
        'shirt_size' => 'M',
        'shirt_type' => 'female',
    ]);
    AlumniRsvpGuest::factory()->create([
        'alumni_id' => $profile->id,
        'sequence' => 2,
        'shirt_size' => 'S',
        'shirt_type' => 'child',
    ]);

    $response = $this->actingAs($administrator)
        ->get(route('reports.rsvp.export'));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    expect($response->streamedContent())
        ->toContain('Nama,NIM,WhatsApp,"Status RSVP",Kehadiran,"Jumlah Keluarga Tambahan","Total Peserta","Kaos Alumni","Kaos Keluarga","Terakhir Diperbarui"')
        ->toContain('"Ade Chandra"')
        ->toContain('D096001')
        ->toContain('Hadir')
        ->toContain('"Bersama keluarga"')
        ->toContain('Pria / XL')
        ->toContain('Keluarga 1: Wanita / M')
        ->toContain('Keluarga 2: Anak / S');
});

test('administrator users can export rooming list report', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);
    $room = Room::factory()->create([
        'room_name' => 'Kamar 01',
        'room_type' => 'Twin Share',
        'capacity' => 2,
        'location_notes' => 'Lantai 2',
        'notes' => 'Dekat lift.',
    ]);
    $profile = Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'student_number' => 'D096001',
    ]);

    RoomAssignment::factory()->create([
        'room_id' => $room->id,
        'alumni_id' => $profile->id,
    ]);

    $response = $this->actingAs($administrator)
        ->get(route('reports.rooming.export'));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    expect($response->streamedContent())
        ->toContain('"Nama Kamar","Tipe Kamar",Kapasitas,"Jumlah Penghuni",Penghuni,"Catatan Lokasi","Catatan Kamar"')
        ->toContain('"Kamar 01"')
        ->toContain('"Twin Share"')
        ->toContain('"Ade Chandra"')
        ->toContain('"Lantai 2"');
});

test('administrator users can print rooming list', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);
    $room = Room::factory()->create([
        'room_name' => 'Kamar 01',
        'room_type' => 'Twin Share',
        'capacity' => 2,
    ]);
    $profile = Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'student_number' => 'D096001',
    ]);

    RoomAssignment::factory()->create([
        'room_id' => $room->id,
        'alumni_id' => $profile->id,
    ]);

    $this->actingAs($administrator)
        ->get(route('reports.rooming.print'))
        ->assertOk()
        ->assertSee('Rooming List')
        ->assertSee('Kamar 01')
        ->assertSee('Twin Share')
        ->assertSee('Ade Chandra')
        ->assertSee('D096001');
});

test('administrator users cannot export finance reports', function (string $route) {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);

    $this->actingAs($administrator)
        ->get(route($route))
        ->assertForbidden();
})->with([
    'payments' => 'reports.payments.export',
    'donations' => 'reports.donations.export',
]);

test('bendahara users can export payment report', function () {
    $bendaharaRole = Role::factory()->create([
        'name' => 'bendahara',
        'description' => 'Bendahara reuni',
    ]);
    $bendahara = User::factory()->create(['role_id' => $bendaharaRole->id]);
    $profile = Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'student_number' => 'D096001',
    ]);

    Payment::factory()->create([
        'alumni_id' => $profile->id,
        'amount' => 1000000,
        'status' => 'paid',
        'payment_date' => '2026-06-06',
        'verified_by' => $bendahara->id,
        'verified_at' => '2026-06-06 10:00:00',
        'notes' => 'Transfer lunas.',
    ]);

    $response = $this->actingAs($bendahara)
        ->get(route('reports.payments.export'));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    expect($response->streamedContent())
        ->toContain('Nama,NIM,WhatsApp,Nominal,"Status Pembayaran","Tanggal Pembayaran","Diverifikasi Oleh","Tanggal Verifikasi",Catatan')
        ->toContain('"Ade Chandra"')
        ->toContain('1000000.00')
        ->toContain('Lunas')
        ->toContain('"Transfer lunas."');
});

test('bendahara users can export donation report with amounts', function () {
    $bendaharaRole = Role::factory()->create([
        'name' => 'bendahara',
        'description' => 'Bendahara reuni',
    ]);
    $bendahara = User::factory()->create(['role_id' => $bendaharaRole->id]);
    $profile = Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'student_number' => 'D096001',
    ]);

    Donation::factory()->create([
        'alumni_id' => $profile->id,
        'amount' => 2500000,
        'publication_status' => 'anonymous',
        'managed_by' => $bendahara->id,
        'notes' => 'Titip anonim.',
    ]);

    $response = $this->actingAs($bendahara)
        ->get(route('reports.donations.export'));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    expect($response->streamedContent())
        ->toContain('Nama,NIM,WhatsApp,Nominal,"Status Publikasi","Dikelola Oleh","Tanggal Donasi",Catatan')
        ->toContain('"Ade Chandra"')
        ->toContain('2500000.00')
        ->toContain('Anonim')
        ->toContain('"Titip anonim."');
});
