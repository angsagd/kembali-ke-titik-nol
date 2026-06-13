<?php

use App\Models\Alumni;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Direktori Alumni')] class extends Component {
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = 'all';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function alumniProfiles(): LengthAwarePaginator
    {
        $search = trim($this->search);

        return Alumni::query()
            ->with('user')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('nickname', 'like', "%{$search}%")
                        ->orWhere('student_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%")
                        ->orWhere('job_title', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%")
                        ->orWhere('country', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($query) use ($search): void {
                            $query->where('whatsapp_number', 'like', "%{$search}%");
                        });
                });
            })
            ->when(in_array($this->status, ['active', 'deceased'], true), function ($query): void {
                $query->where('alumni_status', $this->status);
            })
            ->orderBy('full_name')
            ->paginate(18);
    }
}; ?>

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="space-y-2">
            <flux:heading size="xl">{{ __('Direktori Alumni') }}</flux:heading>
            <flux:text class="max-w-3xl">
                {{ __('Temukan teman seangkatan, baca profil singkat, dan jelajahi cerita alumni Kembali ke Titik Nol.') }}
            </flux:text>
        </div>

        <div class="grid gap-3 sm:grid-cols-[minmax(16rem,1fr)_12rem] lg:w-[34rem]">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                :label="__('Cari alumni')"
                :placeholder="__('Nama, kota, negara, perusahaan')"
            />

            <flux:select wire:model.live="status" :label="__('Status')">
                <flux:select.option value="all">{{ __('Semua') }}</flux:select.option>
                <flux:select.option value="active">{{ __('Aktif') }}</flux:select.option>
                <flux:select.option value="deceased">{{ __('Memorial') }}</flux:select.option>
            </flux:select>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($this->alumniProfiles as $profile)
            <article wire:key="alumni-profile-{{ $profile->id }}" class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0 space-y-1">
                        <flux:heading size="lg" class="truncate">{{ $profile->full_name }}</flux:heading>
                        <flux:text class="truncate">
                            {{ $profile->nickname ?: __('Nama panggilan belum diisi') }}
                        </flux:text>
                    </div>

                    <flux:badge color="{{ $profile->alumni_status === 'active' ? 'green' : 'zinc' }}">
                        {{ $profile->alumni_status === 'active' ? __('Aktif') : __('Memorial') }}
                    </flux:badge>
                </div>

                <dl class="mt-5 grid gap-3 text-sm">
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Instansi / Perusahaan') }}</dt>
                        <dd class="font-medium">{{ $profile->company ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Jabatan / Pekerjaan') }}</dt>
                        <dd class="font-medium">{{ $profile->job_title ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Domisili') }}</dt>
                        <dd class="font-medium">
                            {{ collect([$profile->city, $profile->country])->filter()->join(', ') ?: '-' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-zinc-500 dark:text-zinc-400">{{ __('RSVP') }}</dt>
                        <dd class="font-medium">
                            {{ match ($profile->rsvp_status) {
                                'attending' => __('Hadir'),
                                'not_attending' => __('Tidak hadir'),
                                default => __('Pending'),
                            } }}
                        </dd>
                    </div>
                </dl>

                <div class="mt-5">
                    <flux:button size="sm" variant="ghost" icon="eye" :href="route('alumni.directory.show', $profile)" wire:navigate>
                        {{ __('Lihat Profil') }}
                    </flux:button>
                </div>
            </article>
        @empty
            <div class="rounded-lg border border-zinc-200 bg-white p-10 text-center md:col-span-2 xl:col-span-3 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg">{{ __('Tidak ada alumni ditemukan') }}</flux:heading>
                <flux:text>{{ __('Ubah kata kunci atau filter status untuk melihat data lain.') }}</flux:text>
            </div>
        @endforelse
    </div>

    <flux:pagination :paginator="$this->alumniProfiles" scroll-to="body" />
</section>
