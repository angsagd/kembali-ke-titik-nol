<?php

use App\Models\Alumni;
use App\Models\Role;
use App\Models\User;

function navigationUserWithRole(string $roleName): User
{
    $role = Role::factory()->create(['name' => $roleName]);

    return User::factory()->create(['role_id' => $role->id]);
}

test('public navigation presents the alumni portal and safe public destinations', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Portal Alumni Geodesi 96')
        ->assertSee('Beranda')
        ->assertSee('Tentang')
        ->assertSee('Kabar')
        ->assertSee('Dokumentasi')
        ->assertSee('Reuni 30 Tahun')
        ->assertSee('Login')
        ->assertSee('data-public-mobile-menu', false)
        ->assertSee('href="'.route('news.index').'"', false)
        ->assertSee('href="'.route('public.gallery').'"', false)
        ->assertDontSee('Direktori Alumni')
        ->assertDontSee('Administrasi Alumni');
});

test('alumni navigation is alumni first and contains personal reunion destinations', function () {
    $alumni = Alumni::factory()->create();

    $this->actingAs($alumni->user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Portal Alumni')
        ->assertSee('Alumni')
        ->assertSee('Direktori Alumni')
        ->assertSee('Perjalanan')
        ->assertSee('Peta Alumni')
        ->assertSee('Timeline Perjalanan')
        ->assertSee('Dokumentasi &amp; Kenangan', false)
        ->assertSee('Arsip Dokumentasi')
        ->assertSee('Buku Kenangan')
        ->assertSee('WhatsApp Analytics')
        ->assertSee('Kabar Alumni')
        ->assertSee('Reuni 30 Tahun')
        ->assertSee('RSVP Saya')
        ->assertSee('Pembayaran Saya')
        ->assertSee('Kamar Saya')
        ->assertSee('Profil Saya')
        ->assertDontSee('Administrasi Alumni')
        ->assertDontSee('Pembayaran &amp; Donasi', false);
});

test('administrator navigation exposes operational administration without finance or system access', function () {
    $administrator = navigationUserWithRole('administrator');

    $this->actingAs($administrator)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Administrasi Alumni')
        ->assertSee('Data Alumni')
        ->assertSee('Administrasi Konten')
        ->assertSee('Kelola Dokumentasi')
        ->assertSee('Kelola Kabar')
        ->assertSee('Administrasi Reuni 30 Tahun')
        ->assertSee('Monitoring RSVP')
        ->assertSee('Rooming')
        ->assertSee('Rangkaian Acara')
        ->assertDontSee('WhatsApp Import')
        ->assertDontSee('Audit Log')
        ->assertDontSee('Administrasi Keuangan');
});

test('bendahara navigation is limited to reunion finance', function () {
    $bendahara = navigationUserWithRole('bendahara');

    $this->actingAs($bendahara)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Portal Alumni')
        ->assertSee('Kabar Alumni')
        ->assertSee('Administrasi Keuangan')
        ->assertSee('Pembayaran &amp; Donasi', false)
        ->assertDontSee('Administrasi Alumni')
        ->assertDontSee('Administrasi Konten')
        ->assertDontSee('Administrasi Sistem');
});

test('superadmin navigation exposes all existing administrative capabilities', function () {
    $superadmin = navigationUserWithRole('superadmin');

    $this->actingAs($superadmin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Administrasi Alumni')
        ->assertSee('Administrasi Konten')
        ->assertSee('Administrasi Reuni 30 Tahun')
        ->assertSee('Administrasi Analytics')
        ->assertSee('WhatsApp Import')
        ->assertSee('Administrasi Keuangan')
        ->assertSee('Pembayaran &amp; Donasi', false)
        ->assertSee('Administrasi Sistem')
        ->assertSee('Audit Log');
});
