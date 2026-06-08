<?php

use App\Models\Alumni;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Manajemen Alumni')] class extends Component {
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = 'all';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function alumniProfiles(): LengthAwarePaginator
    {
        $search = trim($this->search);

        return Alumni::query()
            ->with(['currentCity', 'currentCountry', 'user.role'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('nickname', 'like', "%{$search}%")
                        ->orWhere('student_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('currentCity', function ($query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('currentCountry', function ($query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('user', function ($query) use ($search): void {
                            $query->where('whatsapp_number', 'like', "%{$search}%");
                        });
                });
            })
            ->when(in_array($this->status, ['active', 'deceased'], true), function ($query): void {
                $query->where('alumni_status', $this->status);
            })
            ->orderBy('full_name')
            ->paginate(15);
    }
}; ?>

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="space-y-2">
            <flux:heading size="xl">{{ __('Manajemen Alumni') }}</flux:heading>
            <flux:text class="max-w-2xl">
                {{ __('Data awal alumni dari seed kontak WhatsApp. Gunakan pencarian untuk menelusuri nama, NIM, email, atau nomor WhatsApp.') }}
            </flux:text>
        </div>

        <div class="grid gap-3 lg:w-[34rem]">
            <div class="flex justify-end">
                <flux:button icon="arrow-down-tray" variant="primary" :href="route('reports.alumni.export')">
                    {{ __('Export Alumni') }}
                </flux:button>
            </div>

            <div class="grid gap-3 sm:grid-cols-[minmax(16rem,1fr)_12rem]">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    icon="magnifying-glass"
                    :label="__('Cari alumni')"
                    :placeholder="__('Nama, NIM, WhatsApp')"
                />

                <flux:select wire:model.live="status" :label="__('Status')">
                    <flux:select.option value="all">{{ __('Semua') }}</flux:select.option>
                    <flux:select.option value="active">{{ __('Aktif') }}</flux:select.option>
                    <flux:select.option value="deceased">{{ __('Wafat') }}</flux:select.option>
                </flux:select>
            </div>
        </div>
    </div>

    <flux:table :paginate="$this->alumniProfiles" pagination:scroll-to="body">
        <flux:table.columns>
            <flux:table.column>{{ __('Nama') }}</flux:table.column>
            <flux:table.column>{{ __('NIM') }}</flux:table.column>
            <flux:table.column>{{ __('WhatsApp') }}</flux:table.column>
            <flux:table.column>{{ __('Domisili') }}</flux:table.column>
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column>{{ __('RSVP') }}</flux:table.column>
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
                    <flux:table.cell>{{ collect([$profile->currentCity?->name, $profile->currentCountry?->name])->filter()->join(', ') ?: '-' }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge color="{{ $profile->alumni_status === 'active' ? 'green' : 'zinc' }}">
                            {{ $profile->alumni_status === 'active' ? __('Aktif') : __('Wafat') }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge color="{{ $profile->rsvp_status === 'attending' ? 'green' : ($profile->rsvp_status === 'not_attending' ? 'red' : 'amber') }}">
                            {{ match ($profile->rsvp_status) {
                                'attending' => __('Hadir'),
                                'not_attending' => __('Tidak Hadir'),
                                default => __('Pending'),
                            } }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <flux:button size="sm" variant="ghost" icon="eye" :href="route('admin.alumni.show', $profile)" wire:navigate>
                            {{ __('Detail') }}
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7">
                        <div class="py-10 text-center">
                            <flux:heading size="lg">{{ __('Tidak ada alumni ditemukan') }}</flux:heading>
                            <flux:text>{{ __('Ubah kata kunci atau filter status untuk melihat data lain.') }}</flux:text>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</section>
