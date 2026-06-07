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
            'link_poster' => __('Paling Sering Berbagi Link'),
            'image_poster' => __('Paling Sering Berbagi Media'),
            'silent_reader' => __('Silent Reader'),
            'busiest_year' => __('Tahun Paling Ramai'),
            'busiest_month' => __('Bulan Paling Ramai'),
            'busiest_hour' => __('Jam Paling Ramai'),
            'word_cloud' => __('Nostalgia Word Cloud'),
            default => $category,
        };
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

        <div class="grid gap-4 xl:grid-cols-2">
            @foreach (['active_member', 'link_poster', 'image_poster', 'silent_reader'] as $category)
                <flux:card class="space-y-4">
                    <flux:heading size="lg">{{ $this->categoryLabel($category) }}</flux:heading>
                    <div class="space-y-3">
                        @forelse ($this->stats($category) as $statistic)
                            <div class="flex items-center justify-between gap-4 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700" wire:key="{{ $category }}-{{ $statistic->id }}">
                                <div class="min-w-0">
                                    <div class="truncate font-medium">{{ $statistic->label ?: '-' }}</div>
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

        <div class="grid gap-4 xl:grid-cols-3">
            @foreach (['busiest_year', 'busiest_month', 'busiest_hour'] as $category)
                <flux:card class="space-y-4">
                    <flux:heading size="lg">{{ $this->categoryLabel($category) }}</flux:heading>
                    <div class="space-y-3">
                        @forelse ($this->stats($category, 6) as $statistic)
                            <div class="flex items-center justify-between gap-4" wire:key="{{ $category }}-{{ $statistic->id }}">
                                <span>{{ $statistic->label }}</span>
                                <span class="font-semibold tabular-nums">{{ (int) $statistic->value }}</span>
                            </div>
                        @empty
                            <flux:text>{{ __('Belum ada data.') }}</flux:text>
                        @endforelse
                    </div>
                </flux:card>
            @endforeach
        </div>

        <flux:card class="space-y-4">
            <flux:heading size="lg">{{ $this->categoryLabel('word_cloud') }}</flux:heading>
            <div class="flex flex-wrap gap-3">
                @forelse ($this->stats('word_cloud', 30) as $statistic)
                    <span class="rounded-lg bg-amber-100 px-3 py-2 text-sm font-semibold text-amber-900 dark:bg-amber-400/20 dark:text-amber-100" wire:key="word-{{ $statistic->id }}">
                        {{ $statistic->label }} <span class="tabular-nums opacity-70">{{ (int) $statistic->value }}</span>
                    </span>
                @empty
                    <flux:text>{{ __('Word cloud akan muncul setelah import diproses.') }}</flux:text>
                @endforelse
            </div>
        </flux:card>
    @else
        <flux:card class="space-y-3">
            <flux:heading size="lg">{{ __('Belum ada WhatsApp Analytics') }}</flux:heading>
            <flux:text>{{ __('Statistik akan tersedia setelah panitia mengunggah dan memproses file export WhatsApp.') }}</flux:text>
        </flux:card>
    @endif
</section>
