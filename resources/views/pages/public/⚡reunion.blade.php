<?php

use App\Models\EventScheduleItem;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::public')] #[Title('Reuni 30 Tahun | Geodesi 96')] class extends Component {
    /**
     * @return Collection<int, EventScheduleItem>
     */
    #[Computed]
    public function scheduleItems(): Collection
    {
        return EventScheduleItem::query()
            ->orderByRaw("case event_day when 'day_one' then 1 else 2 end")
            ->orderBy('start_time')
            ->orderBy('id')
            ->get();
    }
}; ?>

<main class="min-h-screen bg-ktn-topo">
    <x-public-header active="reunion" />

    <section class="relative overflow-hidden px-4 pb-16 pt-28 sm:px-6 sm:pb-20 sm:pt-32 lg:px-8">
        <div class="hero-kontur absolute inset-0 opacity-25 mix-blend-multiply"></div>
        <div class="absolute inset-0 bg-ktn-surface/65"></div>

        <div class="relative mx-auto grid max-w-7xl items-center gap-10 lg:grid-cols-[1fr_24rem]">
            <div class="text-center lg:text-left">
                <span class="inline-flex rounded-full bg-ktn-sage/15 px-4 py-1.5 font-mono text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-ktn-forest">
                    {{ __('Arsip perjalanan pulang') }}
                </span>
                <p class="mt-6 font-mono text-xs font-semibold uppercase tracking-[0.24em] text-ktn-forest">{{ __('Reuni 30 Tahun Geodesi UGM') }}</p>
                <h1 class="mt-3 font-display text-4xl font-extrabold tracking-tight text-ktn-forest sm:text-5xl lg:text-6xl">
                    {{ __('Kembali ke Titik Nol') }}
                </h1>
                <p class="mx-auto mt-6 max-w-2xl text-base leading-8 text-ktn-muted sm:text-lg lg:mx-0">
                    {{ __('Perjumpaan 23–24 Agustus 2026 telah menjadi bagian dari perjalanan Geodesi 96. Halaman ini menyimpan informasi acara, rangkaian kegiatan, dan jalan menuju dokumentasinya.') }}
                </p>

                <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row lg:justify-start">
                    <flux:button variant="primary" icon="photo" :href="route('public.gallery')" wire:navigate>
                        {{ __('Lihat Dokumentasi Publik') }}
                    </flux:button>

                    @auth
                        @can('update-own-alumni-profile')
                            <flux:button variant="ghost" icon="calendar-days" :href="route('reunion.index')" wire:navigate>
                                {{ __('Buka Ringkasan Internal') }}
                            </flux:button>
                        @endcan
                    @else
                        <flux:button variant="ghost" icon="arrow-right-end-on-rectangle" :href="route('login')">
                            {{ __('Login Alumni') }}
                        </flux:button>
                    @endauth
                </div>
            </div>

            <div class="mx-auto w-full max-w-sm rounded-2xl border border-ktn-sage/20 bg-white p-6 shadow-xl shadow-ktn-forest/10 lg:mx-0">
                <img
                    src="{{ asset('images/brand/sticker-kembali-ke-titik-nol.png') }}"
                    alt="Logo Kembali ke Titik Nol"
                    class="mx-auto size-48 object-contain"
                >
                <dl class="mt-6 grid gap-4 border-t border-ktn-sage/20 pt-5 text-sm">
                    <div>
                        <dt class="font-mono text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-ktn-muted">{{ __('Tanggal') }}</dt>
                        <dd class="mt-1 font-display text-lg font-bold text-ktn-forest">23–24 Agustus 2026</dd>
                    </div>
                    <div>
                        <dt class="font-mono text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-ktn-muted">{{ __('Konteks') }}</dt>
                        <dd class="mt-1 font-medium text-ktn-ink">{{ __('Special-purpose module Portal Alumni') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </section>

    <section class="bg-white px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="max-w-3xl">
                <p class="font-mono text-xs font-semibold uppercase tracking-[0.22em] text-ktn-forest">{{ __('Lokasi') }}</p>
                <h2 class="mt-3 font-display text-3xl font-bold text-ktn-forest sm:text-4xl">{{ __('Dua hari, dua titik perjumpaan') }}</h2>
            </div>

            <div class="mt-9 grid gap-5 md:grid-cols-2">
                <article class="rounded-xl border border-ktn-sage/20 bg-ktn-surface p-6">
                    <span class="font-mono text-xs font-semibold uppercase tracking-[0.18em] text-ktn-sage">{{ __('Hari Pertama') }}</span>
                    <h3 class="mt-3 font-display text-xl font-bold text-ktn-forest">{{ __('Penginapan Joglo / Kampung Wisata Tembi') }}</h3>
                    <p class="mt-2 leading-7 text-ktn-muted">{{ __('Minggu, 23 Agustus 2026 · ruang untuk pulang, menyapa, dan merayakan tiga dekade paseduluran.') }}</p>
                </article>

                <article class="rounded-xl border border-ktn-sage/20 bg-ktn-surface p-6">
                    <span class="font-mono text-xs font-semibold uppercase tracking-[0.18em] text-ktn-sage">{{ __('Hari Kedua') }}</span>
                    <h3 class="mt-3 font-display text-xl font-bold text-ktn-forest">{{ __('Departemen Teknik Geodesi UGM') }}</h3>
                    <p class="mt-2 leading-7 text-ktn-muted">{{ __('Senin, 24 Agustus 2026 · kembali ke kampus dan titik awal perjalanan Geodesi 96.') }}</p>
                </article>
            </div>
        </div>
    </section>

    <section class="px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="max-w-3xl">
                <p class="font-mono text-xs font-semibold uppercase tracking-[0.22em] text-ktn-forest">{{ __('Rundown Historis') }}</p>
                <h2 class="mt-3 font-display text-3xl font-bold text-ktn-forest sm:text-4xl">{{ __('Rangkaian Acara') }}</h2>
                <p class="mt-3 leading-7 text-ktn-muted">{{ __('Agenda yang dikelola panitia tetap menjadi sumber tunggal untuk halaman reuni.') }}</p>
            </div>

            <div class="mt-9">
                <x-reunion-schedule :schedule-items="$this->scheduleItems" />
            </div>
        </div>
    </section>

    <section class="bg-ktn-forest px-4 py-14 text-white sm:px-6 lg:px-8">
        <div class="mx-auto flex max-w-7xl flex-col gap-6 text-center lg:flex-row lg:items-center lg:justify-between lg:text-left">
            <div>
                <p class="font-mono text-xs font-semibold uppercase tracking-[0.2em] text-ktn-sage-light">{{ __('Arsip Reuni 30 Tahun') }}</p>
                <h2 class="mt-3 font-display text-3xl font-bold">{{ __('Cerita berlanjut dalam dokumentasi Geodesi 96') }}</h2>
            </div>
            <flux:button variant="primary" icon="photo" :href="route('public.gallery')" wire:navigate>
                {{ __('Jelajahi Dokumentasi') }}
            </flux:button>
        </div>
    </section>
</main>
