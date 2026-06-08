<?php

use App\Models\Alumni;
use App\Models\City;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Buku Kenangan')] class extends Component {
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = 'all';

    #[Url(as: 'city')]
    public int|string|null $cityId = null;

    #[Url]
    public string $section = 'all';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedCityId(): void
    {
        $this->resetPage();
    }

    public function updatedSection(): void
    {
        if (! in_array($this->section, ['all', 'story', 'memory', 'message', 'memorial'], true)) {
            $this->section = 'all';
        }

        $this->resetPage();
    }

    #[Computed]
    public function alumniProfiles(): LengthAwarePaginator
    {
        $search = trim($this->search);

        return Alumni::query()
            ->with(['currentCity', 'currentCountry'])
            ->withCount(['uploadedMediaItems', 'taggedMediaItems'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('nickname', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%")
                        ->orWhere('job_title', 'like', "%{$search}%")
                        ->orWhere('short_story', 'like', "%{$search}%")
                        ->orWhere('memorable_story', 'like', "%{$search}%")
                        ->orWhere('message_to_friends', 'like', "%{$search}%");
                });
            })
            ->when(in_array($this->status, ['active', 'deceased'], true), function ($query): void {
                $query->where('alumni_status', $this->status);
            })
            ->when(filled($this->cityId), function ($query): void {
                $query->where('current_city_id', $this->cityId);
            })
            ->when($this->section === 'story', function ($query): void {
                $query->whereNotNull('short_story')->where('short_story', '!=', '');
            })
            ->when($this->section === 'memory', function ($query): void {
                $query->whereNotNull('memorable_story')->where('memorable_story', '!=', '');
            })
            ->when($this->section === 'message', function ($query): void {
                $query->whereNotNull('message_to_friends')->where('message_to_friends', '!=', '');
            })
            ->when($this->section === 'memorial', function ($query): void {
                $query->where('alumni_status', 'deceased');
            })
            ->orderBy('full_name')
            ->paginate(18);
    }

    #[Computed]
    public function cities(): Collection
    {
        return City::query()
            ->whereHas('alumni')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function profilePhotoUrl(Alumni $alumni): ?string
    {
        $path = $alumni->current_photo_path ?: $alumni->college_photo_path;

        return $path ? Storage::disk('public')->url($path) : null;
    }

    public function memoryCompletionLabel(Alumni $alumni): string
    {
        $filledCount = collect([
            $alumni->short_story,
            $alumni->memorable_story,
            $alumni->message_to_friends,
        ])->filter(fn (?string $value): bool => filled($value))->count();

        return __(':count/3 bagian', ['count' => $filledCount]);
    }

    public function sectionLabel(): string
    {
        return match ($this->section) {
            'story' => __('Cerita Alumni'),
            'memory' => __('Kenangan Alumni'),
            'message' => __('Pesan Alumni'),
            'memorial' => __('Memorial Alumni'),
            default => __('Seluruh Alumni'),
        };
    }
}; ?>

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="space-y-2">
            <flux:heading size="xl">{{ __('Buku Kenangan Digital') }}</flux:heading>
            <flux:text class="max-w-3xl">
                {{ __('Jelajahi :section, dokumentasi terkait, dan profil alumni Geodesi 96.', ['section' => $this->sectionLabel()]) }}
            </flux:text>
        </div>

        <div class="grid gap-3 sm:grid-cols-[minmax(14rem,1fr)_12rem_12rem] lg:w-[48rem]">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                :label="__('Cari')"
                :placeholder="__('Nama, cerita, perusahaan')"
            />

            <flux:select wire:model.live="cityId" :label="__('Kota')">
                <flux:select.option value="">{{ __('Semua kota') }}</flux:select.option>
                @foreach ($this->cities as $city)
                    <flux:select.option :value="$city->id">{{ $city->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="status" :label="__('Status')">
                <flux:select.option value="all">{{ __('Semua') }}</flux:select.option>
                <flux:select.option value="active">{{ __('Aktif') }}</flux:select.option>
                <flux:select.option value="deceased">{{ __('Memorial') }}</flux:select.option>
            </flux:select>
        </div>
    </div>

    <div class="flex flex-wrap gap-2">
        <flux:button size="sm" variant="{{ $section === 'all' ? 'primary' : 'ghost' }}" wire:click="$set('section', 'all')">
            {{ __('Seluruh Alumni') }}
        </flux:button>
        <flux:button size="sm" variant="{{ $section === 'story' ? 'primary' : 'ghost' }}" wire:click="$set('section', 'story')">
            {{ __('Cerita Alumni') }}
        </flux:button>
        <flux:button size="sm" variant="{{ $section === 'memory' ? 'primary' : 'ghost' }}" wire:click="$set('section', 'memory')">
            {{ __('Kenangan Alumni') }}
        </flux:button>
        <flux:button size="sm" variant="{{ $section === 'message' ? 'primary' : 'ghost' }}" wire:click="$set('section', 'message')">
            {{ __('Pesan Alumni') }}
        </flux:button>
        <flux:button size="sm" variant="{{ $section === 'memorial' ? 'primary' : 'ghost' }}" wire:click="$set('section', 'memorial')">
            {{ __('Memorial Alumni') }}
        </flux:button>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($this->alumniProfiles as $profile)
            <article wire:key="memory-profile-{{ $profile->id }}" class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="relative aspect-[5/3] bg-ktn-topo">
                    @if ($this->profilePhotoUrl($profile))
                        <img src="{{ $this->profilePhotoUrl($profile) }}" alt="{{ $profile->full_name }}" class="size-full object-cover">
                    @else
                        <div class="flex size-full items-center justify-center bg-ktn-forest text-4xl font-semibold text-white">
                            {{ collect(explode(' ', $profile->full_name))->filter()->map(fn (string $name): string => mb_substr($name, 0, 1))->take(2)->join('') }}
                        </div>
                    @endif

                    <div class="absolute left-3 top-3 flex flex-wrap gap-2">
                        <flux:badge color="{{ $profile->alumni_status === 'active' ? 'green' : 'zinc' }}">
                            {{ $profile->alumni_status === 'active' ? __('Aktif') : __('Memorial') }}
                        </flux:badge>
                        <flux:badge color="{{ $profile->is_profile_completed ? 'green' : 'amber' }}">
                            {{ $this->memoryCompletionLabel($profile) }}
                        </flux:badge>
                    </div>
                </div>

                <div class="space-y-4 p-5">
                    <div>
                        <flux:heading size="lg">{{ $profile->full_name }}</flux:heading>
                        <flux:text>
                            {{ collect([$profile->nickname, $profile->currentCity?->name, $profile->currentCountry?->name])->filter()->join(' / ') ?: __('Profil alumni Geodesi 96') }}
                        </flux:text>
                    </div>

                    <p class="line-clamp-3 text-sm leading-6 text-zinc-600 dark:text-zinc-300">
                        {{ $profile->short_story ?: $profile->memorable_story ?: $profile->message_to_friends ?: __('Cerita dan kenangan belum diisi.') }}
                    </p>

                    <div class="flex flex-wrap gap-2">
                        @if ($profile->short_story)
                            <flux:badge>{{ __('Cerita') }}</flux:badge>
                        @endif
                        @if ($profile->memorable_story)
                            <flux:badge>{{ __('Kenangan') }}</flux:badge>
                        @endif
                        @if ($profile->message_to_friends)
                            <flux:badge>{{ __('Pesan') }}</flux:badge>
                        @endif
                        @if (($profile->uploaded_media_items_count + $profile->tagged_media_items_count) > 0)
                            <flux:badge color="green">{{ __('Dokumentasi') }}</flux:badge>
                        @endif
                    </div>

                    <flux:button size="sm" variant="primary" icon="book-open" :href="route('memory-book.show', $profile)" wire:navigate>
                        {{ __('Baca Buku Kenangan') }}
                    </flux:button>
                </div>
            </article>
        @empty
            <div class="rounded-lg border border-dashed border-zinc-300 p-10 text-center md:col-span-2 xl:col-span-3 dark:border-zinc-700">
                <flux:heading size="lg">{{ __('Belum ada profil yang cocok') }}</flux:heading>
                <flux:text class="mt-2">{{ __('Ubah kata kunci atau filter untuk melihat alumni lain.') }}</flux:text>
            </div>
        @endforelse
    </div>

    <flux:pagination :paginator="$this->alumniProfiles" scroll-to="body" />
</section>
