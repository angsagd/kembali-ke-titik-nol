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

new #[Title('Detail Dokumentasi')] class extends Component {
    public MediaItem $mediaItem;

    public ?string $title = null;

    public ?string $description = null;

    public int|string|null $month = null;

    public int|string $year;

    public string $visibility = 'internal';

    /** @var array<int, int|string> */
    public array $tagged_alumni_ids = [];

    public function mount(MediaItem $mediaItem): void
    {
        $this->mediaItem = $mediaItem->load(['uploader', 'taggedAlumni']);
        $this->fillForm();
    }

    #[Computed]
    public function currentAlumni(): Alumni
    {
        return Auth::user()->alumni()->firstOrFail();
    }

    #[Computed]
    public function alumniOptions(): Collection
    {
        return Alumni::query()
            ->where('alumni_status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'student_number']);
    }

    public function isUploader(): bool
    {
        return $this->mediaItem->uploaded_by_alumni_id === $this->currentAlumni->id;
    }

    public function saveDetails(): void
    {
        abort_unless($this->isUploader(), 403);

        $validated = $this->validate([
            'title' => [$this->mediaItem->type === 'video' ? 'required' : 'nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:5000'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:1900', 'max:2100'],
            'visibility' => ['required', Rule::in(['internal', 'public'])],
            'tagged_alumni_ids' => ['array'],
            'tagged_alumni_ids.*' => [Rule::exists(Alumni::class, 'id')],
        ]);

        $oldValues = $this->mediaItem->only(['title', 'description', 'month', 'year', 'visibility']);

        $this->mediaItem->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'month' => $validated['month'],
            'year' => $validated['year'],
            'visibility' => $validated['visibility'],
        ]);

        $this->mediaItem->taggedAlumni()->syncWithPivotValues(
            array_values(array_unique(array_map('intval', $validated['tagged_alumni_ids'] ?? []))),
            ['tagged_by_alumni_id' => $this->currentAlumni->id],
        );

        $this->mediaItem = $this->mediaItem->fresh(['uploader', 'taggedAlumni']);
        $this->fillForm();

        AuditLog::record(
            action: 'media.updated',
            entity: $this->mediaItem,
            oldValues: $oldValues,
            newValues: $this->mediaItem->only(['title', 'description', 'month', 'year', 'visibility']),
        );

        Flux::toast(variant: 'success', text: __('Detail dokumentasi diperbarui.'));
    }

    public function deleteMedia(): void
    {
        abort_unless($this->isUploader(), 403);

        $oldValues = $this->mediaItem->only(['title', 'visibility', 'uploaded_by_alumni_id']);
        $this->mediaItem->delete();

        AuditLog::record(
            action: 'media.deleted',
            entity: $this->mediaItem,
            oldValues: $oldValues,
        );

        $this->redirectRoute('documentation.index', navigate: true);
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

    private function fillForm(): void
    {
        $this->title = $this->mediaItem->title;
        $this->description = $this->mediaItem->description;
        $this->month = $this->mediaItem->month;
        $this->year = $this->mediaItem->year;
        $this->visibility = $this->mediaItem->visibility;
        $this->tagged_alumni_ids = $this->mediaItem->taggedAlumni->pluck('id')->all();
    }
}; ?>

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-2">
            <flux:button variant="ghost" icon="arrow-left" :href="route('documentation.index')" wire:navigate>
                {{ __('Kembali ke Galeri') }}
            </flux:button>
            <flux:heading size="xl">{{ $mediaItem->title ?: __('Dokumentasi Tanpa Judul') }}</flux:heading>
            <flux:text>
                {{ __('Diunggah oleh :name', ['name' => $mediaItem->uploader?->full_name ?: '-']) }}
            </flux:text>
        </div>

        <div class="flex flex-wrap gap-2">
            <flux:badge>{{ $this->typeLabel($mediaItem->type) }}</flux:badge>
            <flux:badge color="{{ $mediaItem->visibility === 'public' ? 'green' : 'zinc' }}">
                {{ $this->visibilityLabel($mediaItem->visibility) }}
            </flux:badge>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_24rem]">
        <div class="space-y-6">
            <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                @if ($mediaItem->isPhoto())
                    <div class="bg-zinc-100 dark:bg-zinc-800">
                        @if ($mediaItem->displayUrl())
                            <img src="{{ $mediaItem->displayUrl() }}" alt="{{ $mediaItem->title ?: __('Foto dokumentasi') }}" class="max-h-[70vh] w-full object-contain">
                        @endif
                    </div>
                @else
                    <div class="p-6">
                        <flux:heading size="lg">{{ __('Video Eksternal') }}</flux:heading>
                        <flux:text class="mt-2">{{ $this->providerLabel($mediaItem->provider) }}</flux:text>
                        <flux:button class="mt-4" variant="primary" icon="arrow-top-right-on-square" href="{{ $mediaItem->video_url }}" target="_blank" rel="noopener">
                            {{ __('Buka Video') }}
                        </flux:button>
                    </div>
                @endif
            </div>

            <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg">{{ __('Cerita Dokumentasi') }}</flux:heading>
                <p class="mt-4 whitespace-pre-line text-sm text-zinc-700 dark:text-zinc-200">
                    {{ $mediaItem->description ?: __('Belum ada deskripsi.') }}
                </p>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg">{{ __('Metadata') }}</flux:heading>
                <dl class="mt-5 grid gap-4">
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Waktu') }}</dt>
                        <dd class="font-medium">{{ collect([$this->monthName($mediaItem->month), $mediaItem->year])->filter()->join(' ') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Tag alumni') }}</dt>
                        <dd class="font-medium">{{ $mediaItem->taggedAlumni->pluck('full_name')->join(', ') ?: '-' }}</dd>
                    </div>
                </dl>
            </div>

            @if ($this->isUploader())
                <form wire:submit="saveDetails" class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="space-y-5">
                        <flux:heading size="lg">{{ __('Edit Dokumentasi') }}</flux:heading>

                        <flux:input wire:model="title" :label="__('Judul')" />
                        <flux:textarea wire:model="description" :label="__('Deskripsi')" rows="4" />

                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
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

                        <div class="flex flex-wrap gap-2">
                            <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled">
                                {{ __('Simpan') }}
                            </flux:button>
                            <flux:button type="button" variant="danger" icon="trash" wire:click="deleteMedia" wire:confirm="{{ __('Pindahkan dokumentasi ini ke arsip?') }}">
                                {{ __('Arsipkan') }}
                            </flux:button>
                        </div>
                    </div>
                </form>
            @endif
        </aside>
    </div>
</section>
