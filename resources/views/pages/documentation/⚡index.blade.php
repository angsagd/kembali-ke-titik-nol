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

    public function mount(): void
    {
        $this->year = (int) now()->year;
    }

    #[Computed]
    public function mediaItems(): Collection
    {
        return MediaItem::query()
            ->with(['uploader', 'taggedAlumni'])
            ->latest()
            ->limit(24)
            ->get();
    }

    #[Computed]
    public function alumniOptions(): Collection
    {
        return Alumni::query()
            ->where('alumni_status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'student_number']);
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

        $this->reset(['photo', 'video_url', 'title', 'description', 'month', 'tagged_alumni_ids']);
        $this->type = 'photo';
        $this->visibility = 'internal';
        $this->year = (int) now()->year;
        unset($this->mediaItems);

        Flux::toast(variant: 'success', text: __('Dokumentasi disimpan.'));
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

                <flux:select wire:model="tagged_alumni_ids" :label="__('Tag alumni')" multiple>
                    @foreach ($this->alumniOptions as $profile)
                        <flux:select.option value="{{ $profile->id }}">
                            {{ $profile->full_name }}{{ $profile->student_number ? ' - '.$profile->student_number : '' }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <flux:button type="submit" variant="primary" icon="check" class="w-full" wire:loading.attr="disabled">
                    {{ __('Simpan Dokumentasi') }}
                </flux:button>
            </div>
        </form>

        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Galeri Terbaru') }}</flux:heading>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($this->mediaItems as $mediaItem)
                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900" wire:key="media-item-{{ $mediaItem->id }}">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-medium">{{ $mediaItem->title ?: __('Tanpa judul') }}</div>
                                <flux:text>{{ $mediaItem->uploader?->full_name }}</flux:text>
                            </div>
                            <div class="flex gap-2">
                                <flux:badge>{{ $this->typeLabel($mediaItem->type) }}</flux:badge>
                                <flux:badge color="{{ $mediaItem->visibility === 'public' ? 'green' : 'zinc' }}">{{ $this->visibilityLabel($mediaItem->visibility) }}</flux:badge>
                            </div>
                        </div>

                        @if ($mediaItem->isPhoto())
                            <div class="mt-4 aspect-video overflow-hidden rounded-md bg-zinc-100 dark:bg-zinc-800">
                                @if ($mediaItem->displayUrl())
                                    <img src="{{ $mediaItem->displayUrl() }}" alt="{{ $mediaItem->title ?: __('Foto dokumentasi') }}" class="size-full object-cover">
                                @endif
                            </div>
                        @else
                            <div class="mt-4 rounded-md border border-zinc-200 p-3 dark:border-zinc-700">
                                <a href="{{ $mediaItem->video_url }}" target="_blank" rel="noopener" class="font-medium text-amber-700 dark:text-amber-300">
                                    {{ __('Buka video') }}
                                </a>
                                <flux:text class="mt-1">{{ $this->providerLabel($mediaItem->provider) }}</flux:text>
                            </div>
                        @endif

                        <div class="mt-4 space-y-2">
                            <flux:text>{{ collect([$this->monthName($mediaItem->month), $mediaItem->year])->filter()->join(' ') }}</flux:text>
                            @if ($mediaItem->isPhoto() && $mediaItem->file_size)
                                <flux:text>{{ __('Ukuran: :size', ['size' => $this->fileSizeLabel($mediaItem->file_size)]) }}</flux:text>
                            @endif
                            @if ($mediaItem->description)
                                <flux:text>{{ $mediaItem->description }}</flux:text>
                            @endif
                            @if ($mediaItem->taggedAlumni->isNotEmpty())
                                <flux:text>{{ __('Tag: :names', ['names' => $mediaItem->taggedAlumni->pluck('full_name')->join(', ')]) }}</flux:text>
                            @endif
                        </div>
                    </div>
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
