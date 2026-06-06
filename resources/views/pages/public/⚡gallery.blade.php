<?php

use App\Models\MediaItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::public')] #[Title('Galeri Publik')] class extends Component {
    use WithPagination;

    #[Url]
    public string $type = 'all';

    #[Url]
    public string $year = 'all';

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function updatedYear(): void
    {
        $this->resetPage();
    }

    /**
     * @return Collection<int, int>
     */
    #[Computed]
    public function years(): Collection
    {
        return MediaItem::query()
            ->where('visibility', 'public')
            ->whereNotNull('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');
    }

    #[Computed]
    public function mediaItems(): LengthAwarePaginator
    {
        return MediaItem::query()
            ->with(['uploader', 'taggedAlumni'])
            ->where('visibility', 'public')
            ->when(in_array($this->type, ['photo', 'video'], true), function ($query): void {
                $query->where('type', $this->type);
            })
            ->when($this->year !== 'all', function ($query): void {
                $query->where('year', (int) $this->year);
            })
            ->latest()
            ->paginate(12);
    }

    public function typeLabel(string $type): string
    {
        return $type === 'video' ? __('Video') : __('Foto');
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

<main class="min-h-screen bg-ktn-topo">
    <header class="border-b border-ktn-sage/20 bg-ktn-surface/90 backdrop-blur-xl">
        <nav class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <span class="grid size-9 place-items-center rounded-lg border border-ktn-forest/20 bg-white text-ktn-forest">
                    <span class="size-4 rounded-full border-2 border-ktn-forest outline outline-1 outline-offset-4 outline-ktn-forest/35"></span>
                </span>
                <span class="font-display text-lg font-extrabold tracking-tight text-ktn-forest">Geodesi 96</span>
            </a>

            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}#galeri" class="hidden font-mono text-xs font-semibold uppercase tracking-[0.18em] text-ktn-muted transition hover:text-ktn-forest sm:inline">Landing</a>
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="rounded-lg bg-ktn-forest px-5 py-2.5 text-sm font-bold text-white transition hover:bg-ktn-forest-strong">
                        Login
                    </a>
                @endif
            </div>
        </nav>
    </header>

    <section class="px-4 py-12 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="space-y-3">
                    <p class="font-mono text-xs font-semibold uppercase tracking-[0.22em] text-ktn-forest">Galeri Publik</p>
                    <h1 class="font-display text-4xl font-extrabold tracking-tight text-ktn-forest sm:text-5xl">Dokumentasi Kembali ke Titik Nol</h1>
                    <p class="max-w-2xl text-base leading-8 text-ktn-muted">
                        Foto dan video yang telah ditandai publik oleh alumni dan panitia.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 lg:w-[28rem]">
                    <flux:select wire:model.live="type" :label="__('Jenis')">
                        <flux:select.option value="all">{{ __('Semua') }}</flux:select.option>
                        <flux:select.option value="photo">{{ __('Foto') }}</flux:select.option>
                        <flux:select.option value="video">{{ __('Video') }}</flux:select.option>
                    </flux:select>

                    <flux:select wire:model.live="year" :label="__('Tahun')">
                        <flux:select.option value="all">{{ __('Semua') }}</flux:select.option>
                        @foreach ($this->years as $yearOption)
                            <flux:select.option value="{{ $yearOption }}">{{ $yearOption }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </div>

            <div class="mt-10 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($this->mediaItems as $mediaItem)
                    <article class="overflow-hidden rounded-xl border border-ktn-sage/20 bg-white shadow-sm" wire:key="public-media-{{ $mediaItem->id }}">
                        <div class="aspect-video bg-ktn-forest/10">
                            @if ($mediaItem->isPhoto() && $mediaItem->displayUrl())
                                <a href="{{ $mediaItem->displayUrl() }}" target="_blank" rel="noopener">
                                    <img src="{{ $mediaItem->displayUrl() }}" alt="{{ $mediaItem->title ?: __('Foto dokumentasi') }}" class="size-full object-cover">
                                </a>
                            @else
                                <div class="grid size-full place-items-center bg-ktn-forest text-center text-white">
                                    <div class="space-y-3 px-6">
                                        <flux:badge color="amber">{{ $this->typeLabel($mediaItem->type) }}</flux:badge>
                                        <p class="font-display text-xl font-bold">{{ $mediaItem->title ?: __('Video Dokumentasi') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="space-y-4 p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div class="space-y-1">
                                    <h2 class="font-display text-xl font-bold text-ktn-forest">{{ $mediaItem->title ?: __('Tanpa judul') }}</h2>
                                    <p class="text-sm text-ktn-muted">{{ collect([$this->monthName($mediaItem->month), $mediaItem->year])->filter()->join(' ') }}</p>
                                </div>
                                <flux:badge color="{{ $mediaItem->isPhoto() ? 'green' : 'amber' }}">{{ $this->typeLabel($mediaItem->type) }}</flux:badge>
                            </div>

                            @if ($mediaItem->description)
                                <p class="leading-7 text-ktn-muted">{{ $mediaItem->description }}</p>
                            @endif

                            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-ktn-sage/20 pt-4">
                                <p class="text-sm text-ktn-muted">{{ $mediaItem->uploader?->full_name }}</p>
                                @if ($mediaItem->isPhoto())
                                    <a href="{{ $mediaItem->displayUrl() }}" target="_blank" rel="noopener" class="font-mono text-xs font-semibold uppercase tracking-[0.18em] text-ktn-forest">
                                        {{ __('Lihat Foto') }}
                                    </a>
                                @elseif ($mediaItem->video_url)
                                    <a href="{{ $mediaItem->video_url }}" target="_blank" rel="noopener" class="font-mono text-xs font-semibold uppercase tracking-[0.18em] text-ktn-forest">
                                        {{ __('Buka Video') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl border border-ktn-sage/20 bg-white p-8 text-center md:col-span-2 xl:col-span-3">
                        <h2 class="font-display text-2xl font-bold text-ktn-forest">{{ __('Belum ada dokumentasi publik') }}</h2>
                        <p class="mt-2 text-ktn-muted">{{ __('Dokumentasi akan tampil setelah panitia atau alumni menandainya sebagai publik.') }}</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $this->mediaItems->links() }}
            </div>
        </div>
    </section>
</main>
