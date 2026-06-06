<?php

use App\Models\MediaItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Manajemen Dokumentasi')] class extends Component {
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $type = 'all';

    #[Url]
    public string $visibility = 'all';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function updatedVisibility(): void
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
            'photos' => MediaItem::query()->where('type', 'photo')->count(),
            'videos' => MediaItem::query()->where('type', 'video')->count(),
            'public' => MediaItem::query()->where('visibility', 'public')->count(),
            'internal' => MediaItem::query()->where('visibility', 'internal')->count(),
        ];
    }

    #[Computed]
    public function mediaItems(): LengthAwarePaginator
    {
        $search = trim($this->search);

        return MediaItem::query()
            ->with(['uploader', 'taggedAlumni'])
            ->when(in_array($this->type, ['photo', 'video'], true), function ($query): void {
                $query->where('type', $this->type);
            })
            ->when(in_array($this->visibility, ['internal', 'public'], true), function ($query): void {
                $query->where('visibility', $this->visibility);
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('uploader', fn ($query) => $query->where('full_name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(15);
    }

    public function typeLabel(string $type): string
    {
        return $type === 'video' ? __('Video') : __('Foto');
    }

    public function visibilityLabel(string $visibility): string
    {
        return $visibility === 'public' ? __('Publik') : __('Internal');
    }

    public function providerLabel(?string $provider): string
    {
        return match ($provider) {
            'youtube' => __('YouTube'),
            'google_drive' => __('Google Drive'),
            'other' => __('Lainnya'),
            default => '-',
        };
    }

    public function fileSizeLabel(?int $fileSize): string
    {
        if ($fileSize === null) {
            return '-';
        }

        return number_format($fileSize / 1024, 0, ',', '.').' KB';
    }
}; ?>

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="space-y-2">
            <flux:heading size="xl">{{ __('Manajemen Dokumentasi') }}</flux:heading>
            <flux:text class="max-w-2xl">
                {{ __('Pantau dokumentasi foto dan video yang diunggah alumni.') }}
            </flux:text>
        </div>

        <div class="grid gap-3 sm:grid-cols-[minmax(14rem,1fr)_10rem_10rem] lg:w-[44rem]">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                :label="__('Cari')"
                :placeholder="__('Judul, deskripsi, uploader')"
            />

            <flux:select wire:model.live="type" :label="__('Jenis')">
                <flux:select.option value="all">{{ __('Semua') }}</flux:select.option>
                <flux:select.option value="photo">{{ __('Foto') }}</flux:select.option>
                <flux:select.option value="video">{{ __('Video') }}</flux:select.option>
            </flux:select>

            <flux:select wire:model.live="visibility" :label="__('Visibilitas')">
                <flux:select.option value="all">{{ __('Semua') }}</flux:select.option>
                <flux:select.option value="internal">{{ __('Internal') }}</flux:select.option>
                <flux:select.option value="public">{{ __('Publik') }}</flux:select.option>
            </flux:select>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <flux:card>
            <flux:text>{{ __('Total Foto') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['photos'] }}</div>
        </flux:card>
        <flux:card>
            <flux:text>{{ __('Total Video') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['videos'] }}</div>
        </flux:card>
        <flux:card>
            <flux:text>{{ __('Dokumentasi Publik') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['public'] }}</div>
        </flux:card>
        <flux:card>
            <flux:text>{{ __('Dokumentasi Internal') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['internal'] }}</div>
        </flux:card>
    </div>

    <flux:table :paginate="$this->mediaItems" pagination:scroll-to="body">
        <flux:table.columns>
            <flux:table.column>{{ __('Judul') }}</flux:table.column>
            <flux:table.column>{{ __('Jenis') }}</flux:table.column>
            <flux:table.column>{{ __('Uploader') }}</flux:table.column>
            <flux:table.column>{{ __('Tahun') }}</flux:table.column>
            <flux:table.column>{{ __('Metadata') }}</flux:table.column>
            <flux:table.column>{{ __('Visibilitas') }}</flux:table.column>
            <flux:table.column>{{ __('Tag') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->mediaItems as $mediaItem)
                <flux:table.row :key="$mediaItem->id">
                    <flux:table.cell variant="strong">
                        <div class="grid gap-1">
                            <span>{{ $mediaItem->title ?: __('Tanpa judul') }}</span>
                            <span class="text-xs font-normal text-zinc-500 dark:text-zinc-400">{{ $mediaItem->description ?: __('Deskripsi belum diisi') }}</span>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge>{{ $this->typeLabel($mediaItem->type) }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>{{ $mediaItem->uploader?->full_name ?: '-' }}</flux:table.cell>
                    <flux:table.cell>{{ $mediaItem->year }}</flux:table.cell>
                    <flux:table.cell>
                        {{ $mediaItem->isPhoto() ? $this->fileSizeLabel($mediaItem->file_size) : $this->providerLabel($mediaItem->provider) }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge color="{{ $mediaItem->visibility === 'public' ? 'green' : 'zinc' }}">{{ $this->visibilityLabel($mediaItem->visibility) }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>{{ $mediaItem->taggedAlumni->pluck('full_name')->join(', ') ?: '-' }}</flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7">
                        <div class="py-10 text-center">
                            <flux:heading size="lg">{{ __('Tidak ada dokumentasi ditemukan') }}</flux:heading>
                            <flux:text>{{ __('Dokumentasi yang diunggah alumni akan tampil di sini.') }}</flux:text>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</section>
