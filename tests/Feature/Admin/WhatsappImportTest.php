<?php

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use App\Models\WhatsappImport;
use App\Models\WhatsappStatistic;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('guests are redirected from whatsapp import page', function () {
    $this->get(route('admin.whatsapp.index'))
        ->assertRedirect(route('login'));
});

test('administrator users cannot access whatsapp import page', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);

    $this->actingAs($administrator)
        ->get(route('admin.whatsapp.index'))
        ->assertForbidden();
});

test('superadmin users can access whatsapp import page', function () {
    $superadminRole = Role::factory()->create([
        'name' => 'superadmin',
        'description' => 'Pengelola teknis sistem',
    ]);
    $superadmin = User::factory()->create(['role_id' => $superadminRole->id]);

    $this->actingAs($superadmin)
        ->get(route('admin.whatsapp.index'))
        ->assertOk()
        ->assertSee('WhatsApp Import')
        ->assertSee('Upload Export Chat')
        ->assertSee('maksimum 10 MB')
        ->assertSee('Mengunggah file...');
});

test('project upload limits support whatsapp exports up to ten megabytes', function () {
    $userIni = file_get_contents(public_path('.user.ini'));
    $composer = json_decode(file_get_contents(base_path('composer.json')), true, flags: JSON_THROW_ON_ERROR);
    $developmentCommand = collect($composer['scripts']['dev'])
        ->first(fn (string $command): bool => str_contains($command, 'artisan serve'));

    expect($userIni)
        ->toContain('upload_max_filesize=12M')
        ->toContain('post_max_size=13M')
        ->and($developmentCommand)
        ->toContain('upload_max_filesize=12M')
        ->toContain('post_max_size=13M');
});

test('alumni users cannot access whatsapp import page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.whatsapp.index'))
        ->assertForbidden();
});

test('superadmin users can upload and process whatsapp export', function () {
    Storage::fake('local');

    $superadminRole = Role::factory()->create([
        'name' => 'superadmin',
        'description' => 'Pengelola teknis sistem',
    ]);
    $superadmin = User::factory()->create(['role_id' => $superadminRole->id]);
    $chat = File::createWithContent('chat.txt', implode("\n", [
        '01/01/2026, 08:00 - Budi: Selamat pagi geodesi',
        '01/01/2026, 08:05 - Citra: Info reuni https://example.test',
        '01/01/2026, 09:00 - Budi: Foto <Media omitted>',
        '01/01/2026, 23:30 - Budi: Malam nostalgia geodesi 😄😄',
        '01/02/2026, 10:00 - Citra: Kerja sambil bahas reuni',
        '01/03/2026, 11:00 - Budi: Weekend kumpul geodesi 😄',
        '01/03/2026, 11:30 - Dodi: Hadir reuni',
    ]));

    $this->actingAs($superadmin);

    Livewire::test('pages::admin.whatsapp.index')
        ->set('chat_file', $chat)
        ->set('notes', 'Import test')
        ->call('saveImport')
        ->assertHasNoErrors();

    $whatsappImport = WhatsappImport::query()->firstOrFail();
    Storage::disk('local')->assertExists($whatsappImport->file_path);

    Livewire::test('pages::admin.whatsapp.index')
        ->call('processImport', $whatsappImport->id)
        ->assertHasNoErrors();

    $whatsappImport->refresh();

    expect($whatsappImport->status)->toBe('completed');
    expect($whatsappImport->total_messages)->toBe(7);
    expect($whatsappImport->total_participants)->toBe(3);
    expect(WhatsappStatistic::query()->where('category', 'active_member')->exists())->toBeTrue();
    expect(WhatsappStatistic::query()->where('category', 'link_poster')->exists())->toBeTrue();
    expect(WhatsappStatistic::query()->where('category', 'image_poster')->exists())->toBeTrue();
    expect(WhatsappStatistic::query()->where('category', 'nocturnal_chatter')->exists())->toBeTrue();
    expect(WhatsappStatistic::query()->where('category', 'work_time_chatter')->exists())->toBeTrue();
    expect(WhatsappStatistic::query()->where('category', 'weekend_warrior')->exists())->toBeTrue();
    expect(WhatsappStatistic::query()->where('category', 'emoji_champion')->exists())->toBeTrue();
    expect(WhatsappStatistic::query()->where('category', 'top_topic')->exists())->toBeTrue();
    expect(WhatsappStatistic::query()->where('category', 'word_cloud')->exists())->toBeTrue();
    expect(AuditLog::query()->where('action', 'whatsapp_import.processed')->exists())->toBeTrue();
});

test('superadmin users can only upload whatsapp text export files', function () {
    Storage::fake('local');

    $superadminRole = Role::factory()->create([
        'name' => 'superadmin',
        'description' => 'Pengelola teknis sistem',
    ]);
    $superadmin = User::factory()->create(['role_id' => $superadminRole->id]);

    $this->actingAs($superadmin);

    Livewire::test('pages::admin.whatsapp.index')
        ->set('chat_file', File::createWithContent('chat.csv', 'not a whatsapp text export'))
        ->call('saveImport')
        ->assertHasErrors(['chat_file']);
});
