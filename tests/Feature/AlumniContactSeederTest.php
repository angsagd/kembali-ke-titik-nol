<?php

use App\Models\Alumni;
use App\Models\User;
use Database\Seeders\AlumniContactSeeder;
use Illuminate\Support\Facades\Schema;

test('alumni contact seeder creates users and alumni profiles', function () {
    $contacts = json_decode(
        file_get_contents(base_path('specification/contacts.json')),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    $this->seed(AlumniContactSeeder::class);

    expect(User::query()->count())->toBe(count($contacts));
    expect(Alumni::query()->count())->toBe(count($contacts));
    expect(Schema::hasColumn('alumni', 'student_number'))->toBeTrue();

    $firstContact = $contacts[0];
    $whatsappNumber = preg_replace('/[^0-9+]/', '', $firstContact['wanumber']);
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
