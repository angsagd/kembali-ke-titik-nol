<?php

use App\Models\Alumni;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Profil Alumni')] class extends Component {
    public Alumni $alumni;

    public function mount(Alumni $alumni): void
    {
        $this->alumni = $alumni->load('user');
    }

    #[Computed]
    public function timelines(): Collection
    {
        return $this->alumni
            ->timelines()
            ->get();
    }

    public function monthName(?int $month): ?string
    {
        if ($month === null) {
            return null;
        }

        return [
            1 => __('Januari'),
            2 => __('Februari'),
            3 => __('Maret'),
            4 => __('April'),
            5 => __('Mei'),
            6 => __('Juni'),
            7 => __('Juli'),
            8 => __('Agustus'),
            9 => __('September'),
            10 => __('Oktober'),
            11 => __('November'),
            12 => __('Desember'),
        ][$month] ?? null;
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

            <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <flux:heading size="lg">{{ __('Timeline Lokasi') }}</flux:heading>
                        <flux:text>{{ __('Riwayat lokasi yang dibagikan alumni dari tahun ke tahun.') }}</flux:text>
                    </div>

                    <flux:badge>{{ $this->timelines->count() }}</flux:badge>
                </div>

                <div class="mt-5 grid gap-4">
                    @forelse ($this->timelines as $timeline)
                        <article wire:key="directory-timeline-{{ $timeline->id }}" class="rounded-md border border-zinc-200 p-4 dark:border-zinc-700">
                            <div class="font-semibold">
                                {{ $timeline->month ? $this->monthName($timeline->month).' ' : '' }}{{ $timeline->year }}
                            </div>
                            <div class="text-sm text-zinc-600 dark:text-zinc-300">
                                {{ collect([$timeline->city, $timeline->country])->filter()->join(', ') ?: __('Lokasi belum diisi') }}
                            </div>

                            @if ($timeline->notes)
                                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $timeline->notes }}</p>
                            @endif
                        </article>
                    @empty
                        <div class="rounded-md border border-dashed border-zinc-300 p-6 text-center dark:border-zinc-700">
                            <flux:text>{{ __('Belum ada riwayat lokasi.') }}</flux:text>
                        </div>
                    @endforelse
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
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Kota domisili') }}</dt>
                        <dd class="font-medium">{{ $alumni->city ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Negara domisili') }}</dt>
                        <dd class="font-medium">{{ $alumni->country ?: '-' }}</dd>
                    </div>
                </dl>
            </div>
        </aside>
    </div>
</section>
