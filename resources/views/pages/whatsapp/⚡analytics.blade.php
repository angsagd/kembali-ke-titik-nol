<?php

use App\Models\WhatsappImport;
use App\Models\WhatsappStatistic;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('WhatsApp Analytics')] class extends Component {
    #[Computed]
    public function latestImport(): ?WhatsappImport
    {
        return WhatsappImport::query()
            ->where('status', 'completed')
            ->latest('processed_at')
            ->first();
    }

    /**
     * @return Collection<int, WhatsappStatistic>
     */
    public function stats(string $category, int $limit = 10): Collection
    {
        if ($this->latestImport === null) {
            return new Collection();
        }

        return WhatsappStatistic::query()
            ->with('alumni')
            ->where('whatsapp_import_id', $this->latestImport->id)
            ->where('category', $category)
            ->orderBy('rank')
            ->limit($limit)
            ->get();
    }

    public function categoryLabel(string $category): string
    {
        return match ($category) {
            'active_member' => __('Member Paling Aktif'),
            'silent_reader' => __('Silent Reader'),
            'link_poster' => __('Paling Sering Berbagi Link'),
            'image_poster' => __('Paling Sering Berbagi Media'),
            'nocturnal_chatter' => __('Nocturnal Chatter'),
            'work_time_chatter' => __('Work Time Chatter'),
            'weekend_warrior' => __('Weekend Warrior'),
            'emoji_champion' => __('Emoji Champion'),
            'busiest_year' => __('Tahun Paling Ramai'),
            'busiest_month' => __('Bulan Paling Ramai'),
            'busiest_hour' => __('Jam Paling Ramai'),
            'top_topic' => __('Topik Populer'),
            'word_cloud' => __('Nostalgia Word Cloud'),
            default => $category,
        };
    }

    /**
     * @return array<int, string>
     */
    public function hallOfFameCategories(): array
    {
        return [
            'active_member',
            'silent_reader',
            'link_poster',
            'image_poster',
            'nocturnal_chatter',
            'work_time_chatter',
            'weekend_warrior',
            'emoji_champion',
        ];
    }

    /**
     * @return array<int, string>
     */
    public function activityCategories(): array
    {
        return ['busiest_year', 'busiest_month', 'busiest_hour'];
    }

    public function statisticLabel(WhatsappStatistic $statistic): string
    {
        if ($statistic->category === 'busiest_hour') {
            return $statistic->label.'.00';
        }

        return $statistic->label ?: '-';
    }
}; ?>

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="space-y-2">
            <flux:heading size="xl">{{ __('WhatsApp Analytics') }}</flux:heading>
            <flux:text class="max-w-3xl">
                {{ __('Statistik agregat grup WhatsApp alumni. Sistem tidak menampilkan isi percakapan mentah.') }}
            </flux:text>
        </div>

        @can('import-whatsapp-analytics')
            <flux:button variant="ghost" :href="route('admin.whatsapp.index')" wire:navigate>
                {{ __('Kelola Import') }}
            </flux:button>
        @endcan
    </div>

    @if ($this->latestImport)
        <div class="grid gap-4 md:grid-cols-4">
            <flux:card>
                <flux:text>{{ __('Total Pesan') }}</flux:text>
                <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->latestImport->total_messages }}</div>
            </flux:card>
            <flux:card>
                <flux:text>{{ __('Partisipan') }}</flux:text>
                <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->latestImport->total_participants }}</div>
            </flux:card>
            <flux:card>
                <flux:text>{{ __('Periode Awal') }}</flux:text>
                <div class="mt-2 text-2xl font-semibold tabular-nums">{{ $this->latestImport->import_start_date?->format('Y-m-d') ?: '-' }}</div>
            </flux:card>
            <flux:card>
                <flux:text>{{ __('Periode Akhir') }}</flux:text>
                <div class="mt-2 text-2xl font-semibold tabular-nums">{{ $this->latestImport->import_end_date?->format('Y-m-d') ?: '-' }}</div>
            </flux:card>
        </div>

        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Hall of Fame') }}</flux:heading>

            <div class="grid gap-4 xl:grid-cols-2">
                @foreach ($this->hallOfFameCategories() as $category)
                    <flux:card class="space-y-4">
                        <flux:heading size="lg">{{ $this->categoryLabel($category) }}</flux:heading>
                        <div class="space-y-3">
                            @forelse ($this->stats($category) as $statistic)
                                <div class="flex items-center justify-between gap-4 rounded-lg border border-zinc-200 p-3" wire:key="{{ $category }}-{{ $statistic->id }}">
                                    <div class="min-w-0">
                                        <div class="truncate font-medium">{{ $this->statisticLabel($statistic) }}</div>
                                        @if ($statistic->alumni)
                                            <flux:text>{{ $statistic->alumni->full_name }}</flux:text>
                                        @endif
                                    </div>
                                    <div class="text-right font-semibold tabular-nums">{{ (int) $statistic->value }}</div>
                                </div>
                            @empty
                                <flux:text>{{ __('Belum ada data untuk kategori ini.') }}</flux:text>
                            @endforelse
                        </div>
                    </flux:card>
                @endforeach
            </div>
        </div>

        <div class="space-y-4">
            <flux:heading size="lg">{{ __('Statistik Grup') }}</flux:heading>

            <div class="grid gap-4 xl:grid-cols-3">
                @foreach ($this->activityCategories() as $category)
                    <flux:card class="space-y-4">
                        <flux:heading size="lg">{{ $this->categoryLabel($category) }}</flux:heading>
                        <div class="space-y-3">
                            @forelse ($this->stats($category, 6) as $statistic)
                                <div class="flex items-center justify-between gap-4" wire:key="{{ $category }}-{{ $statistic->id }}">
                                    <span>{{ $this->statisticLabel($statistic) }}</span>
                                    <span class="font-semibold tabular-nums">{{ (int) $statistic->value }}</span>
                                </div>
                            @empty
                                <flux:text>{{ __('Belum ada data.') }}</flux:text>
                            @endforelse
                        </div>
                    </flux:card>
                @endforeach
            </div>
        </div>

        <div class="grid gap-4 xl:grid-cols-[1fr_1.35fr]">
            <flux:card class="space-y-4">
                <flux:heading size="lg">{{ $this->categoryLabel('top_topic') }}</flux:heading>
                <div class="space-y-3">
                    @forelse ($this->stats('top_topic', 10) as $statistic)
                        <div class="flex items-center justify-between gap-4" wire:key="topic-{{ $statistic->id }}">
                            <span class="font-medium">{{ $this->statisticLabel($statistic) }}</span>
                            <span class="font-semibold tabular-nums">{{ (int) $statistic->value }}</span>
                        </div>
                    @empty
                        <flux:text>{{ __('Topik populer akan muncul setelah import diproses.') }}</flux:text>
                    @endforelse
                </div>
            </flux:card>

            <flux:card class="space-y-4">
                <flux:heading size="lg">{{ $this->categoryLabel('word_cloud') }}</flux:heading>
                <div class="flex flex-wrap gap-3">
                    @forelse ($this->stats('word_cloud', 30) as $statistic)
                        <span class="rounded-lg bg-amber-100 px-3 py-2 text-sm font-semibold text-amber-900" wire:key="word-{{ $statistic->id }}">
                            {{ $this->statisticLabel($statistic) }} <span class="tabular-nums opacity-70">{{ (int) $statistic->value }}</span>
                        </span>
                    @empty
                        <flux:text>{{ __('Word cloud akan muncul setelah import diproses.') }}</flux:text>
                    @endforelse
                </div>
            </flux:card>
        </div>

        <flux:card class="space-y-4">
            <flux:heading size="lg">{{ __('Insight Historis') }}</flux:heading>
            <div class="grid gap-4 md:grid-cols-4">
                <div>
                    <flux:text>{{ __('File import') }}</flux:text>
                    <div class="mt-1 truncate font-medium">{{ $this->latestImport->file_name ?: '-' }}</div>
                </div>
                <div>
                    <flux:text>{{ __('Diproses') }}</flux:text>
                    <div class="mt-1 font-medium">{{ $this->latestImport->processed_at?->translatedFormat('d F Y H:i') ?: '-' }}</div>
                </div>
                <div>
                    <flux:text>{{ __('Rentang data') }}</flux:text>
                    <div class="mt-1 font-medium">
                        {{ collect([$this->latestImport->import_start_date?->format('Y-m-d'), $this->latestImport->import_end_date?->format('Y-m-d')])->filter()->join(' - ') ?: '-' }}
                    </div>
                </div>
                <div>
                    <flux:text>{{ __('Rasio pesan/partisipan') }}</flux:text>
                    <div class="mt-1 font-medium tabular-nums">
                        {{ $this->latestImport->total_participants > 0 ? number_format($this->latestImport->total_messages / $this->latestImport->total_participants, 1) : '0.0' }}
                    </div>
                </div>
            </div>
        </flux:card>
    @else
        <flux:card class="space-y-3">
            <flux:heading size="lg">{{ __('Belum ada WhatsApp Analytics') }}</flux:heading>
            <flux:text>{{ __('Statistik akan tersedia setelah panitia mengunggah dan memproses file export WhatsApp.') }}</flux:text>
        </flux:card>
    @endif
</section>
