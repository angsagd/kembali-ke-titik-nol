<?php

use App\Models\Alumni;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Dulu & Sekarang')] class extends Component {
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
        if (! in_array($this->status, ['all', 'active', 'deceased'], true)) {
            $this->status = 'all';
        }

        $this->resetPage();
    }

    #[Computed]
    public function alumniProfiles(): LengthAwarePaginator
    {
        $search = trim($this->search);

        return Alumni::query()
            ->where(function ($query): void {
                $query->whereNotNull('college_photo_path')->orWhereNotNull('current_photo_path');
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->whereLike('full_name', "%{$search}%")
                        ->orWhereLike('nickname', "%{$search}%")
                        ->orWhereLike('city', "%{$search}%");
                });
            })
            ->when(in_array($this->status, ['active', 'deceased'], true), fn ($query) => $query->where('alumni_status', $this->status))
            ->orderBy('full_name')
            ->paginate(12);
    }

    public function photoUrl(?string $path): ?string
    {
        return filled($path) ? Storage::disk('public')->url($path) : null;
    }
}; ?>

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="space-y-2">
            <flux:heading size="xl">{{ __('Dulu & Sekarang') }}</flux:heading>
            <flux:text class="max-w-3xl">{{ __('Wajah-wajah Geodesi 96 dari masa kuliah hingga hari ini.') }}</flux:text>
        </div>

        <div class="grid gap-3 sm:grid-cols-[minmax(14rem,1fr)_11rem] lg:w-[34rem]">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :label="__('Cari')" :placeholder="__('Nama, panggilan, atau kota')" />
            <flux:select wire:model.live="status" :label="__('Status')">
                <flux:select.option value="all">{{ __('Semua') }}</flux:select.option>
                <flux:select.option value="active">{{ __('Aktif') }}</flux:select.option>
                <flux:select.option value="deceased">{{ __('In Memoriam') }}</flux:select.option>
            </flux:select>
        </div>
    </div>

    <div class="grid gap-5 xl:grid-cols-2">
        @forelse ($this->alumniProfiles as $profile)
            <article wire:key="then-now-{{ $profile->id }}" class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="grid grid-cols-2">
                    @foreach ([['label' => __('Dulu'), 'path' => $profile->college_photo_path], ['label' => __('Sekarang'), 'path' => $profile->current_photo_path]] as $photo)
                        <div class="relative aspect-[4/5] overflow-hidden bg-ktn-topo">
                            @if ($this->photoUrl($photo['path']))
                                <img src="{{ $this->photoUrl($photo['path']) }}" alt="{{ $photo['label'] }} — {{ $profile->full_name }}" class="size-full object-cover">
                            @else
                                <div class="flex size-full flex-col items-center justify-center gap-2 bg-zinc-100 px-4 text-center text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                    <flux:icon.photo class="size-8" />
                                    <span class="text-sm">{{ __('Foto belum tersedia') }}</span>
                                </div>
                            @endif
                            <span class="absolute bottom-3 left-3 rounded-full bg-black/65 px-3 py-1 text-xs font-semibold text-white">{{ $photo['label'] }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-between gap-4 p-5">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <flux:heading size="lg" class="truncate">{{ $profile->full_name }}</flux:heading>
                            @if ($profile->alumni_status === 'deceased')
                                <flux:badge color="zinc">{{ __('In Memoriam') }}</flux:badge>
                            @endif
                        </div>
                        <flux:text class="truncate">{{ collect([$profile->nickname, $profile->city, $profile->country])->filter()->join(' / ') ?: __('Alumni Geodesi 96') }}</flux:text>
                    </div>

                    <flux:button size="sm" variant="primary" icon="arrow-right" :href="route('alumni.directory.show', $profile)" wire:navigate>
                        {{ __('Profil') }}
                    </flux:button>
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-zinc-300 p-10 text-center xl:col-span-2 dark:border-zinc-700">
                <flux:heading size="lg">{{ __('Belum ada foto yang cocok') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Alumni akan tampil setelah minimal satu foto profil tersedia.') }}</flux:text>
            </div>
        @endforelse
    </div>

    <flux:pagination :paginator="$this->alumniProfiles" scroll-to="body" />
</section>
