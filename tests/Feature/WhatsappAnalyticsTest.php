<?php

use App\Models\Alumni;
use App\Models\Role;
use App\Models\User;
use App\Models\WhatsappActivity;
use App\Models\WhatsappDailyStat;
use App\Models\WhatsappImport;
use App\Models\WhatsappMember;
use App\Models\WhatsappMemberEventStat;
use App\Models\WhatsappMemberStat;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

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
        'alumni_id' => $profile->id,
        'display_name' => 'Budi',
        'normalized_name' => 'budi',
        'total_messages' => 20,
        'total_words' => 120,
    ]);

    WhatsappMemberStat::factory()->create([
        'whatsapp_import_id' => $whatsappImport->id,
        'whatsapp_member_id' => $member->id,
        'alumni_id' => $member->alumni_id,
        'total_messages' => 20,
        'media_messages' => 8,
        'sticker_messages' => 6,
        'emoji_messages' => 4,
        'link_messages' => 3,
        'deleted_messages' => 2,
        'location_messages' => 1,
        'morning_messages' => 7,
        'working_hour_messages' => 9,
        'after_work_messages' => 11,
        'midnight_messages' => 5,
        'weekend_messages' => 10,
        'active_days' => 12,
        'total_words' => 120,
    ]);

    WhatsappMemberEventStat::factory()->create([
        'whatsapp_import_id' => $whatsappImport->id,
        'whatsapp_member_id' => $member->id,
        'alumni_id' => $member->alumni_id,
        'member_added_as_actor' => 4,
        'member_left' => 1,
        'security_code_changed' => 3,
    ]);

    WhatsappActivity::factory()->forMember($member)->create([
        'whatsapp_import_id' => $whatsappImport->id,
        'has_media' => true,
        'message_text' => '<Media omitted>',
    ]);
    WhatsappActivity::factory()->forMember($member)->create([
        'whatsapp_import_id' => $whatsappImport->id,
        'has_sticker' => true,
        'has_media' => true,
        'message_text' => '<Sticker omitted>',
    ]);
    WhatsappActivity::factory()->forMember($member)->create([
        'whatsapp_import_id' => $whatsappImport->id,
        'has_link' => true,
        'message_text' => 'https://example.test',
    ]);
    WhatsappActivity::factory()->forMember($member)->create([
        'whatsapp_import_id' => $whatsappImport->id,
        'has_emoji' => true,
        'message_text' => 'Mantap reuni nostalgia 😊',
    ]);
    WhatsappActivity::factory()->forMember($member)->create([
        'whatsapp_import_id' => $whatsappImport->id,
        'activity_type' => 'system',
        'system_event_type' => 'deleted_message',
        'message_text' => 'This message was deleted',
        'is_deleted_message' => true,
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
        ->assertSee('Bahan Analisis')
        ->assertDontSee('Mapping Alumni')
        ->assertSee('Denyut Nadi Grup')
        ->assertSee('Kontur Keramaian Grup')
        ->assertSee('Aktivitas Terakhir')
        ->assertSee('Total Kata')
        ->assertSee('75.364')
        ->assertSee('Rata-rata Pesan')
        ->assertSee('Pesan dengan Sticker')
        ->assertSee('Anggota Keluar')
        ->assertSee('Hari Favorit Buat Rame-Rame')
        ->assertSee('Kalender Keramaian Alumni')
        ->assertSee('Word Cloud Grup')
        ->assertSee('mantap')
        ->assertSee('Jejak Digital Tahunan')
        ->assertDontSee('Ganti Perangkat')
        ->assertDontSee('raw chat');

    Livewire::actingAs($profile->user)
        ->test('pages::whatsapp.analytics')
        ->call('selectDigitalDate', now()->toDateString())
        ->assertSet('selectedDigitalDate', now()->toDateString())
        ->assertSee('Mantap reuni nostalgia')
        ->assertSee('Budi');

    Livewire::actingAs($profile->user)
        ->test('pages::whatsapp.analytics')
        ->call('selectTab', 'top10')
        ->assertSee('Top 10 Tukang Ketik')
        ->assertSee('Top 10 Juragan Dokumentasi')
        ->assertSee('Top 10 Stikerwan-Stikerwati')
        ->assertSee('Top 10 Agen Link Nasional')
        ->assertSee('Top 10 Duta Emoji')
        ->assertSee('Top 10 Jejak Terhapus')
        ->assertSee('Top 10 Shareloc Warrior')
        ->assertSee('Top 10 Pasukan Subuh Produktif')
        ->assertSee('Top 10 Produktif Tapi Fleksibel')
        ->assertSee('Top 10 After Office Club')
        ->assertSee('Top 10 Kalong Digital')
        ->assertSee('Top 10 Weekend Warrior')
        ->assertSee('Top 10 Kultum Terpanjang')
        ->assertSee('Top 10 Paling Konsisten')
        ->assertSee('Top 10 Mode Hemat Kata')
        ->assertSee('Top 10 Menambahkan Anggota')
        ->assertSee('Top 10 Titik Hilang dari Jaringan')
        ->assertSee('Top 10 Reobservasi Perangkat');

    Livewire::actingAs($profile->user)
        ->test('pages::whatsapp.analytics')
        ->call('selectTab', 'personal')
        ->assertSee('Budi')
        ->assertSee('Produktivitas Titik Kontrol')
        ->assertSee('Observasi Non-Verbal')
        ->assertSee('Ephemeris Kehadiran')
        ->assertSee('Azimuth Aktivitas Harian')
        ->assertSee('Jadwal Survei Sosial')
        ->assertSee('Musim Pengamatan Personal')
        ->assertSee('Menambahkan Anggota')
        ->assertSee('Keluar Grup')
        ->assertSee('Mengganti Perangkat')
        ->assertDontSee('Jejak Sistem Personal')
        ->assertSet('selectedWhatsappMemberIds', [$member->id])
        ->call('togglePersonalMember', $member->id)
        ->assertSet('selectedWhatsappMemberIds', []);
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
        ->assertSee('Mapping Alumni')
        ->assertSee('Bahan Analisis')
        ->assertDontSee('Kelola Import');

    Livewire::actingAs($administrator)
        ->test('pages::whatsapp.analytics')
        ->call('selectTab', 'mapping')
        ->assertSet('tab', 'mapping')
        ->assertSee('Pemetaan anggota WhatsApp ke data alumni akan disiapkan pada tahap berikutnya.');
});

test('alumni users can download whatsapp analytics source as txt zip', function () {
    Storage::fake('local');

    $profile = Alumni::factory()->create(['full_name' => 'Budi Santoso']);
    Storage::disk('local')->put('whatsapp-imports/source.txt', 'raw chat content');

    WhatsappImport::factory()->create([
        'status' => 'completed',
        'file_name' => 'whatsapp-chat.txt',
        'file_path' => 'whatsapp-imports/source.txt',
        'processed_at' => now(),
    ]);

    Livewire::actingAs($profile->user)
        ->test('pages::whatsapp.analytics')
        ->call('downloadAnalysisSource')
        ->assertFileDownloaded('whatsapp-chat.txt.zip');
});

test('regular users without alumni profile cannot view whatsapp analytics', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('whatsapp.analytics'))
        ->assertForbidden();
});
