<?php

use App\Models\Alumni;
use App\Models\AuditLog;
use App\Models\MediaItem;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

new #[Title('Dokumentasi')] class extends Component {
    use WithFileUploads;

    public string $type = 'photo';

    public ?TemporaryUploadedFile $photo = null;

    public ?string $video_url = null;

    public ?string $title = null;

    public ?string $description = null;

    public int|string|null $month = null;

    public int|string $year;

    public string $visibility = 'internal';

    /** @var array<int, int|string> */
    public array $tagged_alumni_ids = [];

    public string $alumni_tag_search = '';

    #[Url]
    public string $view = 'all';

    public function mount(): void
    {
        $this->year = (int) now()->year;
    }

    #[Computed]
    public function currentAlumni(): Alumni
    {
        return Auth::user()->alumni()->firstOrFail();
    }

    #[Computed]
    public function mediaItems(): Collection
    {
        $alumni = $this->currentAlumni;

        return MediaItem::query()
            ->with(['uploader', 'taggedAlumni'])
            ->when($this->view === 'uploaded', function ($query) use ($alumni): void {
                $query->where('uploaded_by_alumni_id', $alumni->id);
            })
            ->when($this->view === 'tagged', function ($query) use ($alumni): void {
                $query->whereHas('taggedAlumni', fn ($query) => $query->whereKey($alumni->id));
            })
            ->latest()
            ->limit(24)
            ->get();
    }

    public function updatedView(): void
    {
        if (! in_array($this->view, ['all', 'uploaded', 'tagged'], true)) {
            $this->view = 'all';
        }

        unset($this->mediaItems);
    }

    #[Computed]
    public function alumniTagSuggestions(): Collection
    {
        $search = trim($this->alumni_tag_search);

        if ($search === '') {
            return new Collection;
        }

        return Alumni::query()
            ->where('alumni_status', 'active')
            ->whereNotIn('id', array_map('intval', $this->tagged_alumni_ids))
            ->where(function ($query) use ($search): void {
                $query
                    ->whereLike('full_name', "%{$search}%")
                    ->orWhereLike('nickname', "%{$search}%");
            })
            ->orderBy('full_name')
            ->limit(8)
            ->get(['id', 'full_name', 'nickname']);
    }

    #[Computed]
    public function selectedTaggedAlumni(): Collection
    {
        $selectedIds = array_values(array_unique(array_map('intval', $this->tagged_alumni_ids)));

        if ($selectedIds === []) {
            return new Collection;
        }

        return Alumni::query()
            ->whereKey($selectedIds)
            ->get(['id', 'full_name'])
            ->sortBy(fn (Alumni $alumni): int => array_search($alumni->id, $selectedIds, true))
            ->values();
    }

    public function addTaggedAlumni(int $alumniId): void
    {
        Alumni::query()
            ->where('alumni_status', 'active')
            ->findOrFail($alumniId);

        $this->tagged_alumni_ids = array_values(array_unique([
            ...array_map('intval', $this->tagged_alumni_ids),
            $alumniId,
        ]));
        $this->alumni_tag_search = '';

        unset($this->alumniTagSuggestions, $this->selectedTaggedAlumni);
    }

    public function removeTaggedAlumni(int $alumniId): void
    {
        $this->tagged_alumni_ids = array_values(array_filter(
            array_map('intval', $this->tagged_alumni_ids),
            fn (int $selectedId): bool => $selectedId !== $alumniId,
        ));

        unset($this->alumniTagSuggestions, $this->selectedTaggedAlumni);
    }

    public function updatedType(): void
    {
        $this->photo = null;
        $this->video_url = null;
        $this->resetErrorBag();
    }

    public function saveMedia(): void
    {
        $rules = [
            'type' => ['required', Rule::in(['photo', 'video'])],
            'title' => [$this->type === 'video' ? 'required' : 'nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:5000'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'visibility' => ['required', Rule::in(['internal', 'public'])],
            'tagged_alumni_ids' => ['array'],
            'tagged_alumni_ids.*' => [Rule::exists(Alumni::class, 'id')],
        ];

        if ($this->type === 'photo') {
            $rules['photo'] = ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];
            $rules['video_url'] = ['nullable'];
        } else {
            $rules['photo'] = ['nullable'];
            $rules['video_url'] = ['required', 'url', 'max:500'];
        }

        $validated = $this->validate($rules);
        $uploader = Auth::user()->alumni()->firstOrFail();

        $filePath = null;
        $fileSize = null;
        $width = null;
        $height = null;

        if ($validated['type'] === 'photo' && $this->photo) {
            $filePath = $this->photo->store('documentation/photos', 'public');
            $fileSize = $this->photo->getSize();
            $size = @getimagesize($this->photo->getRealPath());
            $width = is_array($size) ? $size[0] : null;
            $height = is_array($size) ? $size[1] : null;
        }

        $mediaItem = MediaItem::query()->create([
            'type' => $validated['type'],
            'uploaded_by_alumni_id' => $uploader->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'file_path' => $filePath,
            'video_url' => $validated['type'] === 'video' ? $validated['video_url'] : null,
            'provider' => $validated['type'] === 'video' ? $this->videoProvider($validated['video_url']) : null,
            'month' => $validated['month'],
            'year' => $validated['year'],
            'visibility' => $validated['visibility'],
            'file_size' => $fileSize,
            'width' => $width,
            'height' => $height,
        ]);

        $mediaItem->taggedAlumni()->syncWithPivotValues(
            array_values(array_unique(array_map('intval', $validated['tagged_alumni_ids'] ?? []))),
            ['tagged_by_alumni_id' => $uploader->id],
        );

        AuditLog::record(
            action: 'media.uploaded',
            entity: $mediaItem,
            newValues: $mediaItem->only([
                'type',
                'uploaded_by_alumni_id',
                'title',
                'visibility',
                'provider',
                'year',
            ]),
        );

        $this->reset(['photo', 'video_url', 'title', 'description', 'month', 'tagged_alumni_ids', 'alumni_tag_search']);
        $this->type = 'photo';
        $this->visibility = 'internal';
        $this->year = (int) now()->year;
        unset($this->mediaItems);

        Flux::toast(variant: 'success', text: __('Dokumentasi disimpan.'));
    }

    public function deleteMedia(int $mediaItemId): void
    {
        $mediaItem = MediaItem::query()
            ->whereKey($mediaItemId)
            ->firstOrFail();

        abort_unless($mediaItem->uploaded_by_alumni_id === $this->currentAlumni->id, 403);

        $oldValues = $mediaItem->only(['title', 'visibility', 'uploaded_by_alumni_id']);
        $mediaItem->delete();

        AuditLog::record(
            action: 'media.deleted',
            entity: $mediaItem,
            oldValues: $oldValues,
        );

        unset($this->mediaItems);

        Flux::toast(variant: 'success', text: __('Dokumentasi dipindahkan ke arsip.'));
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
            default => __('Video'),
        };
    }

    public function fileSizeLabel(?int $fileSize): ?string
    {
        if ($fileSize === null) {
            return null;
        }

        return number_format($fileSize / 1024, 0, ',', '.').' KB';
    }

    public function viewLabel(): string
    {
        return match ($this->view) {
            'uploaded' => __('Unggahan Saya'),
            'tagged' => __('Tag Saya'),
            default => __('Semua Dokumentasi'),
        };
    }

    private function videoProvider(string $videoUrl): string
    {
        $host = parse_url($videoUrl, PHP_URL_HOST);
        $host = is_string($host) ? strtolower($host) : '';

        return match (true) {
            str_contains($host, 'youtube.com'), str_contains($host, 'youtu.be') => 'youtube',
            str_contains($host, 'drive.google.com') => 'google_drive',
            default => 'other',
        };
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

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="space-y-2">
            <flux:heading size="xl">{{ __('Dokumentasi') }}</flux:heading>
            <flux:text class="max-w-3xl">
                {{ __('Unggah foto dokumentasi atau tambahkan tautan video eksternal untuk arsip reuni.') }}
            </flux:text>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[26rem_1fr]">
        <form wire:submit="saveMedia" class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="space-y-5">
                <div>
                    <flux:heading size="lg">{{ __('Tambah Dokumentasi') }}</flux:heading>
                    <flux:text class="mt-2">{{ __('Foto disimpan di sistem, video disimpan sebagai tautan eksternal.') }}</flux:text>
                </div>

                <flux:select wire:model.live="type" :label="__('Jenis')">
                    <flux:select.option value="photo">{{ __('Foto') }}</flux:select.option>
                    <flux:select.option value="video">{{ __('Video') }}</flux:select.option>
                </flux:select>

                @if ($type === 'photo')
                    <flux:input wire:model="photo" :label="__('File foto')" type="file" accept="image/jpeg,image/png,image/webp" />
                @else
                    <flux:input wire:model="video_url" :label="__('URL video')" placeholder="https://youtube.com/..." />
                @endif

                <flux:input wire:model="title" :label="__('Judul')" />
                <flux:textarea wire:model="description" :label="__('Deskripsi')" rows="3" />

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:select wire:model="month" :label="__('Bulan')">
                        <flux:select.option value="">{{ __('Tidak diisi') }}</flux:select.option>
                        @foreach (range(1, 12) as $monthNumber)
                            <flux:select.option value="{{ $monthNumber }}">{{ $this->monthName($monthNumber) }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input wire:model="year" :label="__('Tahun')" type="number" min="1900" max="2100" />
                </div>

                <flux:select wire:model="visibility" :label="__('Visibilitas')">
                    <flux:select.option value="internal">{{ __('Internal') }}</flux:select.option>
                    <flux:select.option value="public">{{ __('Publik') }}</flux:select.option>
                </flux:select>

                <div>
                    <div class="relative">
                        <flux:input
                            wire:model.live.debounce.250ms="alumni_tag_search"
                            :label="__('Tag alumni')"
                            icon="magnifying-glass"
                            :placeholder="__('Ketik nama alumni')"
                            autocomplete="off"
                        />

                        @if (trim($alumni_tag_search) !== '')
                            <div class="absolute z-20 mt-2 max-h-64 w-full overflow-y-auto rounded-lg border border-zinc-200 bg-white py-1 shadow-lg">
                                @forelse ($this->alumniTagSuggestions as $profile)
                                    <button
                                        type="button"
                                        wire:key="alumni-tag-suggestion-{{ $profile->id }}"
                                        wire:click="addTaggedAlumni({{ $profile->id }})"
                                        class="flex w-full items-center justify-between gap-3 px-3 py-2 text-left text-sm hover:bg-amber-50 focus:bg-amber-50 focus:outline-none"
                                    >
                                        <span class="font-medium text-zinc-900">{{ $profile->full_name }}</span>
                                        @if ($profile->nickname)
                                            <span class="text-xs text-zinc-500">{{ $profile->nickname }}</span>
                                        @endif
                                    </button>
                                @empty
                                    <div class="px-3 py-2 text-sm text-zinc-500">{{ __('Alumni tidak ditemukan.') }}</div>
                                @endforelse
                            </div>
                        @endif
                    </div>

                    @if ($this->selectedTaggedAlumni->isNotEmpty())
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($this->selectedTaggedAlumni as $profile)
                                <flux:badge wire:key="selected-alumni-tag-{{ $profile->id }}" color="amber">
                                    {{ $profile->full_name }}
                                    <flux:badge.close
                                        wire:click="removeTaggedAlumni({{ $profile->id }})"
                                        :aria-label="__('Hapus tag :name', ['name' => $profile->full_name])"
                                    />
                                </flux:badge>
                            @endforeach
                        </div>
                    @endif

                    <flux:error name="tagged_alumni_ids" />
                </div>

                <flux:button type="submit" variant="primary" icon="check" class="w-full" wire:loading.attr="disabled">
                    {{ __('Simpan Dokumentasi') }}
                </flux:button>
            </div>
        </form>

        <div class="space-y-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <flux:heading size="lg">{{ $this->viewLabel() }}</flux:heading>
                    <flux:text>{{ __('Galeri privat alumni untuk arsip foto dan video angkatan.') }}</flux:text>
                </div>

                <div class="flex flex-wrap gap-2">
                    <flux:button size="sm" variant="{{ $view === 'all' ? 'primary' : 'ghost' }}" wire:click="$set('view', 'all')">
                        {{ __('Semua') }}
                    </flux:button>
                    <flux:button size="sm" variant="{{ $view === 'uploaded' ? 'primary' : 'ghost' }}" wire:click="$set('view', 'uploaded')">
                        {{ __('Unggahan Saya') }}
                    </flux:button>
                    <flux:button size="sm" variant="{{ $view === 'tagged' ? 'primary' : 'ghost' }}" wire:click="$set('view', 'tagged')">
                        {{ __('Tag Saya') }}
                    </flux:button>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($this->mediaItems as $mediaItem)
                    <article
                        class="group relative aspect-video overflow-hidden rounded-lg border border-zinc-200 bg-zinc-100 shadow-sm dark:border-zinc-700 dark:bg-zinc-800"
                        wire:key="media-item-{{ $mediaItem->id }}"
                    >
                        @if ($mediaItem->isPhoto())
                            @if ($mediaItem->displayUrl())
                                <img
                                    src="{{ $mediaItem->displayUrl() }}"
                                    alt="{{ $mediaItem->title ?: __('Foto dokumentasi') }}"
                                    class="relative z-0 size-full object-cover transition duration-300 group-hover:scale-[1.02] group-focus-within:scale-[1.02]"
                                    loading="lazy"
                                >
                            @endif
                        @else
                            <div class="flex size-full flex-col items-center justify-center gap-2 bg-zinc-200 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                                <flux:icon.play-circle class="size-12" />
                                <span class="text-sm font-medium">{{ $this->providerLabel($mediaItem->provider) }}</span>
                            </div>
                        @endif

                        <div
                            class="pointer-events-none absolute inset-x-2 top-2 z-10 translate-y-1 rounded-md bg-zinc-950/80 p-3 text-white opacity-0 shadow-lg transition duration-200 group-hover:translate-y-0 group-hover:opacity-100 group-focus-within:translate-y-0 group-focus-within:opacity-100"
                            data-documentation-gallery-metadata
                        >
                            <h3 class="line-clamp-2 text-sm font-semibold">{{ $mediaItem->title ?: __('Tanpa judul') }}</h3>
                            <p class="mt-1 text-xs text-zinc-200">{{ $mediaItem->uploader?->full_name }}</p>
                            <p class="mt-1 text-xs text-zinc-300">
                                {{ collect([$this->monthName($mediaItem->month), $mediaItem->year])->filter()->join(' ') }}
                            </p>
                        </div>

                        <div class="absolute bottom-2 right-2 z-20">
                            <flux:button size="sm" variant="primary" icon="eye" :href="route('documentation.show', $mediaItem)" wire:navigate>
                                {{ __('Detail') }}
                            </flux:button>
                        </div>
                    </article>
                @empty
                    <div class="rounded-lg border border-dashed border-zinc-300 p-8 text-center md:col-span-2 xl:col-span-3 dark:border-zinc-700">
                        <flux:heading size="lg">{{ __('Belum ada dokumentasi') }}</flux:heading>
                        <flux:text class="mt-2">{{ __('Dokumentasi yang diunggah alumni akan tampil di sini.') }}</flux:text>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>
