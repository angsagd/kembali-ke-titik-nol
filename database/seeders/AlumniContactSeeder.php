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

        $contactsPath = (string) config('kembali-ke-titik-nol.contacts_path');

        $contacts = json_decode(
            file_get_contents($contactsPath),
            associative: true,
            flags: JSON_THROW_ON_ERROR,
        );

        collect($contacts)->each(function (array $contact) use ($alumniRole): void {
            $whatsappNumber = User::normalizeWhatsappNumber((string) $contact['wanumber']);

            if (! $whatsappNumber) {
                return;
            }

            $password = 'tgd'.substr($whatsappNumber, -4);

            $user = User::query()->firstOrNew(['whatsapp_number' => $whatsappNumber]);

            if ($user->exists) {
                $user->loadMissing('role');

                if (in_array($user->role?->name, ['superadmin', 'administrator', 'bendahara'], true)) {
                    return;
                }
            }

            $user->fill([
                'role_id' => $alumniRole->id,
                'name' => $contact['name'],
                'email' => "{$whatsappNumber}@geodesi96.local",
                'is_active' => true,
            ]);

            if (! $user->exists) {
                $user->password = $password;
            }

            $user->save();

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
