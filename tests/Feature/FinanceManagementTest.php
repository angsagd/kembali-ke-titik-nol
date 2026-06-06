<?php

use App\Models\Alumni;
use App\Models\Donation;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected from finance pages', function () {
    $this->get(route('finance.index'))
        ->assertRedirect(route('login'));

    $this->get(route('alumni.finance'))
        ->assertRedirect(route('login'));
});

test('alumni users cannot access finance management', function () {
    $profile = Alumni::factory()->create();

    $this->actingAs($profile->user)
        ->get(route('finance.index'))
        ->assertForbidden();
});

test('administrator users cannot access finance management', function () {
    $administratorRole = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);

    $this->actingAs($administrator)
        ->get(route('finance.index'))
        ->assertForbidden();
});

test('bendahara users can access finance management', function () {
    $bendaharaRole = Role::factory()->create([
        'name' => 'bendahara',
        'description' => 'Bendahara reuni',
    ]);
    $bendahara = User::factory()->create(['role_id' => $bendaharaRole->id]);
    $profile = Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'student_number' => 'D096001',
    ]);

    $this->actingAs($bendahara)
        ->get(route('finance.index'))
        ->assertOk()
        ->assertSee('Pembayaran & Donasi')
        ->assertSee('Ade Chandra')
        ->assertSee('D096001');
});

test('alumni users can view finance status without donation amount', function () {
    $profile = Alumni::factory()->create(['full_name' => 'Ade Chandra']);

    Payment::factory()->create([
        'alumni_id' => $profile->id,
        'amount' => 1000000,
        'status' => 'paid',
        'payment_date' => '2026-06-06',
        'verified_by' => User::factory()->create()->id,
        'verified_at' => '2026-06-06 09:00:00',
    ]);
    Donation::factory()->create([
        'alumni_id' => $profile->id,
        'amount' => 5000000,
        'publication_status' => 'anonymous',
        'notes' => 'Titip anonim.',
    ]);

    $this->actingAs($profile->user)
        ->get(route('alumni.finance'))
        ->assertOk()
        ->assertSee('Status Pembayaran')
        ->assertSee('Lunas')
        ->assertSee('Donatur Anonim')
        ->assertSee('Titip anonim.')
        ->assertDontSee('5000000')
        ->assertDontSee('5.000.000');
});

test('bendahara users can save payment and donation records', function () {
    $bendaharaRole = Role::factory()->create([
        'name' => 'bendahara',
        'description' => 'Bendahara reuni',
    ]);
    $bendahara = User::factory()->create(['role_id' => $bendaharaRole->id]);
    $profile = Alumni::factory()->create(['full_name' => 'Ade Chandra']);

    $this->actingAs($bendahara);

    Livewire::test('pages::finance.index')
        ->call('selectAlumni', $profile->id)
        ->set('payment_status', 'paid')
        ->set('payment_amount', 1000000)
        ->set('payment_date', '2026-06-06')
        ->set('payment_notes', 'Transfer lunas.')
        ->call('savePayment')
        ->assertHasNoErrors()
        ->set('has_donation', true)
        ->set('donation_amount', 2500000)
        ->set('donation_publication_status', 'anonymous')
        ->set('donation_notes', 'Donasi tambahan.')
        ->call('saveDonation')
        ->assertHasNoErrors();

    $payment = Payment::query()->where('alumni_id', $profile->id)->firstOrFail();
    $donation = Donation::query()->where('alumni_id', $profile->id)->firstOrFail();

    expect($payment->status)->toBe('paid');
    expect($payment->amount)->toBe('1000000.00');
    expect($payment->verified_by)->toBe($bendahara->id);
    expect($payment->verified_at)->not->toBeNull();
    expect($donation->amount)->toBe('2500000.00');
    expect($donation->publication_status)->toBe('anonymous');
    expect($donation->managed_by)->toBe($bendahara->id);
});

test('bendahara users can remove donation records', function () {
    $bendaharaRole = Role::factory()->create([
        'name' => 'bendahara',
        'description' => 'Bendahara reuni',
    ]);
    $bendahara = User::factory()->create(['role_id' => $bendaharaRole->id]);
    $profile = Alumni::factory()->create();

    Donation::factory()->create([
        'alumni_id' => $profile->id,
        'amount' => 500000,
    ]);

    $this->actingAs($bendahara);

    Livewire::test('pages::finance.index')
        ->call('selectAlumni', $profile->id)
        ->set('has_donation', false)
        ->call('saveDonation')
        ->assertHasNoErrors();

    expect(Donation::query()->where('alumni_id', $profile->id)->exists())->toBeFalse();
});
