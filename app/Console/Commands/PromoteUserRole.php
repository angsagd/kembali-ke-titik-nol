<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('user:promote-role {whatsapp_number : Nomor WhatsApp user} {role=administrator : Role target}')]
#[Description('Promosikan user existing ke role tertentu berdasarkan nomor WhatsApp.')]
class PromoteUserRole extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $whatsappNumber = User::normalizeWhatsappNumber((string) $this->argument('whatsapp_number'));
        $roleName = (string) $this->argument('role');

        $role = Role::query()->where('name', $roleName)->first();

        if (! $role) {
            $this->error("Role [{$roleName}] tidak ditemukan.");

            return self::FAILURE;
        }

        $user = User::query()->where('whatsapp_number', $whatsappNumber)->first();

        if (! $user) {
            $this->error("User dengan WhatsApp [{$whatsappNumber}] tidak ditemukan.");

            return self::FAILURE;
        }

        $user->forceFill(['role_id' => $role->id])->save();

        $this->info("User [{$user->name}] dipromosikan menjadi [{$role->name}].");

        return self::SUCCESS;
    }
}
