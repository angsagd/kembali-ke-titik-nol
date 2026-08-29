<?php

use App\Models\Alumni;
use App\Models\EventScheduleItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Reuni 30 Tahun | Geodesi 96')] class extends Component {
    #[Computed]
    public function alumni(): Alumni
    {
        return Auth::user()->alumni()
            ->with(['payment', 'donation', 'roomAssignment.room'])
            ->firstOrFail();
    }

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

    public function rsvpStatusLabel(?string $status): string
    {
        return match ($status) {
            'attending' => __('Hadir'),
            'not_attending' => __('Tidak Hadir'),
            default => __('Belum Merespon'),
        };
    }

    public function rsvpStatusColor(?string $status): string
    {
        return match ($status) {
            'attending' => 'green',
            'not_attending' => 'red',
            default => 'amber',
        };
    }

    public function paymentStatusLabel(?string $status): string
    {
        return match ($status) {
            'paid' => __('Lunas'),
            'pending_verification' => __('Menunggu Verifikasi'),
            default => __('Belum Bayar'),
        };
    }

    public function paymentStatusColor(?string $status): string
    {
        return match ($status) {
            'paid' => 'green',
            'pending_verification' => 'amber',
            default => 'zinc',
        };
    }

    public function donationStatusLabel(?string $status): string
    {
        return match ($status) {
            'anonymous' => __('Donatur Anonim'),
            'show_name' => __('Nama Ditampilkan'),
            default => __('Belum Ada'),
        };
    }
}; ?>

<section class="w-full space-y-8 p-6 lg:p-8">
    <div class="overflow-hidden rounded-2xl bg-ktn-forest text-white">
        <div class="topo-grid relative grid items-center gap-8 p-6 sm:p-8 lg:grid-cols-[1fr_16rem] lg:p-10">
            <div>
                <p class="font-mono text-xs font-semibold uppercase tracking-[0.22em] text-ktn-sage-light">{{ __('Reuni 30 Tahun Geodesi UGM') }}</p>
                <flux:heading size="xl" class="mt-3 text-white!">{{ __('Kembali ke Titik Nol') }}</flux:heading>
                <flux:text class="mt-4 max-w-3xl text-white/75!">
                    {{ __('Ringkasan acara dan arsip reuni 23–24 Agustus 2026. Seluruh status di bawah menggunakan data operasional yang sudah ada, bukan salinan baru.') }}
                </flux:text>

                <div class="mt-6 flex flex-wrap gap-3">
                    <flux:button variant="primary" icon="arrow-left" :href="route('public.reunion')" wire:navigate>
                        {{ __('Halaman Publik Reuni') }}
                    </flux:button>
                    <flux:button variant="ghost" icon="photo" :href="route('public.gallery')" wire:navigate class="text-white! hover:bg-white/10!">
                        {{ __('Dokumentasi Publik') }}
                    </flux:button>
                </div>
            </div>

            <img
                src="{{ asset('images/brand/sticker-kembali-ke-titik-nol.png') }}"
                alt="Logo Kembali ke Titik Nol"
                class="mx-auto size-44 rounded-xl bg-white/95 object-contain p-3 shadow-lg"
            >
        </div>
    </div>

    <div>
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <flux:heading size="lg">{{ __('Status Reuni Saya') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Ringkasan historis untuk :name.', ['name' => $this->alumni->nickname ?: $this->alumni->full_name]) }}</flux:text>
            </div>
            <flux:badge color="zinc">{{ __('Arsip 2026') }}</flux:badge>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <flux:card class="space-y-3">
                <flux:text>{{ __('RSVP Saya') }}</flux:text>
                <flux:badge color="{{ $this->rsvpStatusColor($this->alumni->rsvp_status) }}">
                    {{ $this->rsvpStatusLabel($this->alumni->rsvp_status) }}
                </flux:badge>
                <flux:button variant="ghost" size="sm" :href="route('alumni.rsvp')" wire:navigate>{{ __('Buka RSVP') }}</flux:button>
            </flux:card>

            <flux:card class="space-y-3">
                <flux:text>{{ __('Status Pembayaran Saya') }}</flux:text>
                <flux:badge color="{{ $this->paymentStatusColor($this->alumni->payment?->status) }}">
                    {{ $this->paymentStatusLabel($this->alumni->payment?->status) }}
                </flux:badge>
                <flux:button variant="ghost" size="sm" :href="route('alumni.finance')" wire:navigate>{{ __('Pembayaran & Donasi') }}</flux:button>
            </flux:card>

            <flux:card class="space-y-3">
                <flux:text>{{ __('Donasi') }}</flux:text>
                <flux:badge color="{{ $this->alumni->donation ? 'green' : 'zinc' }}">
                    {{ $this->donationStatusLabel($this->alumni->donation?->publication_status) }}
                </flux:badge>
                <flux:button variant="ghost" size="sm" :href="route('alumni.finance')" wire:navigate>{{ __('Lihat Catatan') }}</flux:button>
            </flux:card>

            <flux:card class="space-y-3">
                <flux:text>{{ __('Kamar Saya') }}</flux:text>
                <div class="font-medium">{{ $this->alumni->roomAssignment?->room?->room_name ?: __('Belum Ditentukan') }}</div>
                <flux:button variant="ghost" size="sm" :href="route('alumni.room')" wire:navigate>{{ __('Lihat Kamar') }}</flux:button>
            </flux:card>
        </div>

        <div class="mt-4">
            <flux:button variant="ghost" icon="photo" :href="route('documentation.index')" wire:navigate>
                {{ __('Dokumentasi Reuni dalam Arsip') }}
            </flux:button>
        </div>
    </div>

    <div>
        <div class="max-w-3xl">
            <flux:heading size="lg">{{ __('Rangkaian Acara') }}</flux:heading>
            <flux:text class="mt-2">{{ __('Rundown historis menggunakan data yang sama dengan halaman publik dan administrasi panitia.') }}</flux:text>
        </div>

        <div class="mt-5">
            <x-reunion-schedule :schedule-items="$this->scheduleItems" />
        </div>
    </div>
</section>
