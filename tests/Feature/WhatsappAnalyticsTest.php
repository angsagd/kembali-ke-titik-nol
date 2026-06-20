<?php

use App\Models\Alumni;
use App\Models\Role;
use App\Models\User;
use App\Models\WhatsappActivity;
use App\Models\WhatsappDailyStat;
use App\Models\WhatsappImport;
use App\Models\WhatsappMember;
use App\Models\WhatsappMemberStat;

test('guests are redirected from whatsapp analytics page', function () {
    $this->get(route('whatsapp.analytics'))
        ->assertRedirect(route('login'));
});

test('alumni users can view whatsapp analytics', function () {
    $profile = Alumni::factory()->create(['full_name' => 'Budi Santoso']);
    $whatsappImport = WhatsappImport::factory()->create([
        'status' => 'completed',
        'total_activities' => 130,
        'total_messages' => 120,
        'total_system_events' => 10,
        'total_participants' => 12,
        'total_media_messages' => 8,
        'total_sticker_messages' => 3,
        'total_link_messages' => 5,
        'total_deleted_messages' => 2,
        'total_words' => 75364,
        'first_activity_at' => now()->subDays(9)->setTime(7, 53),
        'last_activity_at' => now()->setTime(22, 15),
        'processed_at' => now(),
    ]);

    WhatsappDailyStat::factory()->create([
        'whatsapp_import_id' => $whatsappImport->id,
        'stat_date' => now()->toDateString(),
        'total_activities' => 30,
    ]);

    $member = WhatsappMember::factory()->create([
        'whatsapp_import_id' => $whatsappImport->id,
        'display_name' => 'Budi',
        'normalized_name' => 'budi',
        'total_messages' => 20,
    ]);

    WhatsappMemberStat::factory()->create([
        'whatsapp_import_id' => $whatsappImport->id,
        'whatsapp_member_id' => $member->id,
        'alumni_id' => $member->alumni_id,
        'total_messages' => 20,
        'emoji_messages' => 4,
        'link_messages' => 3,
    ]);

    WhatsappActivity::factory()->create([
        'whatsapp_import_id' => $whatsappImport->id,
        'activity_type' => 'system',
        'system_event_type' => 'member_left',
        'message_text' => null,
    ]);

    $this->actingAs($profile->user)
        ->get(route('whatsapp.analytics'))
        ->assertOk()
        ->assertSee('WhatsApp Group Analyzer')
        ->assertSee('Statistik Grup')
        ->assertSee('Top 10')
        ->assertSee('Statistik Personal')
        ->assertSee('Denyut Nadi Grup')
        ->assertSee('Peta Panas Aktivitas Grup')
        ->assertSee('Aktivitas Terakhir')
        ->assertSee('Total Kata')
        ->assertSee('75.364')
        ->assertSee('Rata-rata Pesan')
        ->assertSee('Pesan dengan Sticker')
        ->assertSee('Anggota Keluar')
        ->assertSee('Hari Favorit Buat Rame-Rame')
        ->assertSee('Kalender Keramaian Alumni')
        ->assertDontSee('Ganti Perangkat')
        ->assertDontSee('raw chat');
});

test('administrator users can view whatsapp analytics', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);

    $this->actingAs($administrator)
        ->get(route('whatsapp.analytics'))
        ->assertOk()
        ->assertSee('WhatsApp Group Analyzer')
        ->assertDontSee('Kelola Import');
});

test('regular users without alumni profile cannot view whatsapp analytics', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('whatsapp.analytics'))
        ->assertForbidden();
});
