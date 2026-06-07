<?php

use App\Models\Alumni;
use App\Models\Role;
use App\Models\User;
use App\Models\WhatsappImport;
use App\Models\WhatsappStatistic;

test('guests are redirected from whatsapp analytics page', function () {
    $this->get(route('whatsapp.analytics'))
        ->assertRedirect(route('login'));
});

test('alumni users can view whatsapp analytics', function () {
    $profile = Alumni::factory()->create(['full_name' => 'Budi Santoso']);
    $whatsappImport = WhatsappImport::factory()->create([
        'status' => 'completed',
        'total_messages' => 120,
        'total_participants' => 12,
        'processed_at' => now(),
    ]);
    WhatsappStatistic::factory()->create([
        'whatsapp_import_id' => $whatsappImport->id,
        'category' => 'active_member',
        'label' => 'Budi',
        'value' => 20,
        'rank' => 1,
    ]);
    WhatsappStatistic::factory()->create([
        'whatsapp_import_id' => $whatsappImport->id,
        'category' => 'word_cloud',
        'label' => 'geodesi',
        'value' => 15,
        'rank' => 1,
    ]);

    $this->actingAs($profile->user)
        ->get(route('whatsapp.analytics'))
        ->assertOk()
        ->assertSee('WhatsApp Analytics')
        ->assertSee('Member Paling Aktif')
        ->assertSee('Budi')
        ->assertSee('geodesi')
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
        ->assertSee('WhatsApp Analytics')
        ->assertSee('Kelola Import');
});

test('regular users without alumni profile cannot view whatsapp analytics', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('whatsapp.analytics'))
        ->assertForbidden();
});
