<?php

use App\Models\Alumni;
use App\Models\MediaItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Detail Buku Kenangan')] class extends Component {
    public Alumni $alumni;

    public function mount(Alumni $alumni): void
    {
        $this->alumni = $alumni->load(['currentCity', 'currentCountry', 'user']);
    }

    #[Computed]
    public function mediaItems(): Collection
    {
        return MediaItem::query()
            ->with(['uploader', 'taggedAlumni'])
            ->where(function ($query): void {
                $query
                    ->where('uploaded_by_alumni_id', $this->alumni->id)
                    ->orWhereHas('taggedAlumni', fn ($query) => $query->whereKey($this->alumni->id));
            })
            ->latest()
            ->limit(9)
            ->get();
    }

    #[Computed]
    public function timelines(): Collection
    {
        return $this->alumni
            ->timelines()
            ->with(['city', 'country'])
            ->limit(6)
            ->get();
    }

    public function profilePhotoUrl(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
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

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="space-y-2">
            <flux:button variant="ghost" icon="arrow-left" :href="route('memory-book.index')" wire:navigate>
                {{ __('Kembali ke Buku Kenangan') }}
            </flux:button>
            <flux:heading size="xl">{{ $alumni->full_name }}</flux:heading>
            <flux:text>
                {{ collect([$alumni->nickname ? __('Panggilan: :nickname', ['nickname' => $alumni->nickname]) : null, $alumni->currentCity?->name, $alumni->currentCountry?->name])->filter()->join(' / ') ?: __('Buku kenangan alumni Geodesi 96') }}
            </flux:text>
        </div>

        <div class="flex flex-wrap gap-2">
            <flux:badge color="{{ $alumni->alumni_status === 'active' ? 'green' : 'zinc' }}">
                {{ $alumni->alumni_status === 'active' ? __('Aktif') : __('Memorial') }}
            </flux:badge>
            <flux:button size="sm" variant="ghost" icon="identification" :href="route('alumni.directory.show', $alumni)" wire:navigate>
                {{ __('Profil Direktori') }}
            </flux:button>
        </div>
    </div>

    @if ($alumni->alumni_status === 'deceased')
        <flux:callout icon="heart" color="zinc">
            <flux:callout.heading>{{ __('Halaman Memorial') }}</flux:callout.heading>
            <flux:callout.text>
                {{ __('Profil ini dipertahankan sebagai arsip kenangan bersama. Dokumentasi yang menandai alumni ini tetap ditampilkan sebagai bagian dari buku kenangan.') }}
            </flux:callout.text>
        </flux:callout>
    @endif

    <div class="grid gap-6 xl:grid-cols-[1fr_24rem]">
        <div class="space-y-6">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="aspect-[4/5] bg-ktn-topo">
                        @if ($this->profilePhotoUrl($alumni->college_photo_path))
                            <img src="{{ $this->profilePhotoUrl($alumni->college_photo_path) }}" alt="{{ __('Foto masa kuliah :name', ['name' => $alumni->full_name]) }}" class="size-full object-cover">
                        @else
                            <div class="flex size-full items-center justify-center bg-ktn-instrument text-center text-sm font-medium text-ktn-muted">
                                {{ __('Foto masa kuliah belum diisi') }}
                            </div>
                        @endif
                    </div>
                    <div class="p-4">
                        <flux:heading size="lg">{{ __('Masa Kuliah') }}</flux:heading>
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="aspect-[4/5] bg-ktn-topo">
                        @if ($this->profilePhotoUrl($alumni->current_photo_path))
                            <img src="{{ $this->profilePhotoUrl($alumni->current_photo_path) }}" alt="{{ __('Foto saat ini :name', ['name' => $alumni->full_name]) }}" class="size-full object-cover">
                        @else
                            <div class="flex size-full items-center justify-center bg-ktn-forest text-5xl font-semibold text-white">
                                {{ collect(explode(' ', $alumni->full_name))->filter()->map(fn (string $name): string => mb_substr($name, 0, 1))->take(2)->join('') }}
                            </div>
                        @endif
                    </div>
                    <div class="p-4">
                        <flux:heading size="lg">{{ __('Saat Ini') }}</flux:heading>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg">{{ __('Cerita Singkat') }}</flux:heading>
                <p class="mt-4 whitespace-pre-line leading-7 text-zinc-700 dark:text-zinc-200">{{ $alumni->short_story ?: __('Belum ada cerita singkat.') }}</p>
            </div>

            <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg">{{ __('Kenangan Masa Kuliah') }}</flux:heading>
                <p class="mt-4 whitespace-pre-line leading-7 text-zinc-700 dark:text-zinc-200">{{ $alumni->memorable_story ?: __('Belum ada kenangan yang ditulis.') }}</p>
            </div>

            <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg">{{ __('Pesan untuk Teman Alumni') }}</flux:heading>
                <p class="mt-4 whitespace-pre-line leading-7 text-zinc-700 dark:text-zinc-200">{{ $alumni->message_to_friends ?: __('Belum ada pesan untuk teman alumni.') }}</p>
            </div>

            <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <flux:heading size="lg">{{ __('Dokumentasi Terkait') }}</flux:heading>
                        <flux:text>{{ __('Foto/video yang diunggah oleh alumni ini atau menandai alumni ini.') }}</flux:text>
                    </div>
                    <flux:badge>{{ $this->mediaItems->count() }}</flux:badge>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-3">
                    @forelse ($this->mediaItems as $mediaItem)
                        <article wire:key="memory-media-{{ $mediaItem->id }}" class="rounded-md border border-zinc-200 p-3 dark:border-zinc-700">
                            @if ($mediaItem->isPhoto())
                                <div class="aspect-video overflow-hidden rounded bg-zinc-100 dark:bg-zinc-800">
                                    @if ($mediaItem->displayUrl())
                                        <img src="{{ $mediaItem->displayUrl() }}" alt="{{ $mediaItem->title ?: __('Foto dokumentasi') }}" class="size-full object-cover">
                                    @endif
                                </div>
                            @else
                                <div class="flex aspect-video items-center justify-center rounded bg-ktn-forest text-sm font-semibold text-white">
                                    {{ __('Video') }}
                                </div>
                            @endif

                            <div class="mt-3 space-y-1">
                                <div class="font-medium">{{ $mediaItem->title ?: __('Tanpa judul') }}</div>
                                <flux:text>{{ $this->typeLabel($mediaItem->type) }} / {{ $mediaItem->year }}</flux:text>
                                <flux:button size="sm" variant="ghost" icon="eye" :href="route('documentation.show', $mediaItem)" wire:navigate>
                                    {{ __('Detail') }}
                                </flux:button>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-md border border-dashed border-zinc-300 p-6 text-center md:col-span-3 dark:border-zinc-700">
                            <flux:text>{{ __('Belum ada dokumentasi terkait.') }}</flux:text>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg">{{ __('Informasi Alumni') }}</flux:heading>
                <dl class="mt-5 grid gap-4">
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('NIM') }}</dt>
                        <dd class="font-medium">{{ $alumni->student_number ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Nomor WhatsApp') }}</dt>
                        <dd class="font-medium">{{ $alumni->user?->whatsapp_number ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Email') }}</dt>
                        <dd class="font-medium">{{ $alumni->email ?: $alumni->user?->email ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Instansi / Perusahaan') }}</dt>
                        <dd class="font-medium">{{ $alumni->company ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Jabatan / Pekerjaan') }}</dt>
                        <dd class="font-medium">{{ $alumni->job_title ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Domisili') }}</dt>
                        <dd class="font-medium">{{ collect([$alumni->currentCity?->name, $alumni->currentCountry?->name])->filter()->join(', ') ?: '-' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg">{{ __('Timeline Ringkas') }}</flux:heading>
                <div class="mt-5 grid gap-3">
                    @forelse ($this->timelines as $timeline)
                        <div wire:key="memory-timeline-{{ $timeline->id }}" class="rounded-md border border-zinc-200 p-3 dark:border-zinc-700">
                            <div class="font-semibold">{{ $timeline->month ? $this->monthName($timeline->month).' ' : '' }}{{ $timeline->year }}</div>
                            <flux:text>{{ collect([$timeline->city?->name, $timeline->country?->name])->filter()->join(', ') ?: __('Lokasi belum diisi') }}</flux:text>
                        </div>
                    @empty
                        <flux:text>{{ __('Timeline belum diisi.') }}</flux:text>
                    @endforelse
                </div>
            </div>
        </aside>
    </div>
</section>
