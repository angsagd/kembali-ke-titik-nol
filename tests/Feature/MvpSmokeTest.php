<?php

use App\Models\Alumni;
use App\Models\City;
use App\Models\Country;
use App\Models\MediaItem;
use App\Models\Role;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('mvp alumni journey works from login through profile rsvp room finance and documentation', function () {
    Storage::fake('public');

    $alumniRole = Role::factory()->create(['name' => 'alumni']);
    $administratorRole = Role::factory()->create(['name' => 'administrator']);
    $bendaharaRole = Role::factory()->create(['name' => 'bendahara']);
    $country = Country::factory()->create(['name' => 'Indonesia']);
    $city = City::factory()->create([
        'country_id' => $country->id,
        'name' => 'Yogyakarta',
    ]);
    $user = User::factory()->create([
        'role_id' => $alumniRole->id,
        'name' => 'Ade Chandra',
        'whatsapp_number' => '6281234567890',
        'password' => Hash::make('secret1234'),
    ]);
    $profile = Alumni::factory()->create([
        'user_id' => $user->id,
        'full_name' => 'Ade Chandra',
        'student_number' => 'D096001',
        'rsvp_status' => 'pending',
    ]);
    $administrator = User::factory()->create(['role_id' => $administratorRole->id]);
    $bendahara = User::factory()->create(['role_id' => $bendaharaRole->id]);

    $this->post(route('login'), [
        'whatsapp_number' => '6281234567890',
        'password' => 'secret1234',
    ])->assertRedirect(route('dashboard'));

    $this->actingAs($user);

    Livewire::test('pages::alumni.profile')
        ->set('nickname', 'Ade')
        ->set('email', 'ade@example.test')
        ->set('company', 'Geo Nusantara')
        ->set('job_title', 'Survey Manager')
        ->set('current_country_id', $country->id)
        ->set('current_city_id', $city->id)
        ->set('short_story', 'Kembali menyapa teman-teman lama.')
        ->set('message_to_friends', 'Sampai jumpa di reuni.')
        ->call('updateProfile')
        ->assertHasNoErrors();

    expect($profile->refresh()->is_profile_completed)->toBeTrue();

    Livewire::test('pages::alumni.rsvp')
        ->set('rsvp_status', 'attending')
        ->call('saveRsvp')
        ->assertHasNoErrors();

    expect($profile->refresh()->rsvp_status)->toBe('attending');

    $this->actingAs($bendahara);

    Livewire::test('pages::finance.index')
        ->call('selectAlumni', $profile->id)
        ->set('payment_status', 'paid')
        ->set('payment_amount', 1000000)
        ->set('payment_date', '2026-08-01')
        ->set('payment_notes', 'Transfer lunas.')
        ->call('savePayment')
        ->assertHasNoErrors()
        ->set('has_donation', true)
        ->set('donation_amount', 2500000)
        ->set('donation_publication_status', 'anonymous')
        ->set('donation_notes', 'Donasi anonim.')
        ->call('saveDonation')
        ->assertHasNoErrors();

    expect($profile->payment()->first()?->status)->toBe('paid')
        ->and($profile->donation()->exists())->toBeTrue();

    $this->actingAs($administrator);

    Livewire::test('pages::admin.rooming.index')
        ->call('newRoom')
        ->set('room_name', 'Kamar 01')
        ->set('room_type', 'Twin Share')
        ->set('capacity', 2)
        ->call('saveRoom')
        ->assertHasNoErrors();

    $room = Room::query()->where('room_name', 'Kamar 01')->firstOrFail();

    Livewire::test('pages::admin.rooming.index')
        ->call('selectRoom', $room->id)
        ->set('assignment_alumni_id', $profile->id)
        ->set('assignment_notes', 'Dekat pintu.')
        ->call('assignAlumni')
        ->assertHasNoErrors();

    expect(RoomAssignment::query()->where('alumni_id', $profile->id)->exists())->toBeTrue();

    $this->actingAs($user);

    Livewire::test('pages::documentation.index')
        ->set('type', 'photo')
        ->set('photo', UploadedFile::fake()->image('reuni.jpg', 1200, 800)->size(256))
        ->set('title', 'Foto UAT Reuni')
        ->set('description', 'Dokumentasi UAT.')
        ->set('year', 2026)
        ->set('visibility', 'internal')
        ->call('saveMedia')
        ->assertHasNoErrors();

    $mediaItem = MediaItem::query()->where('title', 'Foto UAT Reuni')->firstOrFail();
    Storage::disk('public')->assertExists($mediaItem->file_path);

    $this->get(route('documentation.index'))
        ->assertOk()
        ->assertSee('Foto UAT Reuni');

    $this->get(route('alumni.finance'))
        ->assertOk()
        ->assertSee('Lunas')
        ->assertDontSee('2500000');

    $this->get(route('alumni.room'))
        ->assertOk()
        ->assertSee('Kamar 01');

    $this->get(route('home'))->assertOk();
});
