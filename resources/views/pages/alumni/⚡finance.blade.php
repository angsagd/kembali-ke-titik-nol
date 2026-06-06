<?php

use App\Models\Alumni;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Pembayaran')] class extends Component {
    public Alumni $alumni;

    public function mount(): void
    {
        $this->alumni = Auth::user()->alumni()
            ->with(['payment.verifier', 'donation.manager'])
            ->firstOrFail();
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

    public function donationPublicationLabel(?string $status): string
    {
        return $status === 'anonymous'
            ? __('Donatur Anonim')
            : __('Tampilkan Nama Saya');
    }
}; ?>

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="space-y-2">
            <flux:heading size="xl">{{ __('Pembayaran & Donasi') }}</flux:heading>
            <flux:text class="max-w-3xl">
                {{ __('Pantau status pembayaran kontribusi reuni dan status publikasi donasi Anda.') }}
            </flux:text>
        </div>

        <flux:button variant="ghost" icon="identification" :href="route('alumni.profile')" wire:navigate>
            {{ __('Profil Saya') }}
        </flux:button>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <flux:heading size="lg">{{ __('Status Pembayaran') }}</flux:heading>
                    <flux:text class="mt-2">{{ __('Pembayaran dicatat dan diverifikasi manual oleh bendahara.') }}</flux:text>
                </div>

                <flux:badge color="{{ $this->paymentStatusColor($alumni->payment?->status) }}">
                    {{ $this->paymentStatusLabel($alumni->payment?->status) }}
                </flux:badge>
            </div>

            <dl class="mt-6 grid gap-4">
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Tanggal pembayaran') }}</dt>
                    <dd class="font-medium">{{ $alumni->payment?->payment_date?->translatedFormat('d F Y') ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Diverifikasi') }}</dt>
                    <dd class="font-medium">{{ $alumni->payment?->verified_at?->translatedFormat('d F Y H:i') ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Catatan') }}</dt>
                    <dd class="font-medium whitespace-pre-line">{{ $alumni->payment?->notes ?: '-' }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <flux:heading size="lg">{{ __('Donasi') }}</flux:heading>
                    <flux:text class="mt-2">{{ __('Nominal donasi tidak ditampilkan pada halaman alumni atau publik.') }}</flux:text>
                </div>

                <flux:badge color="{{ $alumni->donation ? 'green' : 'zinc' }}">
                    {{ $alumni->donation ? __('Tercatat') : __('Belum Ada') }}
                </flux:badge>
            </div>

            <dl class="mt-6 grid gap-4">
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Status publikasi') }}</dt>
                    <dd class="font-medium">{{ $alumni->donation ? $this->donationPublicationLabel($alumni->donation->publication_status) : '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Catatan') }}</dt>
                    <dd class="font-medium whitespace-pre-line">{{ $alumni->donation?->notes ?: '-' }}</dd>
                </div>
            </dl>
        </div>
    </div>
</section>
