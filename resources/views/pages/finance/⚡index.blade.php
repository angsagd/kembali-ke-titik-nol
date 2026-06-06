<?php

use App\Models\Alumni;
use App\Models\Donation;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Pembayaran & Donasi')] class extends Component {
    public string $q = '';

    public ?int $selected_alumni_id = null;

    public string $payment_status = 'unpaid';

    public int|string|null $payment_amount = null;

    public ?string $payment_date = null;

    public ?string $payment_notes = null;

    public bool $has_donation = false;

    public int|string|null $donation_amount = null;

    public string $donation_publication_status = 'show_name';

    public ?string $donation_notes = null;

    public function mount(): void
    {
        $this->selectAlumni(
            Alumni::query()->orderBy('full_name')->value('id'),
        );
    }

    #[Computed]
    public function alumniProfiles(): Collection
    {
        $search = trim($this->q);

        return Alumni::query()
            ->with(['payment', 'donation'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('nickname', 'like', "%{$search}%")
                        ->orWhere('student_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('full_name')
            ->limit(30)
            ->get();
    }

    #[Computed]
    public function selectedAlumni(): ?Alumni
    {
        if ($this->selected_alumni_id === null) {
            return null;
        }

        return Alumni::query()
            ->with(['payment.verifier', 'donation.manager'])
            ->find($this->selected_alumni_id);
    }

    #[Computed]
    public function summary(): array
    {
        return [
            'unpaid' => Alumni::query()->whereDoesntHave('payment')
                ->orWhereHas('payment', fn ($query) => $query->where('status', 'unpaid'))
                ->count(),
            'pending_verification' => Alumni::query()
                ->whereHas('payment', fn ($query) => $query->where('status', 'pending_verification'))
                ->count(),
            'paid' => Alumni::query()
                ->whereHas('payment', fn ($query) => $query->where('status', 'paid'))
                ->count(),
            'donors' => Alumni::query()->whereHas('donation')->count(),
        ];
    }

    public function updatedQ(): void
    {
        unset($this->alumniProfiles);
    }

    public function selectAlumni(?int $alumniId): void
    {
        $this->selected_alumni_id = $alumniId;

        $alumni = $this->selectedAlumni;

        $this->payment_status = $alumni?->payment?->status ?? 'unpaid';
        $this->payment_amount = $alumni?->payment?->amount;
        $this->payment_date = $alumni?->payment?->payment_date?->format('Y-m-d');
        $this->payment_notes = $alumni?->payment?->notes;
        $this->has_donation = $alumni?->donation !== null;
        $this->donation_amount = $alumni?->donation?->amount;
        $this->donation_publication_status = $alumni?->donation?->publication_status ?? 'show_name';
        $this->donation_notes = $alumni?->donation?->notes;
    }

    public function savePayment(): void
    {
        $validated = $this->validate([
            'selected_alumni_id' => ['required', Rule::exists(Alumni::class, 'id')],
            'payment_status' => ['required', Rule::in(['unpaid', 'pending_verification', 'paid'])],
            'payment_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'payment_date' => ['nullable', 'date'],
            'payment_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $verifiedBy = $validated['payment_status'] === 'paid'
            ? Auth::id()
            : null;
        $verifiedAt = $validated['payment_status'] === 'paid'
            ? now()
            : null;

        $this->selectedAlumni?->payment()->updateOrCreate(
            ['alumni_id' => $validated['selected_alumni_id']],
            [
                'amount' => $validated['payment_amount'],
                'status' => $validated['payment_status'],
                'payment_date' => $validated['payment_date'],
                'verified_by' => $verifiedBy,
                'verified_at' => $verifiedAt,
                'notes' => $validated['payment_notes'],
            ],
        );

        unset($this->selectedAlumni, $this->alumniProfiles, $this->summary);

        Flux::toast(variant: 'success', text: __('Pembayaran disimpan.'));
    }

    public function saveDonation(): void
    {
        $validated = $this->validate([
            'selected_alumni_id' => ['required', Rule::exists(Alumni::class, 'id')],
            'has_donation' => ['boolean'],
            'donation_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999999.99'],
            'donation_publication_status' => ['required', Rule::in(['show_name', 'anonymous'])],
            'donation_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($validated['has_donation']) {
            $this->selectedAlumni?->donation()->updateOrCreate(
                ['alumni_id' => $validated['selected_alumni_id']],
                [
                    'amount' => $validated['donation_amount'],
                    'publication_status' => $validated['donation_publication_status'],
                    'notes' => $validated['donation_notes'],
                    'managed_by' => Auth::id(),
                ],
            );
        } else {
            Donation::query()
                ->where('alumni_id', $validated['selected_alumni_id'])
                ->delete();
        }

        unset($this->selectedAlumni, $this->alumniProfiles, $this->summary);

        Flux::toast(variant: 'success', text: __('Donasi disimpan.'));
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
            : __('Tampilkan Nama');
    }
}; ?>

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="space-y-2">
            <flux:heading size="xl">{{ __('Pembayaran & Donasi') }}</flux:heading>
            <flux:text class="max-w-3xl">
                {{ __('Kelola pembayaran kontribusi reuni dan donasi alumni. Nominal donasi hanya tersedia untuk bendahara dan superadmin.') }}
            </flux:text>
        </div>

        <div class="flex flex-wrap gap-2">
            <flux:button icon="arrow-down-tray" variant="ghost" :href="route('reports.payments.export')">
                {{ __('Export Pembayaran') }}
            </flux:button>
            <flux:button icon="arrow-down-tray" variant="primary" :href="route('reports.donations.export')">
                {{ __('Export Donasi') }}
            </flux:button>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:text>{{ __('Belum bayar') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['unpaid'] }}</div>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:text>{{ __('Menunggu verifikasi') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['pending_verification'] }}</div>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:text>{{ __('Lunas') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['paid'] }}</div>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:text>{{ __('Donatur') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['donors'] }}</div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[24rem_1fr]">
        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg">{{ __('Alumni') }}</flux:heading>
            <flux:input class="mt-4" wire:model.live.debounce.300ms="q" :placeholder="__('Cari nama, panggilan, atau NIM')" />

            <div class="mt-5 grid gap-2">
                @forelse ($this->alumniProfiles as $profile)
                    <button
                        type="button"
                        wire:key="finance-alumni-{{ $profile->id }}"
                        wire:click="selectAlumni({{ $profile->id }})"
                        class="rounded-md border p-3 text-left transition hover:bg-zinc-50 dark:hover:bg-zinc-800 {{ $selected_alumni_id === $profile->id ? 'border-amber-500 bg-amber-50 dark:border-amber-400 dark:bg-amber-950/30' : 'border-zinc-200 dark:border-zinc-700' }}"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-medium">{{ $profile->full_name }}</div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $profile->student_number ?: $profile->nickname ?: '-' }}</div>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <flux:badge color="{{ $this->paymentStatusColor($profile->payment?->status) }}">
                                    {{ $this->paymentStatusLabel($profile->payment?->status) }}
                                </flux:badge>

                                @if ($profile->donation)
                                    <flux:badge color="green">{{ __('Donatur') }}</flux:badge>
                                @endif
                            </div>
                        </div>
                    </button>
                @empty
                    <flux:text>{{ __('Tidak ada alumni yang cocok.') }}</flux:text>
                @endforelse
            </div>
        </div>

        <div class="space-y-6">
            @if ($this->selectedAlumni)
                <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <flux:heading size="lg">{{ $this->selectedAlumni->full_name }}</flux:heading>
                            <flux:text>{{ __('NIM: :nim', ['nim' => $this->selectedAlumni->student_number ?: '-']) }}</flux:text>
                        </div>
                    </div>
                </div>

                <form wire:submit="savePayment" class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <flux:heading size="lg">{{ __('Pembayaran') }}</flux:heading>
                            <flux:text>{{ __('Status pembayaran kontribusi reuni.') }}</flux:text>
                        </div>
                        <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled">
                            {{ __('Simpan Pembayaran') }}
                        </flux:button>
                    </div>

                    <div class="mt-5 grid gap-5 lg:grid-cols-2">
                        <flux:select wire:model="payment_status" :label="__('Status')">
                            <flux:select.option value="unpaid">{{ __('Belum Bayar') }}</flux:select.option>
                            <flux:select.option value="pending_verification">{{ __('Menunggu Verifikasi') }}</flux:select.option>
                            <flux:select.option value="paid">{{ __('Lunas') }}</flux:select.option>
                        </flux:select>

                        <flux:input wire:model="payment_amount" :label="__('Nominal')" type="number" min="0" step="1000" />
                        <flux:input wire:model="payment_date" :label="__('Tanggal pembayaran')" type="date" />
                        <flux:textarea wire:model="payment_notes" :label="__('Catatan pembayaran')" rows="3" class="lg:col-span-2" />
                    </div>
                </form>

                <form wire:submit="saveDonation" class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <flux:heading size="lg">{{ __('Donasi') }}</flux:heading>
                            <flux:text>{{ __('Kelola donasi dan status publikasi nama donatur.') }}</flux:text>
                        </div>
                        <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled">
                            {{ __('Simpan Donasi') }}
                        </flux:button>
                    </div>

                    <div class="mt-5 grid gap-5 lg:grid-cols-2">
                        <div class="lg:col-span-2">
                            <flux:checkbox wire:model.live="has_donation" :label="__('Alumni ini memiliki donasi tercatat')" />
                        </div>

                        @if ($has_donation)
                            <flux:input wire:model="donation_amount" :label="__('Nominal')" type="number" min="0" step="1000" />

                            <flux:select wire:model="donation_publication_status" :label="__('Publikasi donor')">
                                <flux:select.option value="show_name">{{ __('Tampilkan Nama Saya') }}</flux:select.option>
                                <flux:select.option value="anonymous">{{ __('Donatur Anonim') }}</flux:select.option>
                            </flux:select>

                            <flux:textarea wire:model="donation_notes" :label="__('Catatan donasi')" rows="3" class="lg:col-span-2" />
                        @else
                            <div class="rounded-lg border border-dashed border-zinc-300 p-5 lg:col-span-2 dark:border-zinc-700">
                                <flux:text>{{ __('Saat disimpan, data donasi untuk alumni ini akan dikosongkan.') }}</flux:text>
                            </div>
                        @endif
                    </div>
                </form>
            @else
                <div class="rounded-lg border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-700">
                    <flux:heading size="lg">{{ __('Belum ada alumni') }}</flux:heading>
                    <flux:text>{{ __('Data alumni perlu tersedia sebelum pembayaran dan donasi dapat dikelola.') }}</flux:text>
                </div>
            @endif
        </div>
    </div>
</section>
