<?php

use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Profile settings')] class extends Component {
    use ProfileValidationRules;

    public string $whatsapp_number = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->whatsapp_number = Auth::user()->whatsapp_number;
    }

    /**
     * Update the WhatsApp number for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();
        $this->whatsapp_number = User::normalizeWhatsappNumber($this->whatsapp_number);

        $validated = $this->validate([
            'whatsapp_number' => $this->whatsappNumberRules($user->id),
        ]);

        $user->forceFill($validated)->save();

        Flux::toast(variant: 'success', text: __('Nomor WhatsApp diperbarui.'));
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Pengaturan Akun')" :subheading="__('Ubah nomor WhatsApp yang digunakan untuk login.')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="whatsapp_number" :label="__('Nomor WhatsApp')" type="tel" required autocomplete="tel" />

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full" data-test="update-profile-button">
                        {{ __('Save') }}
                    </flux:button>
                </div>

            </div>
        </form>

    </x-pages::settings.layout>
</section>
