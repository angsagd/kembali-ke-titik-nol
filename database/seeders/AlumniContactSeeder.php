<?php

namespace Database\Seeders;

use App\Models\Alumni;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AlumniContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $alumniRole = Role::query()->firstOrCreate(
            ['name' => 'alumni'],
            ['description' => 'Anggota alumni'],
        );

        $contacts = json_decode(
            file_get_contents(base_path('specification/contacts.json')),
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );

        collect($contacts)->each(function (array $contact) use ($alumniRole): void {
            $whatsappNumber = User::normalizeWhatsappNumber((string) $contact['wanumber']);
            $password = 'tgd'.substr($whatsappNumber, -4);

            $user = User::query()->updateOrCreate(
                ['whatsapp_number' => $whatsappNumber],
                [
                    'role_id' => $alumniRole->id,
                    'name' => $contact['name'],
                    'email' => "{$whatsappNumber}@geodesi96.local",
                    'password' => $password,
                    'is_active' => true,
                ],
            );

            Alumni::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'student_number' => $contact['nim'] ?? $contact['student_number'] ?? null,
                    'full_name' => $contact['name'],
                    'email' => $user->email,
                    'alumni_status' => 'active',
                    'rsvp_status' => 'pending',
                ],
            );
        });
    }
}
