<?php

use App\Models\Alumni;
use App\Services\WhatsappChatAnalyzer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('it converts whatsapp export text into aggregate statistics without raw chat', function () {
    Alumni::factory()->create(['full_name' => 'Budi Santoso', 'nickname' => 'Budi']);
    Alumni::factory()->create(['full_name' => 'Citra Lestari', 'nickname' => 'Citra']);

    $contents = implode("\n", [
        '01/01/2026, 08:00 - Budi: Selamat pagi geodesi, info reuni ada di https://example.test',
        '01/01/2026, 08:05 - Citra: Foto reuni <Media omitted>',
        '01/01/2026, 09:00 - Budi: Ngalibrasi paseduluran geodesi reuni',
    ]);

    $analysis = app(WhatsappChatAnalyzer::class)->analyze($contents);

    expect($analysis['total_messages'])->toBe(3);
    expect($analysis['total_participants'])->toBe(2);
    expect($analysis['import_start_date'])->toBe('2026-01-01');
    expect(collect($analysis['statistics'])->where('category', 'active_member')->first()['label'])->toBe('Budi');
    expect(collect($analysis['statistics'])->where('category', 'link_poster')->first()['label'])->toBe('Budi');
    expect(collect($analysis['statistics'])->where('category', 'word_cloud')->pluck('label')->all())->toContain('geodesi');
    expect(json_encode($analysis['statistics']))->not->toContain('Selamat pagi geodesi');
});

test('it prefers month day year format used by the alumni whatsapp export', function () {
    $contents = implode("\n", [
        '2/6/16, 08:53 - Budi: Long weekend',
        '5/8/22, 12:45 - Citra: Reuni',
    ]);

    $analysis = app(WhatsappChatAnalyzer::class)->analyze($contents);

    expect($analysis['import_start_date'])->toBe('2016-02-06');
    expect($analysis['import_end_date'])->toBe('2022-05-08');
});
