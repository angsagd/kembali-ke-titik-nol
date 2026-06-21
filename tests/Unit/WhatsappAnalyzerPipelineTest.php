<?php

use App\Models\Alumni;
use App\Models\User;
use App\Models\WhatsappImport;
use App\Models\WhatsappMember;
use App\Models\WhatsappMemberEventStat;
use App\Models\WhatsappMemberMapping;
use App\Models\WhatsappMemberStat;
use App\Services\WhatsAppAnalyzer\WhatsappImportProcessor;
use App\Services\WhatsAppAnalyzer\WhatsappParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('parser converts whatsapp export into full activities with display timezone', function () {
    $contents = implode("\n", [
        '2/6/16, 08:53 - Budi Santoso: Selamat long weekend 😊',
        'lanjutan pesan',
        '2/6/16, 12:23 - Zaim Nur Hidayat left',
        '2/6/16, 13:00 - Budi Santoso: This message was deleted',
    ]);

    $activities = app(WhatsappParser::class)->parse($contents);

    expect($activities)->toHaveCount(3)
        ->and($activities[0]->activityType)->toBe('message')
        ->and($activities[0]->messageText)->toContain("Selamat long weekend 😊\nlanjutan pesan")
        ->and($activities[0]->occurredAtSource->format('H:i'))->toBe('08:53')
        ->and($activities[0]->occurredAtDisplay->format('H:i'))->toBe('07:53')
        ->and($activities[1]->systemEventType)->toBe('member_left')
        ->and($activities[1]->targetName)->toBe('Zaim Nur Hidayat')
        ->and($activities[2]->activityType)->toBe('system')
        ->and($activities[2]->systemEventType)->toBe('deleted_message')
        ->and($activities[2]->isDeletedMessage)->toBeTrue();
});

test('processor stores raw activities members and personal stats', function () {
    $alumni = Alumni::factory()->create([
        'full_name' => 'Budi Santoso',
        'nickname' => 'Budi',
        'alumni_status' => 'active',
    ]);

    $import = WhatsappImport::factory()->create([
        'uploaded_by' => User::factory(),
        'status' => 'uploaded',
        'total_messages' => 0,
        'total_participants' => 0,
    ]);

    $contents = implode("\n", [
        '2/6/16, 08:53 - Budi: Selamat long weekend 😊',
        '2/6/16, 09:10 - Citra Lestari: Foto reuni <Media omitted>',
        '2/6/16, 10:00 - Budi: Info reuni https://example.test',
        '2/6/16, 12:23 - Budi added Citra Lestari',
        '2/6/16, 13:00 - Budi: This message was deleted',
        '2/7/16, 00:20 - Citra Lestari: Kalong digital',
    ]);

    app(WhatsappImportProcessor::class)->process($import, $contents);

    $import->refresh();

    expect($import->status)->toBe('completed')
        ->and($import->total_lines)->toBe(6)
        ->and($import->total_activities)->toBe(6)
        ->and($import->total_messages)->toBe(4)
        ->and($import->total_system_events)->toBe(2)
        ->and($import->total_participants)->toBe(2)
        ->and($import->total_deleted_messages)->toBe(1)
        ->and($import->first_activity_at?->format('Y-m-d H:i'))->toBe('2016-02-06 07:53');

    $budi = WhatsappMember::query()
        ->where('whatsapp_import_id', $import->id)
        ->where('normalized_name', 'budi')
        ->firstOrFail();

    expect($budi->alumni_id)->toBe($alumni->id)
        ->and($budi->total_messages)->toBe(2);

    $budiStats = WhatsappMemberStat::query()
        ->where('whatsapp_member_id', $budi->id)
        ->firstOrFail();

    expect($budiStats->emoji_messages)->toBe(1)
        ->and($budiStats->link_messages)->toBe(1)
        ->and($budiStats->deleted_messages)->toBe(1);

    $budiEvents = WhatsappMemberEventStat::query()
        ->where('whatsapp_member_id', $budi->id)
        ->firstOrFail();

    expect($budiEvents->member_added_as_actor)->toBe(1);

    expect($import->activities()->count())->toBe(6)
        ->and($import->dailyStats()->count())->toBe(1);
});

test('processor applies saved whatsapp member mappings to new imports', function () {
    $alumni = Alumni::factory()->create([
        'full_name' => 'I Made Alumni',
        'nickname' => null,
        'alumni_status' => 'active',
    ]);
    $mapping = WhatsappMemberMapping::factory()->linkedToAlumni($alumni)->create([
        'display_name' => 'Budi WA',
        'normalized_name' => 'budi',
    ]);
    $import = WhatsappImport::factory()->create([
        'uploaded_by' => User::factory(),
        'status' => 'uploaded',
    ]);

    app(WhatsappImportProcessor::class)->process($import, implode("\n", [
        '2/6/16, 08:53 - Budi: Selamat pagi',
        '2/6/16, 09:10 - Budi: Info reuni https://example.test',
    ]));

    $member = WhatsappMember::query()
        ->where('whatsapp_import_id', $import->id)
        ->where('normalized_name', 'budi')
        ->firstOrFail();

    expect($member->whatsapp_member_mapping_id)->toBe($mapping->id)
        ->and($member->alumni_id)->toBe($alumni->id)
        ->and($member->activities()->where('alumni_id', $alumni->id)->count())->toBe(2);

    $this->assertDatabaseHas('whatsapp_member_stats', [
        'whatsapp_member_id' => $member->id,
        'alumni_id' => $alumni->id,
    ]);
    $this->assertDatabaseHas('whatsapp_member_event_stats', [
        'whatsapp_member_id' => $member->id,
        'alumni_id' => $alumni->id,
    ]);
});
