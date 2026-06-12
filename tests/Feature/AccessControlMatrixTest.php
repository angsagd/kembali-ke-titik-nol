<?php

use App\Models\Alumni;
use App\Models\MediaItem;
use App\Models\Role;
use App\Models\User;

function userWithRole(string $roleName): User
{
    $role = Role::factory()->create(['name' => $roleName]);

    return User::factory()->create(['role_id' => $role->id]);
}

test('guests are redirected from private mvp routes', function (string $route) {
    $this->get(route($route))
        ->assertRedirect(route('login'));
})->with([
    'dashboard',
    'alumni.profile',
    'alumni.rsvp',
    'alumni.room',
    'alumni.finance',
    'documentation.index',
    'admin.alumni.index',
    'finance.index',
    'admin.audit-logs.index',
]);

test('alumni role can access alumni area but cannot access admin finance or audit area', function () {
    $alumni = Alumni::factory()->create();

    $this->actingAs($alumni->user)
        ->get(route('dashboard'))
        ->assertOk();
    $this->actingAs($alumni->user)
        ->get(route('alumni.profile'))
        ->assertOk();
    $this->actingAs($alumni->user)
        ->get(route('documentation.index'))
        ->assertOk();
    $this->actingAs($alumni->user)
        ->get(route('admin.alumni.index'))
        ->assertForbidden();
    $this->actingAs($alumni->user)
        ->get(route('finance.index'))
        ->assertForbidden();
    $this->actingAs($alumni->user)
        ->get(route('admin.audit-logs.index'))
        ->assertForbidden();
});

test('administrator role can manage operations but cannot access finance or audit logs', function () {
    $administrator = userWithRole('administrator');

    $this->actingAs($administrator)
        ->get(route('admin.alumni.index'))
        ->assertOk();
    $this->actingAs($administrator)
        ->get(route('admin.rsvp.index'))
        ->assertOk();
    $this->actingAs($administrator)
        ->get(route('admin.rooming.index'))
        ->assertOk();
    $this->actingAs($administrator)
        ->get(route('admin.whatsapp.index'))
        ->assertForbidden();
    $this->actingAs($administrator)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('WhatsApp Import');
    $this->actingAs($administrator)
        ->get(route('whatsapp.analytics'))
        ->assertOk()
        ->assertDontSee('Kelola Import');
    $this->actingAs($administrator)
        ->get(route('finance.index'))
        ->assertForbidden();
    $this->actingAs($administrator)
        ->get(route('admin.audit-logs.index'))
        ->assertForbidden();
});

test('bendahara role can manage finance but cannot manage alumni operations', function () {
    $bendahara = userWithRole('bendahara');

    $this->actingAs($bendahara)
        ->get(route('finance.index'))
        ->assertOk();
    $this->actingAs($bendahara)
        ->get(route('admin.alumni.index'))
        ->assertForbidden();
    $this->actingAs($bendahara)
        ->get(route('admin.rooming.index'))
        ->assertForbidden();
    $this->actingAs($bendahara)
        ->get(route('admin.audit-logs.index'))
        ->assertForbidden();
});

test('superadmin role can access protected operational finance and audit areas', function () {
    $superadmin = userWithRole('superadmin');

    $this->actingAs($superadmin)
        ->get(route('admin.alumni.index'))
        ->assertOk();
    $this->actingAs($superadmin)
        ->get(route('admin.whatsapp.index'))
        ->assertOk();
    $this->actingAs($superadmin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('WhatsApp Import');
    $this->actingAs($superadmin)
        ->get(route('whatsapp.analytics'))
        ->assertOk()
        ->assertSee('Kelola Import');
    $this->actingAs($superadmin)
        ->get(route('finance.index'))
        ->assertOk();
    $this->actingAs($superadmin)
        ->get(route('admin.audit-logs.index'))
        ->assertOk();
});

test('documentation detail follows internal gallery access rules', function () {
    $alumni = Alumni::factory()->create();
    $mediaItem = MediaItem::factory()->photo()->create([
        'uploaded_by_alumni_id' => $alumni->id,
        'visibility' => 'internal',
        'title' => 'Dokumentasi Internal',
    ]);

    $this->get(route('documentation.show', $mediaItem))
        ->assertRedirect(route('login'));

    $this->actingAs($alumni->user)
        ->get(route('documentation.show', $mediaItem))
        ->assertOk()
        ->assertSee('Dokumentasi Internal');
});
