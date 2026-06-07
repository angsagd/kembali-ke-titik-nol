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

test('administrator users can access whatsapp import page', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);

    $this->actingAs($administrator)
        ->get(route('admin.whatsapp.index'))
        ->assertOk()
        ->assertSee('WhatsApp Import')
        ->assertSee('Upload Export Chat');
});

test('alumni users cannot access whatsapp import page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.whatsapp.index'))
        ->assertForbidden();
});

test('administrator users can upload and process whatsapp export', function () {
    Storage::fake('local');

    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);
    $chat = File::createWithContent('chat.txt', implode("\n", [
        '01/01/2026, 08:00 - Budi: Selamat pagi geodesi',
        '01/01/2026, 08:05 - Citra: Info reuni https://example.test',
        '01/01/2026, 09:00 - Budi: Foto <Media omitted>',
    ]));

    $this->actingAs($administrator);

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
    expect($whatsappImport->total_messages)->toBe(3);
    expect($whatsappImport->total_participants)->toBe(2);
    expect(WhatsappStatistic::query()->where('category', 'active_member')->exists())->toBeTrue();
    expect(WhatsappStatistic::query()->where('category', 'word_cloud')->exists())->toBeTrue();
    expect(AuditLog::query()->where('action', 'whatsapp_import.processed')->exists())->toBeTrue();
});
