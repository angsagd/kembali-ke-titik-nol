<?php

use App\Models\Alumni;
use App\Models\AlumniTimeline;
use App\Models\City;
use App\Models\Country;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Timeline Lokasi')] class extends Component {
    public Alumni $alumni;

    public ?int $editing_timeline_id = null;

    public ?int $month = null;

    public int|string|null $year = null;

    public ?int $country_id = null;

    public ?int $city_id = null;

    public ?string $notes = null;

    public function mount(): void
    {
        $this->alumni = Auth::user()->alumni()
            ->with(['timelines.city.country', 'timelines.country'])
            ->firstOrFail();
    }

    public function updatedCountryId(): void
    {
        $this->city_id = null;
    }

    #[Computed]
    public function countries(): Collection
    {
        return Country::query()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    #[Computed]
    public function cities(): Collection
    {
        return City::query()
            ->when($this->country_id, fn ($query) => $query->where('country_id', $this->country_id))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    #[Computed]
    public function timelines(): Collection
    {
        return $this->alumni
            ->timelines()
            ->with(['city.country', 'country'])
            ->get();
    }

    public function saveTimeline(): void
    {
        $validated = $this->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:1996,2100'],
            'country_id' => ['nullable', Rule::exists(Country::class, 'id')],
            'city_id' => [
                'nullable',
                Rule::exists(City::class, 'id')
                    ->where(fn ($query) => $query->where('country_id', $this->country_id ?? 0)),
            ],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $timeline = $this->editing_timeline_id === null
            ? new AlumniTimeline(['alumni_id' => $this->alumni->id])
            : $this->alumni->timelines()->whereKey($this->editing_timeline_id)->firstOrFail();

        $city = filled($validated['city_id'] ?? null)
            ? City::query()->find($validated['city_id'])
            : null;
        $country = filled($validated['country_id'] ?? null)
            ? Country::query()->find($validated['country_id'])
            : null;

        $timeline->fill([
            ...$validated,
            'latitude' => $city?->latitude ?? $country?->latitude,
            'longitude' => $city?->longitude ?? $country?->longitude,
            'location_source' => 'geocoded',
        ])->save();

        $this->resetForm();
        unset($this->timelines);

        Flux::toast(variant: 'success', text: __('Timeline lokasi disimpan.'));
    }

    public function editTimeline(int $timelineId): void
    {
        $timeline = $this->alumni->timelines()->whereKey($timelineId)->firstOrFail();

        $this->editing_timeline_id = $timeline->id;
        $this->month = $timeline->month;
        $this->year = $timeline->year;
        $this->country_id = $timeline->country_id;
        $this->city_id = $timeline->city_id;
        $this->notes = $timeline->notes;
    }

    public function deleteTimeline(int $timelineId): void
    {
        $this->alumni->timelines()->whereKey($timelineId)->firstOrFail()->delete();

        if ($this->editing_timeline_id === $timelineId) {
            $this->resetForm();
        }

        unset($this->timelines);

        Flux::toast(variant: 'success', text: __('Timeline lokasi dihapus.'));
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

    public function resetForm(): void
    {
        $this->reset([
            'editing_timeline_id',
            'month',
            'year',
            'country_id',
            'city_id',
            'notes',
        ]);
    }
}; ?>

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="space-y-2">
            <flux:heading size="xl">{{ __('Timeline Lokasi') }}</flux:heading>
            <flux:text class="max-w-3xl">
                {{ __('Catat perjalanan lokasi dari masa kuliah sampai sekarang. Data ini akan menjadi dasar visualisasi perjalanan alumni.') }}
            </flux:text>
        </div>

        <flux:button variant="ghost" icon="identification" :href="route('alumni.profile')" wire:navigate>
            {{ __('Profil Saya') }}
        </flux:button>
    </div>

    <div class="grid gap-6 xl:grid-cols-[24rem_1fr]">
        <form wire:submit="saveTimeline" class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg">
                {{ $editing_timeline_id ? __('Ubah Lokasi') : __('Tambah Lokasi') }}
            </flux:heading>
            <flux:text class="mt-2">{{ __('Minimal isi tahun. Kota dan negara bisa dilengkapi jika sudah tersedia di master lokasi.') }}</flux:text>

            <div class="mt-5 grid gap-5">
                <flux:input wire:model="year" :label="__('Tahun')" type="number" min="1996" max="2100" required />

                <flux:select wire:model="month" :label="__('Bulan')">
                    <flux:select.option value="">{{ __('Tanpa bulan') }}</flux:select.option>
                    @foreach (range(1, 12) as $monthNumber)
                        <flux:select.option :value="$monthNumber">
                            {{ $this->monthName($monthNumber) }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="country_id" :label="__('Negara')">
                    <flux:select.option value="">{{ __('Belum diisi') }}</flux:select.option>
                    @foreach ($this->countries as $country)
                        <flux:select.option :value="$country->id">{{ $country->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="city_id" :label="__('Kota')" wire:key="timeline-city-{{ $country_id ?: 'none' }}">
                    <flux:select.option value="">{{ __('Belum diisi') }}</flux:select.option>
                    @foreach ($this->cities as $city)
                        <flux:select.option :value="$city->id">{{ $city->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="notes" :label="__('Catatan')" :placeholder="__('Kuliah, kerja pertama, pindah rumah, dll.')" />
            </div>

            <div class="mt-6 flex gap-3">
                <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled">
                    {{ $editing_timeline_id ? __('Simpan Perubahan') : __('Tambah') }}
                </flux:button>

                @if ($editing_timeline_id)
                    <flux:button type="button" variant="ghost" wire:click="resetForm">
                        {{ __('Batal') }}
                    </flux:button>
                @endif
            </div>
        </form>

        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <flux:heading size="lg">{{ __('Riwayat Lokasi') }}</flux:heading>
                    <flux:text>{{ __('Urutan waktu dari lokasi paling awal ke paling baru.') }}</flux:text>
                </div>
                <flux:badge>{{ $this->timelines->count() }}</flux:badge>
            </div>

            <div class="mt-6 grid gap-4">
                @forelse ($this->timelines as $timeline)
                    <article wire:key="timeline-{{ $timeline->id }}" class="rounded-md border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="space-y-1">
                                <div class="font-semibold">
                                    {{ $timeline->month ? $this->monthName($timeline->month).' ' : '' }}{{ $timeline->year }}
                                </div>
                                <div class="text-sm text-zinc-600 dark:text-zinc-300">
                                    {{ collect([$timeline->city?->name, $timeline->country?->name])->filter()->join(', ') ?: __('Lokasi belum diisi') }}
                                </div>
                                @if ($timeline->latitude !== null && $timeline->longitude !== null)
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ __('Koordinat: :lat, :lng', ['lat' => $timeline->latitude, 'lng' => $timeline->longitude]) }}
                                    </div>
                                @endif
                                @if ($timeline->notes)
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $timeline->notes }}</p>
                                @endif
                            </div>

                            <div class="flex gap-2">
                                <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="editTimeline({{ $timeline->id }})">
                                    {{ __('Edit') }}
                                </flux:button>
                                <flux:button size="sm" variant="danger" icon="trash" wire:click="deleteTimeline({{ $timeline->id }})" wire:confirm="{{ __('Hapus timeline lokasi ini?') }}">
                                    {{ __('Hapus') }}
                                </flux:button>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-md border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-700">
                        <flux:heading size="lg">{{ __('Belum ada riwayat lokasi') }}</flux:heading>
                        <flux:text>{{ __('Tambahkan lokasi pertama, misalnya 1996 - Yogyakarta.') }}</flux:text>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>
