<?php

use App\Models\Alumni;
use App\Models\Donation;
use App\Models\MediaItem;
use App\Models\News;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard')] class extends Component {
    #[Computed]
    public function user(): User
    {
        return Auth::user();
    }

    #[Computed]
    public function alumni(): ?Alumni
    {
        return $this->user->alumni()
            ->with(['payment', 'donation', 'roomAssignment.room'])
            ->first();
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function alumniStats(): array
    {
        return [
            'total' => Alumni::query()->count(),
            'active' => Alumni::query()->where('alumni_status', 'active')->count(),
            'deceased' => Alumni::query()->where('alumni_status', 'deceased')->count(),
            'completed_profiles' => Alumni::query()->where('is_profile_completed', true)->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function rsvpStats(): array
    {
        return [
            'pending' => Alumni::query()->where('rsvp_status', 'pending')->count(),
            'attending' => Alumni::query()->where('rsvp_status', 'attending')->count(),
            'not_attending' => Alumni::query()->where('rsvp_status', 'not_attending')->count(),
        ];
    }

    /**
     * @return array<string, int|float>
     */
    #[Computed]
    public function financeStats(): array
    {
        $paidAmount = (float) Payment::query()
            ->where('status', 'paid')
            ->sum('amount');
        $donationAmount = (float) Donation::query()->sum('amount');

        return [
            'unpaid' => Alumni::query()
                ->whereDoesntHave('payment')
                ->orWhereHas('payment', fn ($query) => $query->where('status', 'unpaid'))
                ->count(),
            'pending_verification' => Payment::query()->where('status', 'pending_verification')->count(),
            'paid' => Payment::query()->where('status', 'paid')->count(),
            'donors' => Donation::query()->count(),
            'anonymous_donors' => Donation::query()->where('publication_status', 'anonymous')->count(),
            'public_donors' => Donation::query()->where('publication_status', 'show_name')->count(),
            'paid_amount' => $paidAmount,
            'donation_amount' => $donationAmount,
            'total_amount' => $paidAmount + $donationAmount,
        ];
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function systemStats(): array
    {
        return [
            'users' => User::query()->count(),
            'active_users' => User::query()->where('is_active', true)->count(),
            'login_today' => User::query()->whereDate('last_login_at', today())->count(),
            'profile_updates_today' => Alumni::query()->whereDate('updated_at', today())->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function roomStats(): array
    {
        return [
            'rooms' => Room::query()->count(),
            'assigned' => RoomAssignment::query()->count(),
            'available_capacity' => max(0, (int) Room::query()->sum('capacity') - RoomAssignment::query()->count()),
        ];
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function documentationStats(): array
    {
        return [
            'photos' => MediaItem::query()->where('type', 'photo')->count(),
            'videos' => MediaItem::query()->where('type', 'video')->count(),
            'public' => MediaItem::query()->where('visibility', 'public')->count(),
            'internal' => MediaItem::query()->where('visibility', 'internal')->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function newsStats(): array
    {
        return [
            'total' => News::query()->count(),
            'draft' => News::query()->where('status', 'draft')->count(),
            'published' => News::query()->where('status', 'published')->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function operationalAnalytics(): array
    {
        $totalAlumni = $this->alumniStats['total'];
        $totalDocumentation = $this->documentationStats['photos'] + $this->documentationStats['videos'];

        return [
            'rsvp_rate' => $this->percentage($this->rsvpStats['attending'], $totalAlumni),
            'payment_rate' => $this->percentage($this->financeStats['paid'], $totalAlumni),
            'profile_completion_rate' => $this->percentage($this->alumniStats['completed_profiles'], $totalAlumni),
            'documentation_total' => $totalDocumentation,
            'donor_rate' => $this->percentage($this->financeStats['donors'], $totalAlumni),
        ];
    }

    /**
     * @return Collection<int, Payment>
     */
    #[Computed]
    public function recentPayments(): Collection
    {
        return Payment::query()
            ->with('alumni')
            ->latest('updated_at')
            ->limit(5)
            ->get();
    }

    /**
     * @return Collection<int, Donation>
     */
    #[Computed]
    public function recentDonations(): Collection
    {
        return Donation::query()
            ->with('alumni')
            ->latest()
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function latestNews(): ?News
    {
        return News::query()
            ->with('author')
            ->where('status', 'published')
            ->latest('published_at')
            ->first();
    }

    public function canManageAlumni(): bool
    {
        return $this->user->canManageAlumni();
    }

    public function canManageFinance(): bool
    {
        return $this->user->canManageFinance();
    }

    public function isSuperadmin(): bool
    {
        return $this->user->hasRole('superadmin');
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

    public function publicationStatusLabel(?string $status): string
    {
        return $status === 'anonymous'
            ? __('Anonim')
            : __('Publik');
    }

    public function profileCompletion(): int
    {
        $alumni = $this->alumni;

        if ($alumni === null) {
            return 0;
        }

        $fields = [
            $alumni->full_name,
            $alumni->nickname,
            $alumni->student_number,
            $alumni->email,
            $alumni->current_city_id,
            $alumni->current_country_id,
            $alumni->company,
            $alumni->job_title,
            $alumni->short_story,
            $alumni->message_to_friends,
        ];

        $completed = collect($fields)
            ->filter(fn ($value): bool => filled($value))
            ->count();

        return (int) round(($completed / count($fields)) * 100);
    }

    public function money(int|float|null $amount): string
    {
        return 'Rp '.number_format((float) $amount, 0, ',', '.');
    }

    private function percentage(int|float $value, int|float $total): int
    {
        if ($total <= 0) {
            return 0;
        }

        return (int) round(($value / $total) * 100);
    }
}; ?>

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="space-y-2">
            <flux:heading size="xl">{{ __('Dashboard') }}</flux:heading>
            <flux:text class="max-w-3xl">
                {{ __('Ringkasan operasional Sistem Manajemen Reuni Alumni Teknik Geodesi UGM Angkatan 1996.') }}
            </flux:text>
        </div>

        <div class="flex flex-wrap gap-2">
            @can('update-own-alumni-profile')
                <flux:button variant="ghost" :href="route('alumni.profile')" wire:navigate>
                    {{ __('Lengkapi Profil') }}
                </flux:button>
            @endcan

            @can('manage-finance')
                <flux:button variant="primary" :href="route('finance.index')" wire:navigate>
                    {{ __('Kelola Keuangan') }}
                </flux:button>
            @endcan
        </div>
    </div>

    @if ($this->alumni)
        <div class="grid gap-4 lg:grid-cols-[1.4fr_1fr]">
            <flux:card class="space-y-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="space-y-1">
                        <flux:heading size="lg">{{ __('Selamat datang, :name', ['name' => $this->alumni->nickname ?: $this->alumni->full_name]) }}</flux:heading>
                        <flux:text>{{ $this->alumni->full_name }}</flux:text>
                    </div>

                    <flux:badge color="{{ $this->alumni->is_profile_completed ? 'green' : 'amber' }}">
                        {{ $this->alumni->is_profile_completed ? __('Profil Lengkap') : __('Profil Perlu Dilengkapi') }}
                    </flux:badge>
                </div>

                <div class="grid gap-3 sm:grid-cols-4">
                    <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <flux:text>{{ __('RSVP') }}</flux:text>
                        <div class="mt-2">
                            <flux:badge color="{{ $this->rsvpStatusColor($this->alumni->rsvp_status) }}">
                                {{ $this->rsvpStatusLabel($this->alumni->rsvp_status) }}
                            </flux:badge>
                        </div>
                    </div>

                    <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <flux:text>{{ __('Pembayaran') }}</flux:text>
                        <div class="mt-2">
                            <flux:badge color="{{ $this->paymentStatusColor($this->alumni->payment?->status) }}">
                                {{ $this->paymentStatusLabel($this->alumni->payment?->status) }}
                            </flux:badge>
                        </div>
                    </div>

                    <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <flux:text>{{ __('Kamar') }}</flux:text>
                        <div class="mt-2 font-medium">{{ $this->alumni->roomAssignment?->room?->room_name ?: __('Belum tersedia') }}</div>
                    </div>

                    <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <flux:text>{{ __('Kelengkapan Profil') }}</flux:text>
                        <div class="mt-2 text-2xl font-semibold tabular-nums">{{ $this->profileCompletion() }}%</div>
                    </div>
                </div>
            </flux:card>

            <flux:card class="space-y-4">
                <flux:heading size="lg">{{ __('Akses Cepat') }}</flux:heading>
                <div class="grid gap-3">
                    <flux:button variant="ghost" :href="route('alumni.profile')" wire:navigate>{{ __('Profil Saya') }}</flux:button>
                    <flux:button variant="ghost" :href="route('alumni.rsvp')" wire:navigate>{{ __('Isi RSVP') }}</flux:button>
                    <flux:button variant="ghost" :href="route('alumni.room')" wire:navigate>{{ __('Kamar Saya') }}</flux:button>
                    <flux:button variant="ghost" :href="route('alumni.finance')" wire:navigate>{{ __('Status Pembayaran') }}</flux:button>
                    <flux:button variant="ghost" :href="route('documentation.index')" wire:navigate>{{ __('Dokumentasi') }}</flux:button>
                    <flux:button variant="ghost" :href="route('alumni.directory.index')" wire:navigate>{{ __('Direktori Alumni') }}</flux:button>
                    <flux:button variant="ghost" :href="route('alumni.distribution.index')" wire:navigate>{{ __('Peta Alumni') }}</flux:button>
                </div>
            </flux:card>
        </div>
    @elseif (! $this->canManageAlumni() && ! $this->canManageFinance())
        <flux:card class="space-y-3">
            <flux:heading size="lg">{{ __('Akun belum terhubung ke data alumni') }}</flux:heading>
            <flux:text>{{ __('Hubungi administrator untuk menghubungkan akun ini dengan profil alumni.') }}</flux:text>
        </flux:card>
    @endif

    @if ($this->canManageAlumni())
        <div class="space-y-4">
            <div class="flex items-center justify-between gap-4">
                <flux:heading size="lg">{{ $this->isSuperadmin() ? __('Dashboard Superadmin') : __('Dashboard Administrator') }}</flux:heading>
                <flux:button variant="ghost" :href="route('admin.alumni.index')" wire:navigate>{{ __('Manajemen Alumni') }}</flux:button>
            </div>

            <div class="grid gap-4 md:grid-cols-4">
                <flux:card>
                    <flux:text>{{ __('Total Alumni') }}</flux:text>
                    <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->alumniStats['total'] }}</div>
                </flux:card>
                <flux:card>
                    <flux:text>{{ __('Alumni Aktif') }}</flux:text>
                    <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->alumniStats['active'] }}</div>
                </flux:card>
                <flux:card>
                    <flux:text>{{ __('Alumni Meninggal') }}</flux:text>
                    <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->alumniStats['deceased'] }}</div>
                </flux:card>
                <flux:card>
                    <flux:text>{{ __('Profil Lengkap') }}</flux:text>
                    <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->alumniStats['completed_profiles'] }}</div>
                </flux:card>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <flux:card>
                    <flux:text>{{ __('RSVP Belum Merespon') }}</flux:text>
                    <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->rsvpStats['pending'] }}</div>
                </flux:card>
                <flux:card>
                    <flux:text>{{ __('RSVP Hadir') }}</flux:text>
                    <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->rsvpStats['attending'] }}</div>
                </flux:card>
                <flux:card>
                    <flux:text>{{ __('RSVP Tidak Hadir') }}</flux:text>
                    <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->rsvpStats['not_attending'] }}</div>
                </flux:card>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <flux:card>
                    <flux:text>{{ __('Total Kamar') }}</flux:text>
                    <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->roomStats['rooms'] }}</div>
                </flux:card>
                <flux:card>
                    <flux:text>{{ __('Penghuni Ditempatkan') }}</flux:text>
                    <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->roomStats['assigned'] }}</div>
                </flux:card>
                <flux:card>
                    <flux:text>{{ __('Sisa Kapasitas') }}</flux:text>
                    <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->roomStats['available_capacity'] }}</div>
                </flux:card>
            </div>

            <div class="grid gap-4 md:grid-cols-4">
                <flux:card>
                    <flux:text>{{ __('Total Foto') }}</flux:text>
                    <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->documentationStats['photos'] }}</div>
                </flux:card>
                <flux:card>
                    <flux:text>{{ __('Total Video') }}</flux:text>
                    <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->documentationStats['videos'] }}</div>
                </flux:card>
                <flux:card>
                    <flux:text>{{ __('Dokumentasi Publik') }}</flux:text>
                    <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->documentationStats['public'] }}</div>
                </flux:card>
                <flux:card>
                    <flux:text>{{ __('Dokumentasi Internal') }}</flux:text>
                    <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->documentationStats['internal'] }}</div>
                </flux:card>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <flux:card>
                    <flux:text>{{ __('Total Berita') }}</flux:text>
                    <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->newsStats['total'] }}</div>
                </flux:card>
                <flux:card>
                    <flux:text>{{ __('Berita Draft') }}</flux:text>
                    <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->newsStats['draft'] }}</div>
                </flux:card>
                <flux:card>
                    <flux:text>{{ __('Berita Published') }}</flux:text>
                    <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->newsStats['published'] }}</div>
                </flux:card>
            </div>

            @if (! $this->canManageFinance())
                <div class="grid gap-4 md:grid-cols-4">
                    <flux:card>
                        <flux:text>{{ __('Pembayaran Belum Bayar') }}</flux:text>
                        <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->financeStats['unpaid'] }}</div>
                    </flux:card>
                    <flux:card>
                        <flux:text>{{ __('Pembayaran Menunggu') }}</flux:text>
                        <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->financeStats['pending_verification'] }}</div>
                    </flux:card>
                    <flux:card>
                        <flux:text>{{ __('Pembayaran Lunas') }}</flux:text>
                        <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->financeStats['paid'] }}</div>
                    </flux:card>
                    <flux:card>
                        <flux:text>{{ __('Jumlah Donatur') }}</flux:text>
                        <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->financeStats['donors'] }}</div>
                    </flux:card>
                </div>
            @endif

            <flux:card class="space-y-4">
                <flux:heading size="lg">{{ __('Analytics Operasional') }}</flux:heading>
                <div class="grid gap-4 md:grid-cols-5">
                    <div>
                        <flux:text>{{ __('Kehadiran') }}</flux:text>
                        <div class="mt-1 text-2xl font-semibold tabular-nums">{{ $this->operationalAnalytics['rsvp_rate'] }}%</div>
                    </div>
                    <div>
                        <flux:text>{{ __('Pembayaran Lunas') }}</flux:text>
                        <div class="mt-1 text-2xl font-semibold tabular-nums">{{ $this->operationalAnalytics['payment_rate'] }}%</div>
                    </div>
                    <div>
                        <flux:text>{{ __('Donatur') }}</flux:text>
                        <div class="mt-1 text-2xl font-semibold tabular-nums">{{ $this->operationalAnalytics['donor_rate'] }}%</div>
                    </div>
                    <div>
                        <flux:text>{{ __('Profil Lengkap') }}</flux:text>
                        <div class="mt-1 text-2xl font-semibold tabular-nums">{{ $this->operationalAnalytics['profile_completion_rate'] }}%</div>
                    </div>
                    <div>
                        <flux:text>{{ __('Dokumentasi') }}</flux:text>
                        <div class="mt-1 text-2xl font-semibold tabular-nums">{{ $this->operationalAnalytics['documentation_total'] }}</div>
                    </div>
                </div>
            </flux:card>

            <flux:card class="space-y-4">
                <flux:heading size="lg">{{ __('Export Laporan Operasional') }}</flux:heading>
                <div class="flex flex-wrap gap-3">
                    <flux:button icon="arrow-down-tray" variant="ghost" :href="route('reports.alumni.export')">
                        {{ __('Export Alumni') }}
                    </flux:button>
                    <flux:button icon="arrow-down-tray" variant="ghost" :href="route('reports.rsvp.export')">
                        {{ __('Export RSVP') }}
                    </flux:button>
                    <flux:button icon="arrow-down-tray" variant="ghost" :href="route('reports.rooming.export')">
                        {{ __('Export Rooming') }}
                    </flux:button>
                    <flux:button icon="printer" variant="ghost" :href="route('reports.rooming.print')" target="_blank">
                        {{ __('Cetak Rooming') }}
                    </flux:button>
                </div>
            </flux:card>
        </div>
    @endif

    @if ($this->canManageFinance())
        <div class="space-y-4">
            <div class="flex items-center justify-between gap-4">
                <flux:heading size="lg">{{ __('Dashboard Bendahara') }}</flux:heading>
                <flux:button variant="ghost" :href="route('finance.index')" wire:navigate>{{ __('Pembayaran & Donasi') }}</flux:button>
            </div>

            <div class="grid gap-4 md:grid-cols-4">
                <flux:card>
                    <flux:text>{{ __('Belum Bayar') }}</flux:text>
                    <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->financeStats['unpaid'] }}</div>
                </flux:card>
                <flux:card>
                    <flux:text>{{ __('Menunggu Verifikasi') }}</flux:text>
                    <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->financeStats['pending_verification'] }}</div>
                </flux:card>
                <flux:card>
                    <flux:text>{{ __('Lunas') }}</flux:text>
                    <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->financeStats['paid'] }}</div>
                </flux:card>
                <flux:card>
                    <flux:text>{{ __('Donatur') }}</flux:text>
                    <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->financeStats['donors'] }}</div>
                </flux:card>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <flux:card>
                    <flux:text>{{ __('Total Pembayaran Diterima') }}</flux:text>
                    <div class="mt-2 text-2xl font-semibold tabular-nums">{{ $this->money($this->financeStats['paid_amount']) }}</div>
                </flux:card>
                <flux:card>
                    <flux:text>{{ __('Total Donasi Diterima') }}</flux:text>
                    <div class="mt-2 text-2xl font-semibold tabular-nums">{{ $this->money($this->financeStats['donation_amount']) }}</div>
                </flux:card>
                <flux:card>
                    <flux:text>{{ __('Total Dana Terkumpul') }}</flux:text>
                    <div class="mt-2 text-2xl font-semibold tabular-nums">{{ $this->money($this->financeStats['total_amount']) }}</div>
                </flux:card>
            </div>

            <flux:card class="space-y-4">
                <flux:heading size="lg">{{ __('Export Laporan Keuangan') }}</flux:heading>
                <div class="flex flex-wrap gap-3">
                    <flux:button icon="arrow-down-tray" variant="ghost" :href="route('reports.payments.export')">
                        {{ __('Export Pembayaran') }}
                    </flux:button>
                    <flux:button icon="arrow-down-tray" variant="primary" :href="route('reports.donations.export')">
                        {{ __('Export Donasi') }}
                    </flux:button>
                </div>
            </flux:card>

            <div class="grid gap-4 xl:grid-cols-2">
                <flux:card class="space-y-4">
                    <flux:heading size="lg">{{ __('Monitoring Pembayaran') }}</flux:heading>
                    <div class="space-y-3">
                        @forelse ($this->recentPayments as $payment)
                            <div class="flex items-start justify-between gap-4 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700" wire:key="dashboard-payment-{{ $payment->id }}">
                                <div>
                                    <div class="font-medium">{{ $payment->alumni?->full_name }}</div>
                                    <flux:text>{{ $payment->payment_date?->translatedFormat('d F Y') ?: __('Tanggal belum dicatat') }}</flux:text>
                                </div>
                                <flux:badge color="{{ $this->paymentStatusColor($payment->status) }}">
                                    {{ $this->paymentStatusLabel($payment->status) }}
                                </flux:badge>
                            </div>
                        @empty
                            <flux:text>{{ __('Belum ada pembayaran tercatat.') }}</flux:text>
                        @endforelse
                    </div>
                </flux:card>

                <flux:card class="space-y-4">
                    <flux:heading size="lg">{{ __('Monitoring Donasi') }}</flux:heading>
                    <div class="space-y-3">
                        @forelse ($this->recentDonations as $donation)
                            <div class="flex items-start justify-between gap-4 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700" wire:key="dashboard-donation-{{ $donation->id }}">
                                <div>
                                    <div class="font-medium">{{ $donation->alumni?->full_name }}</div>
                                    <flux:text>{{ $donation->created_at?->translatedFormat('d F Y') }}</flux:text>
                                </div>
                                <div class="text-right">
                                    <div class="font-medium tabular-nums">{{ $this->money($donation->amount) }}</div>
                                    <flux:badge color="{{ $donation->publication_status === 'anonymous' ? 'amber' : 'green' }}">
                                        {{ $this->publicationStatusLabel($donation->publication_status) }}
                                    </flux:badge>
                                </div>
                            </div>
                        @empty
                            <flux:text>{{ __('Belum ada donasi tercatat.') }}</flux:text>
                        @endforelse
                    </div>
                </flux:card>
            </div>
        </div>
    @endif

    @if ($this->isSuperadmin())
        <div class="space-y-4">
            <flux:heading size="lg">{{ __('KPI Sistem') }}</flux:heading>
            <div class="grid gap-4 md:grid-cols-4">
                <flux:card>
                    <flux:text>{{ __('Total User') }}</flux:text>
                    <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->systemStats['users'] }}</div>
                </flux:card>
                <flux:card>
                    <flux:text>{{ __('User Aktif') }}</flux:text>
                    <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->systemStats['active_users'] }}</div>
                </flux:card>
                <flux:card>
                    <flux:text>{{ __('Login Hari Ini') }}</flux:text>
                    <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->systemStats['login_today'] }}</div>
                </flux:card>
                <flux:card>
                    <flux:text>{{ __('Perubahan Profil Hari Ini') }}</flux:text>
                    <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->systemStats['profile_updates_today'] }}</div>
                </flux:card>
            </div>
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-3">
        <flux:card class="space-y-2">
            <flux:heading size="lg">{{ __('Dokumentasi Terbaru') }}</flux:heading>
            <flux:text>{{ __('Foto dan video terbaru kini tersedia melalui menu Dokumentasi.') }}</flux:text>
        </flux:card>
        <flux:card class="space-y-2">
            <flux:heading size="lg">{{ __('Berita Terbaru') }}</flux:heading>
            @if ($this->latestNews)
                <flux:text>{{ $this->latestNews->title }}</flux:text>
                <flux:button variant="ghost" icon="arrow-right" :href="route('news.show', $this->latestNews->slug)" wire:navigate>
                    {{ __('Baca') }}
                </flux:button>
            @else
                <flux:text>{{ __('Belum ada berita published.') }}</flux:text>
            @endif
        </flux:card>
        <flux:card class="space-y-2">
            <flux:heading size="lg">{{ __('WhatsApp Analytics') }}</flux:heading>
            <flux:text>{{ __('Statistik grup akan tersedia setelah modul import dan analisis WhatsApp dibangun.') }}</flux:text>
        </flux:card>
    </div>
</section>
