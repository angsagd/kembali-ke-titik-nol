<?php

use App\Models\MediaItem;
use App\Models\AuditLog;
use Flux\Flux;
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

    #[Url]
    public string $status = 'active';

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
            'photos' => MediaItem::query()->where('type', 'photo')->count(),
            'videos' => MediaItem::query()->where('type', 'video')->count(),
            'public' => MediaItem::query()->where('visibility', 'public')->count(),
            'internal' => MediaItem::query()->where('visibility', 'internal')->count(),
            'archived' => MediaItem::onlyTrashed()->count(),
        ];
    }

    #[Computed]
    public function mediaItems(): LengthAwarePaginator
    {
        $search = trim($this->search);

        return MediaItem::query()
            ->withTrashed()
            ->with(['uploader', 'taggedAlumni'])
            ->when(in_array($this->type, ['photo', 'video'], true), function ($query): void {
                $query->where('type', $this->type);
            })
            ->when(in_array($this->visibility, ['internal', 'public'], true), function ($query): void {
                $query->where('visibility', $this->visibility);
            })
            ->when($this->status === 'active', function ($query): void {
                $query->whereNull('deleted_at');
            })
            ->when($this->status === 'archived', function ($query): void {
                $query->onlyTrashed();
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

    public function setVisibility(int $mediaItemId, string $visibility): void
    {
        abort_unless(in_array($visibility, ['internal', 'public'], true), 422);

        $mediaItem = MediaItem::withTrashed()->findOrFail($mediaItemId);
        $oldValues = $mediaItem->only(['visibility']);

        $mediaItem->update(['visibility' => $visibility]);

        AuditLog::record(
            action: 'media.visibility_updated',
            entity: $mediaItem,
            oldValues: $oldValues,
            newValues: $mediaItem->only(['visibility']),
        );

        unset($this->summary, $this->mediaItems);

        Flux::toast(variant: 'success', text: __('Visibilitas dokumentasi diperbarui.'));
    }

    public function restoreMedia(int $mediaItemId): void
    {
        $mediaItem = MediaItem::onlyTrashed()->findOrFail($mediaItemId);
        $mediaItem->restore();

        AuditLog::record(
            action: 'media.restored',
            entity: $mediaItem,
            newValues: $mediaItem->only(['title', 'visibility']),
        );

        unset($this->summary, $this->mediaItems);

        Flux::toast(variant: 'success', text: __('Dokumentasi dipulihkan.'));
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

    public function statusLabel(mixed $deletedAt): string
    {
        return $deletedAt ? __('Diarsipkan') : __('Aktif');
    }
}; ?>

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="space-y-2">
        <flux:heading size="xl">{{ __('Manajemen Dokumentasi') }}</flux:heading>
        <flux:text class="max-w-3xl">
            {{ __('Pantau dokumentasi foto dan video yang diunggah alumni.') }}
        </flux:text>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-[minmax(18rem,1fr)_10rem_10rem_10rem]">
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

        <flux:select wire:model.live="status" :label="__('Status')">
            <flux:select.option value="active">{{ __('Aktif') }}</flux:select.option>
            <flux:select.option value="archived">{{ __('Diarsipkan') }}</flux:select.option>
            <flux:select.option value="all">{{ __('Semua') }}</flux:select.option>
        </flux:select>
    </div>

    <div class="grid gap-4 md:grid-cols-5">
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
        <flux:card>
            <flux:text>{{ __('Diarsipkan') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['archived'] }}</div>
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
            <flux:table.column>{{ __('Status') }}</flux:table.column>
            <flux:table.column>{{ __('Tag') }}</flux:table.column>
            <flux:table.column>{{ __('Aksi') }}</flux:table.column>
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
                    <flux:table.cell>
                        <flux:badge color="{{ $mediaItem->trashed() ? 'amber' : 'green' }}">{{ $this->statusLabel($mediaItem->deleted_at) }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>{{ $mediaItem->taggedAlumni->pluck('full_name')->join(', ') ?: '-' }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex flex-wrap gap-2">
                            @if ($mediaItem->visibility === 'public')
                                <flux:button size="sm" variant="ghost" wire:click="setVisibility({{ $mediaItem->id }}, 'internal')">{{ __('Internal') }}</flux:button>
                            @else
                                <flux:button size="sm" variant="primary" wire:click="setVisibility({{ $mediaItem->id }}, 'public')">{{ __('Publik') }}</flux:button>
                            @endif

                            @if ($mediaItem->trashed())
                                <flux:button size="sm" variant="primary" icon="arrow-path" wire:click="restoreMedia({{ $mediaItem->id }})">{{ __('Pulihkan') }}</flux:button>
                            @endif
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="9">
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
