<?php

use App\Models\Alumni;
use App\Models\AlumniTimeline;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Persebaran Alumni')] class extends Component {
    #[Url(as: 'city')]
    public ?string $selectedCityId = null;

    #[Computed]
    public function summary(): array
    {
        $total = Alumni::query()->count();
        $located = Alumni::query()
            ->whereNotNull('city')
            ->orWhereNotNull('country')
            ->count();
        $completed = Alumni::query()->where('is_profile_completed', true)->count();

        return [
            'total' => $total,
            'located' => $located,
            'completed' => $completed,
            'attending' => Alumni::query()->where('rsvp_status', 'attending')->count(),
            'pending' => Alumni::query()->where('rsvp_status', 'pending')->count(),
            'not_attending' => Alumni::query()->where('rsvp_status', 'not_attending')->count(),
            'active' => Alumni::query()->where('alumni_status', 'active')->count(),
            'deceased' => Alumni::query()->where('alumni_status', 'deceased')->count(),
            'timeline_entries' => AlumniTimeline::query()->count(),
            'timeline_alumni' => AlumniTimeline::query()->distinct('alumni_id')->count('alumni_id'),
        ];
    }

    #[Computed]
    public function countryDistribution(): Collection
    {
        return Alumni::query()
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->select('country')
            ->selectRaw('count(*) as alumni_count')
            ->groupBy('country')
            ->orderByDesc('alumni_count')
            ->orderBy('country')
            ->get();
    }

    #[Computed]
    public function cityDistribution(): Collection
    {
        return Alumni::query()
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->select(['city', 'country'])
            ->selectRaw('count(*) as alumni_count')
            ->groupBy('city', 'country')
            ->orderByDesc('alumni_count')
            ->orderBy('city')
            ->limit(20)
            ->get();
    }

    #[Computed]
    public function cityMarkers(): Collection
    {
        return Alumni::query()
            ->whereNotNull('city')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->select(['city', 'country', 'latitude', 'longitude'])
            ->selectRaw('count(*) as alumni_count')
            ->groupBy('city', 'country', 'latitude', 'longitude')
            ->orderByDesc('alumni_count')
            ->orderBy('city')
            ->get();
    }

    #[Computed]
    public function selectedCity(): ?object
    {
        if (! filled($this->selectedCityId)) {
            return $this->cityMarkers->first();
        }

        return $this->cityMarkers->first(
            fn (object $location): bool => $this->locationKey($location->city, $location->country) === $this->selectedCityId,
        );
    }

    #[Computed]
    public function selectedCityAlumni(): Collection
    {
        if (! $this->selectedCity) {
            return collect();
        }

        return Alumni::query()
            ->where('city', $this->selectedCity->city)
            ->where('country', $this->selectedCity->country)
            ->orderBy('full_name')
            ->get();
    }

    #[Computed]
    public function timelineYearDistribution(): Collection
    {
        return AlumniTimeline::query()
            ->select('year')
            ->selectRaw('count(*) as timeline_count')
            ->groupBy('year')
            ->orderBy('year')
            ->get();
    }

    #[Computed]
    public function timelineCityDistribution(): Collection
    {
        return AlumniTimeline::query()
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->select(['city', 'country'])
            ->selectRaw('count(*) as timeline_count')
            ->groupBy('city', 'country')
            ->orderByDesc('timeline_count')
            ->orderBy('city')
            ->limit(12)
            ->get();
    }

    #[Computed]
    public function unlocatedCount(): int
    {
        return Alumni::query()
            ->whereNull('city')
            ->whereNull('country')
            ->count();
    }

    public function percentage(int $count): int
    {
        if ($this->summary['total'] === 0) {
            return 0;
        }

        return (int) round(($count / $this->summary['total']) * 100);
    }

    public function percentageOfMax(int $count, int $max): int
    {
        if ($max === 0) {
            return 0;
        }

        return (int) round(($count / $max) * 100);
    }

    public function selectCity(string $cityId): void
    {
        $this->selectedCityId = $cityId;
        unset($this->selectedCity, $this->selectedCityAlumni);
    }

    /**
     * @return list<array{id: string, name: string, country: string, count: int, latitude: float, longitude: float, selected: bool}>
     */
    public function leafletMarkers(): array
    {
        return $this->cityMarkers
            ->map(fn (object $location): array => [
                'id' => $this->locationKey($location->city, $location->country),
                'name' => $location->city,
                'country' => $location->country ?: __('Negara belum diisi'),
                'count' => (int) $location->alumni_count,
                'latitude' => (float) $location->latitude,
                'longitude' => (float) $location->longitude,
                'selected' => $this->selectedCity !== null
                    && $this->locationKey($this->selectedCity->city, $this->selectedCity->country) === $this->locationKey($location->city, $location->country),
            ])
            ->values()
            ->all();
    }

    private function locationKey(?string $city, ?string $country): string
    {
        return implode('::', [$city, $country]);
    }
}; ?>

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="space-y-2">
            <flux:heading size="xl">{{ __('Persebaran Alumni') }}</flux:heading>
            <flux:text class="max-w-3xl">
                {{ __('Ringkasan awal persebaran alumni berdasarkan domisili saat ini, status RSVP, dan kelengkapan profil.') }}
            </flux:text>
        </div>

        <flux:button variant="ghost" icon="users" :href="route('alumni.directory.index')" wire:navigate>
            {{ __('Buka Direktori') }}
        </flux:button>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:text>{{ __('Total alumni') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['total'] }}</div>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:text>{{ __('Sudah mengisi domisili') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['located'] }}</div>
            <flux:progress class="mt-4" :value="$this->percentage($this->summary['located'])" />
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:text>{{ __('Profil lengkap') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['completed'] }}</div>
            <flux:progress class="mt-4" :value="$this->percentage($this->summary['completed'])" color="green" />
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:text>{{ __('Belum ada domisili') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->unlocatedCount }}</div>
            <flux:progress class="mt-4" :value="$this->percentage($this->unlocatedCount)" color="amber" />
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:text>{{ __('Catatan timeline') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['timeline_entries'] }}</div>
            <flux:text class="mt-2">{{ __(':count alumni sudah mengisi', ['count' => $this->summary['timeline_alumni']]) }}</flux:text>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_24rem]">
        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <flux:heading size="lg">{{ __('Peta Persebaran') }}</flux:heading>
                    <flux:text>{{ __('Marker kota berdasarkan koordinat hasil pencarian eksternal dan jumlah alumni pada domisili saat ini.') }}</flux:text>
                </div>
                <flux:badge>{{ __(':count marker kota', ['count' => $this->cityMarkers->count()]) }}</flux:badge>
            </div>

            <div class="relative mt-5 aspect-[16/9] overflow-hidden rounded-lg border border-ktn-instrument bg-ktn-topo">
                @if ($this->cityMarkers->isNotEmpty())
                    <div
                        wire:ignore
                        data-leaflet-distribution-map
                        data-markers='@json($this->leafletMarkers())'
                        class="size-full"
                    ></div>
                @else
                    <div class="absolute inset-0 flex items-center justify-center p-6 text-center">
                        <flux:text>{{ __('Belum ada kota dengan koordinat dan alumni domisili.') }}</flux:text>
                    </div>
                @endif
            </div>
        </div>

        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <flux:heading size="lg">{{ __('Detail Lokasi') }}</flux:heading>
                    <flux:text>{{ __('Klik marker kota untuk melihat alumni pada lokasi tersebut.') }}</flux:text>
                </div>
                @if ($this->selectedCity)
                    <flux:badge>{{ $this->selectedCity->alumni_count }}</flux:badge>
                @endif
            </div>

            @if ($this->selectedCity)
                <div class="mt-5 space-y-4">
                    <div class="rounded-md border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="font-semibold">{{ $this->selectedCity->city }}</div>
                        <flux:text>{{ $this->selectedCity->country ?: __('Negara belum diisi') }}</flux:text>
                        <flux:text class="mt-2">
                            {{ __('Koordinat: :lat, :lng', ['lat' => $this->selectedCity->latitude, 'lng' => $this->selectedCity->longitude]) }}
                        </flux:text>
                    </div>

                    <div class="grid gap-3">
                        @foreach ($this->selectedCityAlumni as $profile)
                            <a wire:key="city-alumni-{{ $profile->id }}" href="{{ route('alumni.directory.show', $profile) }}" wire:navigate class="rounded-md border border-zinc-200 p-3 transition hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">
                                <div class="font-medium">{{ $profile->full_name }}</div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                    {{ collect([$profile->company, $profile->job_title])->filter()->join(' / ') ?: __('Profil alumni') }}
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="mt-5 rounded-md border border-dashed border-zinc-300 p-6 text-center dark:border-zinc-700">
                    <flux:text>{{ __('Belum ada lokasi yang dapat ditampilkan.') }}</flux:text>
                </div>
            @endif
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_1fr]">
        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg">{{ __('Status RSVP') }}</flux:heading>
            <div class="mt-5 grid gap-4">
                @foreach ([
                    ['label' => __('Hadir'), 'count' => $this->summary['attending'], 'color' => 'green'],
                    ['label' => __('Pending'), 'count' => $this->summary['pending'], 'color' => 'amber'],
                    ['label' => __('Tidak hadir'), 'count' => $this->summary['not_attending'], 'color' => 'red'],
                ] as $row)
                    <div wire:key="rsvp-{{ $row['label'] }}" class="space-y-2">
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span>{{ $row['label'] }}</span>
                            <span class="font-medium tabular-nums">{{ $row['count'] }} / {{ $this->percentage($row['count']) }}%</span>
                        </div>
                        <flux:progress :value="$this->percentage($row['count'])" :color="$row['color']" />
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg">{{ __('Status Alumni') }}</flux:heading>
            <div class="mt-5 grid gap-4">
                @foreach ([
                    ['label' => __('Aktif'), 'count' => $this->summary['active'], 'color' => 'green'],
                    ['label' => __('Memorial'), 'count' => $this->summary['deceased'], 'color' => 'zinc'],
                ] as $row)
                    <div wire:key="alumni-status-{{ $row['label'] }}" class="space-y-2">
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span>{{ $row['label'] }}</span>
                            <span class="font-medium tabular-nums">{{ $row['count'] }} / {{ $this->percentage($row['count']) }}%</span>
                        </div>
                        <flux:progress :value="$this->percentage($row['count'])" :color="$row['color']" />
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_1fr]">
        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <flux:heading size="lg">{{ __('Lintasan Tahun') }}</flux:heading>
                    <flux:text>{{ __('Agregasi catatan lokasi alumni berdasarkan tahun.') }}</flux:text>
                </div>
                <flux:badge>{{ $this->summary['timeline_entries'] }}</flux:badge>
            </div>

            @php
                $maxYearCount = (int) $this->timelineYearDistribution->max('timeline_count');
            @endphp

            <div class="mt-5 grid gap-4">
                @forelse ($this->timelineYearDistribution as $row)
                    <div wire:key="timeline-year-{{ $row->year }}" class="space-y-2">
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <span class="font-medium tabular-nums">{{ $row->year }}</span>
                            <span class="text-zinc-600 tabular-nums dark:text-zinc-300">{{ $row->timeline_count }} {{ __('catatan') }}</span>
                        </div>
                        <flux:progress :value="$this->percentageOfMax((int) $row->timeline_count, $maxYearCount)" />
                    </div>
                @empty
                    <div class="rounded-md border border-dashed border-zinc-300 p-6 text-center dark:border-zinc-700">
                        <flux:text>{{ __('Belum ada catatan timeline lokasi.') }}</flux:text>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <flux:heading size="lg">{{ __('Titik Historis Teratas') }}</flux:heading>
                    <flux:text>{{ __('Kota yang paling sering muncul dalam timeline lokasi.') }}</flux:text>
                </div>
                <flux:badge>{{ $this->timelineCityDistribution->count() }}</flux:badge>
            </div>

            @php
                $maxTimelineCityCount = (int) $this->timelineCityDistribution->max('timeline_count');
            @endphp

            <div class="mt-5 grid gap-4">
                @forelse ($this->timelineCityDistribution as $city)
                    <a wire:key="timeline-city-{{ $city->city }}-{{ $city->country }}" href="{{ route('alumni.directory.index', ['q' => $city->city]) }}" wire:navigate class="block rounded-md border border-zinc-200 p-4 transition hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="font-medium">{{ $city->city }}</div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $city->country }}</div>
                            </div>
                            <flux:badge>{{ $city->timeline_count }}</flux:badge>
                        </div>
                        <flux:progress class="mt-3" :value="$this->percentageOfMax((int) $city->timeline_count, $maxTimelineCityCount)" color="green" />
                    </a>
                @empty
                    <div class="rounded-md border border-dashed border-zinc-300 p-6 text-center dark:border-zinc-700">
                        <flux:text>{{ __('Belum ada kota dalam timeline lokasi.') }}</flux:text>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_1fr]">
        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg">{{ __('Per Negara') }}</flux:heading>

            <div class="mt-5 grid gap-4">
                @forelse ($this->countryDistribution as $country)
                    <a wire:key="country-{{ $country->country }}" href="{{ route('alumni.directory.index', ['q' => $country->country]) }}" wire:navigate class="block rounded-md border border-zinc-200 p-4 transition hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-medium">{{ $country->country }}</span>
                            <flux:badge>{{ $country->alumni_count }}</flux:badge>
                        </div>
                        <flux:progress class="mt-3" :value="$this->percentage($country->alumni_count)" />
                    </a>
                @empty
                    <flux:text>{{ __('Belum ada alumni dengan negara domisili.') }}</flux:text>
                @endforelse
            </div>
        </div>

        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg">{{ __('Per Kota') }}</flux:heading>

            <div class="mt-5 grid gap-4">
                @forelse ($this->cityDistribution as $city)
                    <a wire:key="city-{{ $city->city }}-{{ $city->country }}" href="{{ route('alumni.directory.index', ['q' => $city->city]) }}" wire:navigate class="block rounded-md border border-zinc-200 p-4 transition hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-medium">{{ $city->city }}</span>
                            <flux:badge>{{ $city->alumni_count }}</flux:badge>
                        </div>
                        <div class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $city->country }}</div>
                        <flux:progress class="mt-3" :value="$this->percentage($city->alumni_count)" />
                    </a>
                @empty
                    <flux:text>{{ __('Belum ada alumni dengan kota domisili.') }}</flux:text>
                @endforelse
            </div>
        </div>
    </div>
</section>
