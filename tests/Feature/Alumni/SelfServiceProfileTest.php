<?php

use App\Models\Alumni;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('guests are redirected from alumni profile page', function () {
    $this->get(route('alumni.profile'))
        ->assertRedirect(route('login'));
});

test('users without alumni profile cannot access alumni profile page', function () {
    $role = Role::factory()->create([
        'name' => 'administrator',
        'description' => 'Administrator sistem',
    ]);

    $user = User::factory()->create(['role_id' => $role->id]);

    $this->actingAs($user)
        ->get(route('alumni.profile'))
        ->assertForbidden();
});

test('alumni users can view their self service profile page', function () {
    $profile = Alumni::factory()->create([
        'full_name' => 'Ade Chandra',
        'rsvp_status' => 'pending',
    ]);

    $this->actingAs($profile->user)
        ->get(route('alumni.profile'))
        ->assertOk()
        ->assertSee('Profil Alumni')
        ->assertSee('Ade Chandra')
        ->assertSee('Simpan Profil');
});

test('alumni users can update their own profile and rsvp', function () {
    $profile = Alumni::factory()->create([
        'full_name' => 'Nama Lama',
        'student_number' => 'D096010',
        'email' => 'lama@example.test',
        'rsvp_status' => 'pending',
        'is_profile_completed' => false,
    ]);

    $this->actingAs($profile->user);

    Livewire::test('pages::alumni.profile')
        ->set('full_name', 'Nama Baru')
        ->set('nickname', 'Baru')
        ->set('student_number', 'D096777')
        ->set('email', 'baru@example.test')
        ->set('whatsapp_number', '+62 812-3333-4444')
        ->set('rsvp_status', 'attending')
        ->set('company', 'PT Titik Nol')
        ->set('job_title', 'Surveyor')
        ->set('location_search', 'Makassar, Sulawesi Selatan, Indonesia')
        ->set('country', 'Indonesia')
        ->set('city', 'Makassar')
        ->set('latitude', -5.147665)
        ->set('longitude', 119.432732)
        ->set('short_story', 'Sekarang tinggal di Makassar.')
        ->set('memorable_story', 'Kenangan praktikum lapangan.')
        ->set('message_to_friends', 'Sampai jumpa di reuni.')
        ->call('updateProfile')
        ->assertHasNoErrors();

    $profile->refresh();
    $profile->user->refresh();

    expect($profile->full_name)->toBe('Nama Baru');
    expect($profile->student_number)->toBe('D096777');
    expect($profile->rsvp_status)->toBe('attending');
    expect($profile->country)->toBe('Indonesia');
    expect($profile->city)->toBe('Makassar');
    expect((string) $profile->latitude)->toBe('-5.1476650');
    expect((string) $profile->longitude)->toBe('119.4327320');
    expect($profile->short_story)->toBe('Sekarang tinggal di Makassar.');
    expect($profile->is_profile_completed)->toBeTrue();
    expect($profile->user->name)->toBe('Nama Baru');
    expect($profile->user->whatsapp_number)->toBe('6281233334444');
    expect($profile->user->email)->toBe('baru@example.test');
});

test('alumni users can upload memory book photos from their profile', function () {
    Storage::fake('public');

    $profile = Alumni::factory()->create();
    $collegePhoto = UploadedFile::fake()->image('kuliah.jpg', 800, 1000)->size(512);
    $currentPhoto = UploadedFile::fake()->image('sekarang.webp', 800, 1000)->size(512);

    $this->actingAs($profile->user);

    Livewire::test('pages::alumni.profile')
        ->set('college_photo', $collegePhoto)
        ->set('current_photo', $currentPhoto)
        ->call('updateMemoryBookPhotos')
        ->assertHasNoErrors();

    $profile->refresh();

    expect($profile->college_photo_path)->not->toBeNull();
    expect($profile->current_photo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($profile->college_photo_path);
    Storage::disk('public')->assertExists($profile->current_photo_path);
});

test('replacing a memory book photo deletes the previous managed file', function () {
    Storage::fake('public');
    Storage::disk('public')->put('alumni/memory-book/college/old.jpg', 'old photo');

    $profile = Alumni::factory()->create([
        'college_photo_path' => 'alumni/memory-book/college/old.jpg',
    ]);

    $this->actingAs($profile->user);

    Livewire::test('pages::alumni.profile')
        ->set('college_photo', UploadedFile::fake()->image('baru.jpg', 800, 1000))
        ->call('updateMemoryBookPhotos')
        ->assertHasNoErrors();

    $profile->refresh();

    Storage::disk('public')->assertMissing('alumni/memory-book/college/old.jpg');
    Storage::disk('public')->assertExists($profile->college_photo_path);
});

test('alumni profile update validates unique identifiers', function () {
    $existing = Alumni::factory()->create([
        'student_number' => 'D096123',
    ]);

    $profile = Alumni::factory()->create([
        'student_number' => 'D096124',
    ]);

    $this->actingAs($profile->user);

    Livewire::test('pages::alumni.profile')
        ->set('student_number', 'D096123')
        ->set('whatsapp_number', $existing->user->whatsapp_number)
        ->call('updateProfile')
        ->assertHasErrors([
            'student_number' => 'unique',
            'whatsapp_number' => 'unique',
        ]);
});

test('alumni profile requires a city suggestion when location text is entered', function () {
    $profile = Alumni::factory()->create([
        'country' => null,
        'city' => null,
        'latitude' => null,
        'longitude' => null,
    ]);

    $this->actingAs($profile->user);

    Livewire::test('pages::alumni.profile')
        ->set('location_search', 'Kuala Lumpur')
        ->call('updateProfile')
        ->assertHasErrors([
            'city' => 'required_with',
            'country' => 'required_with',
            'latitude' => 'required_with',
            'longitude' => 'required_with',
        ]);

    $profile->refresh();

    expect($profile->country)->toBeNull();
    expect($profile->city)->toBeNull();
});
