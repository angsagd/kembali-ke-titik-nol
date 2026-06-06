<?php

use App\Models\Alumni;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Profil Alumni')] class extends Component {
    public Alumni $alumni;

    public function mount(Alumni $alumni): void
    {
        $this->alumni = $alumni->load(['user']);
    }
}; ?>

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-2">
            <flux:button variant="ghost" icon="arrow-left" :href="route('alumni.directory.index')" wire:navigate>
                {{ __('Kembali ke Direktori') }}
            </flux:button>
            <flux:heading size="xl">{{ $alumni->full_name }}</flux:heading>
            <flux:text>
                {{ $alumni->nickname ? __('Biasa dipanggil :nickname', ['nickname' => $alumni->nickname]) : __('Profil alumni Teknik Geodesi UGM 1996') }}
            </flux:text>
        </div>

        <div class="flex flex-wrap gap-2">
            <flux:badge color="{{ $alumni->alumni_status === 'active' ? 'green' : 'zinc' }}">
                {{ $alumni->alumni_status === 'active' ? __('Aktif') : __('Memorial') }}
            </flux:badge>
            <flux:badge color="{{ $alumni->is_profile_completed ? 'green' : 'amber' }}">
                {{ $alumni->is_profile_completed ? __('Profil Lengkap') : __('Profil Awal') }}
            </flux:badge>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_22rem]">
        <div class="space-y-6">
            <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg">{{ __('Cerita Alumni') }}</flux:heading>

                <div class="mt-5 grid gap-5">
                    <div>
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Cerita singkat saat ini') }}</h3>
                        <p class="mt-1 whitespace-pre-line">{{ $alumni->short_story ?: __('Belum diisi.') }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Kenangan masa kuliah') }}</h3>
                        <p class="mt-1 whitespace-pre-line">{{ $alumni->memorable_story ?: __('Belum diisi.') }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Pesan untuk teman alumni') }}</h3>
                        <p class="mt-1 whitespace-pre-line">{{ $alumni->message_to_friends ?: __('Belum diisi.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg">{{ __('Informasi') }}</flux:heading>

                <dl class="mt-5 grid gap-4">
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Nama lengkap') }}</dt>
                        <dd class="font-medium">{{ $alumni->full_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('NIM') }}</dt>
                        <dd class="font-medium">{{ $alumni->student_number ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Email') }}</dt>
                        <dd class="font-medium">{{ $alumni->email ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('WhatsApp') }}</dt>
                        <dd class="font-medium">{{ $alumni->user?->whatsapp_number ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Instansi / Perusahaan') }}</dt>
                        <dd class="font-medium">{{ $alumni->company ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Jabatan / Pekerjaan') }}</dt>
                        <dd class="font-medium">{{ $alumni->job_title ?: '-' }}</dd>
                    </div>
                </dl>
            </div>
        </aside>
    </div>
</section>
