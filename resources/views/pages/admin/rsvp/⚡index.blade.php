<?php

use App\Models\Alumni;
use App\Models\ApplicationSetting;
use App\Models\AuditLog;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Monitoring RSVP')] class extends Component {
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = 'all';

    public bool $public_rsvp_form_enabled = true;

    public function mount(): void
    {
        $this->public_rsvp_form_enabled = ApplicationSetting::boolean(ApplicationSetting::PUBLIC_RSVP_FORM_ENABLED, true);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function summary(): array
    {
        return [
            'total' => Alumni::query()->count(),
            'pending' => Alumni::query()->where('rsvp_status', 'pending')->count(),
            'attending' => Alumni::query()->where('rsvp_status', 'attending')->count(),
            'not_attending' => Alumni::query()->where('rsvp_status', 'not_attending')->count(),
        ];
    }

    #[Computed]
    public function responseRate(): int
    {
        if ($this->summary['total'] === 0) {
            return 0;
        }

        $responded = $this->summary['attending'] + $this->summary['not_attending'];

        return (int) round(($responded / $this->summary['total']) * 100);
    }

    #[Computed]
    public function alumniProfiles(): LengthAwarePaginator
    {
        $search = trim($this->search);

        return Alumni::query()
            ->with('user')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('nickname', 'like', "%{$search}%")
                        ->orWhere('student_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($query) use ($search): void {
                            $query->where('whatsapp_number', 'like', "%{$search}%");
                        });
                });
            })
            ->when(in_array($this->status, ['pending', 'attending', 'not_attending'], true), function ($query): void {
                $query->where('rsvp_status', $this->status);
            })
            ->orderBy('full_name')
            ->paginate(15);
    }

    public function togglePublicRsvpForm(): void
    {
        $oldValue = $this->public_rsvp_form_enabled;
        $this->public_rsvp_form_enabled = ! $this->public_rsvp_form_enabled;

        $setting = ApplicationSetting::setBoolean(ApplicationSetting::PUBLIC_RSVP_FORM_ENABLED, $this->public_rsvp_form_enabled);

        AuditLog::record(
            action: 'settings.public_rsvp_form_updated',
            entity: $setting,
            oldValues: ['enabled' => $oldValue],
            newValues: ['enabled' => $this->public_rsvp_form_enabled],
        );

        Flux::toast(variant: 'success', text: __('Pengaturan form RSVP publik diperbarui.'));
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
}; ?>

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="space-y-2">
            <flux:heading size="xl">{{ __('Monitoring RSVP') }}</flux:heading>
            <flux:text class="max-w-2xl">
                {{ __('Pantau status kehadiran alumni dan rekap peserta reuni berdasarkan RSVP yang diisi alumni.') }}
            </flux:text>
        </div>

        <div class="grid gap-3 lg:w-[44rem]">
            <div class="flex justify-end">
                <flux:button icon="arrow-down-tray" variant="primary" :href="route('reports.rsvp.export')">
                    {{ __('Export CSV') }}
                </flux:button>
            </div>

            <div class="grid gap-3 sm:grid-cols-[minmax(16rem,1fr)_12rem]">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    icon="magnifying-glass"
                    :label="__('Cari alumni')"
                    :placeholder="__('Nama, NIM, WhatsApp')"
                />

                <flux:select wire:model.live="status" :label="__('Status RSVP')">
                    <flux:select.option value="all">{{ __('Semua') }}</flux:select.option>
                    <flux:select.option value="pending">{{ __('Belum Merespon') }}</flux:select.option>
                    <flux:select.option value="attending">{{ __('Hadir') }}</flux:select.option>
                    <flux:select.option value="not_attending">{{ __('Tidak Hadir') }}</flux:select.option>
                </flux:select>
            </div>
        </div>
    </div>

    <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <flux:heading size="lg">{{ __('Form RSVP Publik') }}</flux:heading>
                <flux:text class="max-w-2xl">
                    {{ __('Kontrol apakah alumni dapat mengisi atau mengedit data melalui halaman publik /rsvp tanpa login.') }}
                </flux:text>
            </div>

            <div class="flex flex-col gap-3 sm:items-end">
                <flux:badge color="{{ $public_rsvp_form_enabled ? 'green' : 'red' }}">
                    {{ $public_rsvp_form_enabled ? __('Dibuka') : __('Ditutup') }}
                </flux:badge>
                <flux:button
                    type="button"
                    variant="{{ $public_rsvp_form_enabled ? 'danger' : 'primary' }}"
                    icon="{{ $public_rsvp_form_enabled ? 'lock-closed' : 'lock-open' }}"
                    wire:click="togglePublicRsvpForm"
                    wire:loading.attr="disabled"
                >
                    {{ $public_rsvp_form_enabled ? __('Tutup Form Publik') : __('Buka Form Publik') }}
                </flux:button>
            </div>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-5">
        <flux:card>
            <flux:text>{{ __('Total Alumni') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['total'] }}</div>
        </flux:card>
        <flux:card>
            <flux:text>{{ __('Belum Merespon') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['pending'] }}</div>
        </flux:card>
        <flux:card>
            <flux:text>{{ __('Hadir') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['attending'] }}</div>
        </flux:card>
        <flux:card>
            <flux:text>{{ __('Tidak Hadir') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['not_attending'] }}</div>
        </flux:card>
        <flux:card>
            <flux:text>{{ __('Response Rate') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->responseRate }}%</div>
        </flux:card>
    </div>

    <flux:table :paginate="$this->alumniProfiles" pagination:scroll-to="body">
        <flux:table.columns>
            <flux:table.column>{{ __('Nama') }}</flux:table.column>
            <flux:table.column>{{ __('NIM') }}</flux:table.column>
            <flux:table.column>{{ __('WhatsApp') }}</flux:table.column>
            <flux:table.column>{{ __('Status RSVP') }}</flux:table.column>
            <flux:table.column>{{ __('Terakhir Diperbarui') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Aksi') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->alumniProfiles as $profile)
                <flux:table.row :key="$profile->id">
                    <flux:table.cell variant="strong">
                        <div class="grid gap-1">
                            <span>{{ $profile->full_name }}</span>
                            <span class="text-xs font-normal text-zinc-500 dark:text-zinc-400">{{ $profile->nickname ?: __('Nama panggilan belum diisi') }}</span>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>{{ $profile->student_number ?: '-' }}</flux:table.cell>
                    <flux:table.cell>{{ $profile->user?->whatsapp_number ?: '-' }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge color="{{ $this->rsvpStatusColor($profile->rsvp_status) }}">
                            {{ $this->rsvpStatusLabel($profile->rsvp_status) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>{{ $profile->updated_at?->translatedFormat('d F Y H:i') ?: '-' }}</flux:table.cell>
                    <flux:table.cell align="end">
                        <flux:button size="sm" variant="ghost" icon="eye" :href="route('admin.alumni.show', $profile)" wire:navigate>
                            {{ __('Detail') }}
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6">
                        <div class="py-10 text-center">
                            <flux:heading size="lg">{{ __('Tidak ada RSVP ditemukan') }}</flux:heading>
                            <flux:text>{{ __('Ubah kata kunci atau filter status untuk melihat data lain.') }}</flux:text>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</section>
