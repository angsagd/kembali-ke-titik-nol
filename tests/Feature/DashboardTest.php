<?php

use App\Models\Alumni;
use App\Models\Donation;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('alumni users see personal dashboard without donation amount', function () {
    $profile = Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'nickname' => 'Ade',
        'rsvp_status' => 'attending',
        'is_profile_completed' => false,
    ]);

    Payment::factory()->create([
        'alumni_id' => $profile->id,
        'status' => 'pending_verification',
        'amount' => 1000000,
    ]);
    Donation::factory()->create([
        'alumni_id' => $profile->id,
        'amount' => 2500000,
        'publication_status' => 'anonymous',
    ]);

    $this->actingAs($profile->user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Selamat datang, Ade')
        ->assertSee('Hadir')
        ->assertSee('Menunggu Verifikasi')
        ->assertSee('Kelengkapan Profil')
        ->assertDontSee('2.500.000')
        ->assertDontSee('2500000');
});

test('administrator users see operational dashboard but not finance totals', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);

    Alumni::factory()->create(['rsvp_status' => 'attending']);
    Alumni::factory()->create(['rsvp_status' => 'not_attending']);
    Donation::factory()->create(['amount' => 2500000]);
    Payment::factory()->create(['status' => 'pending_verification']);

    $this->actingAs($administrator)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Dashboard Administrator')
        ->assertSee('Total Alumni')
        ->assertSee('RSVP Hadir')
        ->assertSee('Pembayaran Menunggu')
        ->assertSee('Jumlah Donatur')
        ->assertDontSee('Total Dana Terkumpul')
        ->assertDontSee('2.500.000');
});

test('bendahara users see finance dashboard', function () {
    $bendaharaRole = Role::factory()->create([
        'name' => 'bendahara',
        'description' => 'Bendahara reuni',
    ]);
    $bendahara = User::factory()->create(['role_id' => $bendaharaRole->id]);
    $profile = Alumni::factory()->create(['full_name' => 'Ade Chandra']);

    Payment::factory()->create([
        'alumni_id' => $profile->id,
        'status' => 'paid',
        'amount' => 1000000,
        'payment_date' => '2026-06-06',
    ]);
    Donation::factory()->create([
        'alumni_id' => $profile->id,
        'amount' => 2500000,
        'publication_status' => 'show_name',
    ]);

    $this->actingAs($bendahara)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Dashboard Bendahara')
        ->assertSee('Total Dana Terkumpul')
        ->assertSee('Rp 3.500.000')
        ->assertSee('Monitoring Pembayaran')
        ->assertSee('Monitoring Donasi');
});

test('superadmin users see system dashboard', function () {
    $superadminRole = Role::factory()->create([
        'name' => 'superadmin',
        'description' => 'Pengelola utama sistem',
    ]);
    $superadmin = User::factory()->create([
        'role_id' => $superadminRole->id,
        'last_login_at' => now(),
    ]);

    $this->actingAs($superadmin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Dashboard Superadmin')
        ->assertSee('Dashboard Bendahara')
        ->assertSee('KPI Sistem')
        ->assertSee('Login Hari Ini');
});
