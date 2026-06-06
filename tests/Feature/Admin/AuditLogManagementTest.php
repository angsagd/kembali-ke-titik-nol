<?php

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected from audit log page', function () {
    $this->get(route('admin.audit-logs.index'))
        ->assertRedirect(route('login'));
});

test('administrator users cannot access audit log page', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);

    $this->actingAs($administrator)
        ->get(route('admin.audit-logs.index'))
        ->assertForbidden();
});

test('superadmin users can view audit logs', function () {
    $superadminRole = Role::factory()->create([
        'name' => 'superadmin',
        'description' => 'Pengelola teknis sistem',
    ]);
    $superadmin = User::factory()->create(['role_id' => $superadminRole->id]);

    AuditLog::factory()->create([
        'user_id' => $superadmin->id,
        'action' => 'news.created',
        'entity_type' => 'news',
        'entity_id' => 10,
        'new_values' => ['title' => 'Pengumuman Reuni'],
    ]);

    $this->actingAs($superadmin)
        ->get(route('admin.audit-logs.index'))
        ->assertOk()
        ->assertSee('Audit Log')
        ->assertSee('news.created')
        ->assertSee('Pengumuman Reuni');
});

test('superadmin users can filter audit logs by action', function () {
    $superadminRole = Role::factory()->create([
        'name' => 'superadmin',
        'description' => 'Pengelola teknis sistem',
    ]);
    $superadmin = User::factory()->create(['role_id' => $superadminRole->id]);

    AuditLog::factory()->create(['action' => 'news.created']);
    AuditLog::factory()->create(['action' => 'media.uploaded']);

    $this->actingAs($superadmin);

    Livewire::test('pages::admin.audit-logs.index')
        ->set('action', 'news.created')
        ->assertSet('action', 'news.created')
        ->assertSee('news.created');
});
