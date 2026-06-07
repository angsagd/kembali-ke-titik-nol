<?php

use App\Models\Alumni;
use App\Models\User;
use Database\Seeders\AlumniContactSeeder;
use Illuminate\Support\Facades\Schema;

test('alumni contact seeder creates users and alumni profiles', function () {
    $contacts = [
        [
            'name' => 'Ade Chandra',
            'wanumber' => '+62 812-3456-7890',
            'nim' => 'D096001',
        ],
        [
            'name' => 'Budi Santoso',
            'wanumber' => '6281234567891',
            'nim' => 'D096002',
        ],
    ];

    $contactsPath = storage_path('framework/testing/contacts.json');
    file_put_contents($contactsPath, json_encode($contacts, JSON_THROW_ON_ERROR));
    config()->set('kembali-ke-titik-nol.contacts_path', $contactsPath);

    $this->seed(AlumniContactSeeder::class);

    expect(User::query()->count())->toBe(count($contacts));
    expect(Alumni::query()->count())->toBe(count($contacts));
    expect(Schema::hasColumn('alumni', 'student_number'))->toBeTrue();

    $firstContact = $contacts[0];
    $whatsappNumber = User::normalizeWhatsappNumber($firstContact['wanumber']);
    $password = 'tgd'.substr($whatsappNumber, -4);

    $response = $this->post(route('login.store'), [
        'whatsapp_number' => $whatsappNumber,
        'password' => $password,
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();

    $user = User::query()->where('whatsapp_number', $whatsappNumber)->firstOrFail();

    expect($user->role->name)->toBe('alumni');
    expect($user->alumni)->not->toBeNull();
    expect($user->alumni->full_name)->toBe($firstContact['name']);
});
