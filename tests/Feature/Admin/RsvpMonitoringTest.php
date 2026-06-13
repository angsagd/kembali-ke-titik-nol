<?php

use App\Models\Alumni;
use App\Models\AlumniRsvpGuest;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected from rsvp monitoring', function () {
    $this->get(route('admin.rsvp.index'))
        ->assertRedirect(route('login'));
});

test('alumni users cannot access rsvp monitoring', function () {
    $profile = Alumni::factory()->create();

    $this->actingAs($profile->user)
        ->get(route('admin.rsvp.index'))
        ->assertForbidden();
});

test('administrator users can view rsvp monitoring summary', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);

    $attendingAlumni = Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'student_number' => 'D096001',
        'rsvp_status' => 'attending',
        'rsvp_party_type' => 'family',
        'family_members_count' => 2,
        'shirt_size' => 'XL',
        'shirt_type' => 'male',
    ]);
    AlumniRsvpGuest::factory()->create([
        'alumni_id' => $attendingAlumni->id,
        'sequence' => 1,
        'shirt_size' => 'M',
        'shirt_type' => 'female',
    ]);
    AlumniRsvpGuest::factory()->create([
        'alumni_id' => $attendingAlumni->id,
        'sequence' => 2,
        'shirt_size' => 'S',
        'shirt_type' => 'child',
    ]);
    Alumni::factory()->create([
        'full_name' => 'Budi Santoso',
        'student_number' => 'D096002',
        'rsvp_status' => 'not_attending',
    ]);
    Alumni::factory()->create([
        'full_name' => 'Citra Lestari',
        'student_number' => 'D096003',
        'rsvp_status' => 'pending',
    ]);

    $this->actingAs($administrator);

    $this->get(route('admin.rsvp.index'))
        ->assertOk()
        ->assertSee('Monitoring RSVP')
        ->assertDontSee('NIM')
        ->assertSee('Total Alumni')
        ->assertSee('Response Rate')
        ->assertSee('67%')
        ->assertSee('Total Peserta Hadir')
        ->assertSee('Bersama keluarga')
        ->assertSee('2 tambahan')
        ->assertSee('Alumni: Pria / XL')
        ->assertSee('Keluarga 1: Wanita / M')
        ->assertSee('Keluarga 2: Anak / S')
        ->assertSee('Ade Chandra')
        ->assertSee('Budi Santoso')
        ->assertSee('Citra Lestari')
        ->assertSee('Rekap Kaos')
        ->assertSee('Total: 3 kaos');

    $component = Livewire::test('pages::admin.rsvp.index');
    $shirtSummary = $component->get('shirtSummary');

    expect($shirtSummary['counts']['child']['S'])->toBe(1)
        ->and($shirtSummary['counts']['male']['XL'])->toBe(1)
        ->and($shirtSummary['counts']['female']['M'])->toBe(1)
        ->and($shirtSummary['totals'])->toBe([
            'child' => 1,
            'male' => 1,
            'female' => 1,
        ])
        ->and($shirtSummary['grand_total'])->toBe(3);
});

test('superadmin users can access rsvp monitoring', function () {
    $superadminRole = Role::factory()->create([
        'name' => 'superadmin',
        'description' => 'Pengelola utama sistem',
    ]);
    $superadmin = User::factory()->create(['role_id' => $superadminRole->id]);

    $this->actingAs($superadmin)
        ->get(route('admin.rsvp.index'))
        ->assertOk()
        ->assertSee('Monitoring RSVP');
});

test('administrator users can filter rsvp monitoring by status', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);

    Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'rsvp_status' => 'attending',
    ]);
    Alumni::factory()->create([
        'full_name' => 'Budi Santoso',
        'rsvp_status' => 'not_attending',
    ]);

    $this->actingAs($administrator);

    Livewire::test('pages::admin.rsvp.index')
        ->set('status', 'attending')
        ->assertSee('Ade Chandra')
        ->assertDontSee('Budi Santoso');
});

test('administrator users can search rsvp monitoring', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);

    Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'student_number' => 'D096001',
    ]);
    Alumni::factory()->create([
        'full_name' => 'Budi Santoso',
        'student_number' => 'D096002',
    ]);

    $this->actingAs($administrator);

    Livewire::test('pages::admin.rsvp.index')
        ->set('search', 'D096001')
        ->assertSee('Ade Chandra')
        ->assertDontSee('Budi Santoso');
});

test('administrator users can sort rsvp monitoring table', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);

    Alumni::factory()->create([
        'full_name' => 'Ade Chandra Sort',
        'rsvp_status' => 'pending',
    ]);
    Alumni::factory()->create([
        'full_name' => 'Budi Santoso Sort',
        'rsvp_status' => 'attending',
    ]);
    Alumni::factory()->create([
        'full_name' => 'Citra Lestari Sort',
        'rsvp_status' => 'not_attending',
    ]);

    $this->actingAs($administrator);

    Livewire::test('pages::admin.rsvp.index')
        ->assertSet('sort_by', 'full_name')
        ->assertSet('sort_direction', 'asc')
        ->assertSeeInOrder([
            'Ade Chandra Sort',
            'Budi Santoso Sort',
            'Citra Lestari Sort',
        ])
        ->call('sort', 'full_name')
        ->assertSet('sort_direction', 'desc')
        ->assertSeeInOrder([
            'Citra Lestari Sort',
            'Budi Santoso Sort',
            'Ade Chandra Sort',
        ])
        ->call('sort', 'rsvp_status')
        ->assertSet('sort_by', 'rsvp_status')
        ->assertSet('sort_direction', 'asc')
        ->assertSeeInOrder([
            'Budi Santoso Sort',
            'Citra Lestari Sort',
            'Ade Chandra Sort',
        ]);
});

test('rsvp monitoring pagination does not scroll the page', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);

    Alumni::factory()->count(16)->create();

    $this->actingAs($administrator);

    Livewire::test('pages::admin.rsvp.index')
        ->assertSee('Showing')
        ->assertDontSee('scrollIntoView', escape: false);
});
