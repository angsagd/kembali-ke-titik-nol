<?php

use App\Models\Alumni;
use App\Models\AlumniRsvpGuest;
use App\Models\ApplicationSetting;
use App\Models\AuditLog;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Monitoring RSVP')] class extends Component {
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = 'all';

    #[Url(as: 'sort')]
    public string $sort_by = 'full_name';

    #[Url(as: 'direction')]
    public string $sort_direction = 'asc';

    public bool $public_rsvp_form_enabled = true;

    public function mount(): void
    {
        $this->public_rsvp_form_enabled = ApplicationSetting::boolean(ApplicationSetting::PUBLIC_RSVP_FORM_ENABLED, true);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if (! array_key_exists($column, $this->sortableColumns())) {
            return;
        }

        if ($this->sort_by === $column) {
            $this->sort_direction = $this->sort_direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort_by = $column;
            $this->sort_direction = 'asc';
        }

        $this->resetPage();
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function summary(): array
    {
        return [
            'total' => Alumni::query()->count(),
            'pending' => Alumni::query()->where('rsvp_status', 'pending')->count(),
            'attending' => Alumni::query()->where('rsvp_status', 'attending')->count(),
            'attending_participants' => Alumni::query()
                ->where('rsvp_status', 'attending')
                ->where('rsvp_party_type', 'family')
                ->sum('family_members_count') + Alumni::query()->where('rsvp_status', 'attending')->count(),
            'not_attending' => Alumni::query()->where('rsvp_status', 'not_attending')->count(),
        ];
    }

    #[Computed]
    public function responseRate(): int
    {
        if ($this->summary['total'] === 0) {
            return 0;
        }

        $responded = $this->summary['attending'] + $this->summary['not_attending'];

        return (int) round(($responded / $this->summary['total']) * 100);
    }

    /**
     * @return array{
     *     sizes: array<int, string>,
     *     types: array<string, string>,
     *     counts: array<string, array<string, int>>,
     *     totals: array<string, int>,
     *     grand_total: int
     * }
     */
    #[Computed]
    public function shirtSummary(): array
    {
        $sizes = ['S', 'M', 'L', 'XL', 'XXL'];
        $types = [
            'child' => __('Anak'),
            'male' => __('Pria'),
            'female' => __('Wanita'),
        ];
        $counts = [];

        foreach (array_keys($types) as $type) {
            $counts[$type] = array_fill_keys($sizes, 0);
        }

        $alumniCounts = Alumni::query()
            ->select(['shirt_type', 'shirt_size'])
            ->selectRaw('COUNT(*) as aggregate')
            ->whereNotNull('shirt_type')
            ->whereNotNull('shirt_size')
            ->groupBy('shirt_type', 'shirt_size')
            ->get();

        $familyCounts = AlumniRsvpGuest::query()
            ->select(['shirt_type', 'shirt_size'])
            ->selectRaw('COUNT(*) as aggregate')
            ->whereHas('alumni')
            ->groupBy('shirt_type', 'shirt_size')
            ->get();

        foreach ($alumniCounts->concat($familyCounts) as $row) {
            if (isset($counts[$row->shirt_type][$row->shirt_size])) {
                $counts[$row->shirt_type][$row->shirt_size] += (int) $row->aggregate;
            }
        }

        $totals = collect($counts)
            ->map(fn (array $sizeCounts): int => array_sum($sizeCounts))
            ->all();

        return [
            'sizes' => $sizes,
            'types' => $types,
            'counts' => $counts,
            'totals' => $totals,
            'grand_total' => array_sum($totals),
        ];
    }

    #[Computed]
    public function alumniProfiles(): LengthAwarePaginator
    {
        $search = trim($this->search);

        return Alumni::query()
            ->with(['user', 'rsvpGuests'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('nickname', 'like', "%{$search}%")
                        ->orWhere('student_number', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($query) use ($search): void {
                            $query->where('whatsapp_number', 'like', "%{$search}%");
                        });
                });
            })
            ->when(in_array($this->status, ['pending', 'attending', 'not_attending'], true), function ($query): void {
                $query->where('rsvp_status', $this->status);
            })
            ->tap(fn ($query) => $this->applySorting($query))
            ->paginate(15);
    }

    public function togglePublicRsvpForm(): void
    {
        $oldValue = $this->public_rsvp_form_enabled;
        $this->public_rsvp_form_enabled = ! $this->public_rsvp_form_enabled;

        $setting = ApplicationSetting::setBoolean(ApplicationSetting::PUBLIC_RSVP_FORM_ENABLED, $this->public_rsvp_form_enabled);

        AuditLog::record(
            action: 'settings.public_rsvp_form_updated',
            entity: $setting,
            oldValues: ['enabled' => $oldValue],
            newValues: ['enabled' => $this->public_rsvp_form_enabled],
        );

        Flux::toast(variant: 'success', text: __('Pengaturan form RSVP publik diperbarui.'));
    }

    public function rsvpStatusLabel(?string $status): string
    {
        return match ($status) {
            'attending' => __('Hadir'),
            'not_attending' => __('Tidak Hadir'),
            default => __('Belum Merespon'),
        };
    }

    public function rsvpStatusColor(?string $status): string
    {
        return match ($status) {
            'attending' => 'green',
            'not_attending' => 'red',
            default => 'amber',
        };
    }

    public function partyTypeLabel(Alumni $alumni): string
    {
        if ($alumni->rsvp_status !== 'attending') {
            return '-';
        }

        return $alumni->rsvp_party_type === 'family'
            ? __('Bersama keluarga')
            : __('Sendiri');
    }

    public function shirtTypeLabel(?string $shirtType): string
    {
        return match ($shirtType) {
            'child' => __('Anak'),
            'male' => __('Pria'),
            'female' => __('Wanita'),
            default => '-',
        };
    }

    private function applySorting(Builder $query): void
    {
        $direction = $this->sort_direction === 'desc' ? 'desc' : 'asc';
        $column = $this->sortableColumns()[$this->sort_by] ?? 'full_name';

        match ($column) {
            'whatsapp_number' => $query->orderBy(
                User::query()
                    ->select('whatsapp_number')
                    ->whereColumn('users.id', 'alumni.user_id')
                    ->limit(1),
                $direction,
            ),
            'attendance' => $query
                ->orderBy('rsvp_party_type', $direction)
                ->orderBy('family_members_count', $direction),
            'shirt' => $query
                ->orderBy('shirt_type', $direction)
                ->orderBy('shirt_size', $direction),
            default => $query->orderBy($column, $direction),
        };

        $query->orderBy('id');
    }

    /**
     * @return array<string, string>
     */
    private function sortableColumns(): array
    {
        return [
            'full_name' => 'full_name',
            'whatsapp_number' => 'whatsapp_number',
            'rsvp_status' => 'rsvp_status',
            'attendance' => 'attendance',
            'shirt' => 'shirt',
            'updated_at' => 'updated_at',
        ];
    }
}; ?>

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="space-y-2">
        <flux:heading size="xl">{{ __('Monitoring RSVP') }}</flux:heading>
        <flux:text class="max-w-3xl">
            {{ __('Pantau status kehadiran alumni dan rekap peserta reuni berdasarkan RSVP yang diisi alumni.') }}
        </flux:text>
    </div>

    <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="space-y-1">
                <flux:heading size="lg">{{ __('Form RSVP Publik') }}</flux:heading>
                <flux:text class="max-w-2xl">
                    {{ __('Kontrol apakah alumni dapat mengisi atau mengedit data melalui halaman publik /rsvp tanpa login.') }}
                </flux:text>
            </div>

            <div class="flex flex-col gap-3 sm:items-end">
                <flux:badge color="{{ $public_rsvp_form_enabled ? 'green' : 'red' }}">
                    {{ $public_rsvp_form_enabled ? __('Dibuka') : __('Ditutup') }}
                </flux:badge>
                <flux:button
                    type="button"
                    variant="{{ $public_rsvp_form_enabled ? 'danger' : 'primary' }}"
                    icon="{{ $public_rsvp_form_enabled ? 'lock-closed' : 'lock-open' }}"
                    wire:click="togglePublicRsvpForm"
                    wire:loading.attr="disabled"
                >
                    {{ $public_rsvp_form_enabled ? __('Tutup Form Publik') : __('Buka Form Publik') }}
                </flux:button>
            </div>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <flux:card>
            <flux:text>{{ __('Total Alumni') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['total'] }}</div>
        </flux:card>
        <flux:card>
            <flux:text>{{ __('Belum Merespon') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['pending'] }}</div>
        </flux:card>
        <flux:card>
            <flux:text>{{ __('Hadir') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['attending'] }}</div>
        </flux:card>
        <flux:card>
            <flux:text>{{ __('Total Peserta Hadir') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['attending_participants'] }}</div>
        </flux:card>
        <flux:card>
            <flux:text>{{ __('Tidak Hadir') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['not_attending'] }}</div>
        </flux:card>
        <flux:card>
            <flux:text>{{ __('Response Rate') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->responseRate }}%</div>
        </flux:card>
    </div>

    <flux:card class="space-y-4">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <flux:heading size="lg">{{ __('Rekap Kaos') }}</flux:heading>
                <flux:text>{{ __('Jumlah kaos alumni dan keluarga berdasarkan jenis dan ukuran.') }}</flux:text>
            </div>
            <div class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Total: :count kaos', ['count' => $this->shirtSummary['grand_total']]) }}
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[38rem] table-fixed text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 text-left text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                        <th class="w-32 px-3 py-2 font-medium">{{ __('Jenis') }}</th>
                        @foreach ($this->shirtSummary['sizes'] as $size)
                            <th wire:key="shirt-size-heading-{{ $size }}" class="px-3 py-2 text-center font-medium">{{ $size }}</th>
                        @endforeach
                        <th class="px-3 py-2 text-center font-medium">{{ __('Total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($this->shirtSummary['types'] as $type => $label)
                        <tr wire:key="shirt-type-{{ $type }}" class="border-b border-zinc-100 last:border-0 dark:border-zinc-800">
                            <th class="px-3 py-3 text-left font-medium text-zinc-800 dark:text-zinc-100">{{ $label }}</th>
                            @foreach ($this->shirtSummary['sizes'] as $size)
                                <td wire:key="shirt-count-{{ $type }}-{{ $size }}" class="px-3 py-3 text-center tabular-nums">
                                    {{ $this->shirtSummary['counts'][$type][$size] }}
                                </td>
                            @endforeach
                            <td class="px-3 py-3 text-center font-semibold tabular-nums">
                                {{ $this->shirtSummary['totals'][$type] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </flux:card>

    <div class="grid gap-3 lg:grid-cols-[minmax(20rem,1fr)_12rem_auto] lg:items-end">
        <flux:input
            wire:model.live.debounce.300ms="search"
            icon="magnifying-glass"
            :label="__('Cari alumni')"
            :placeholder="__('Nama atau WhatsApp')"
        />

        <flux:select wire:model.live="status" :label="__('Status RSVP')">
            <flux:select.option value="all">{{ __('Semua') }}</flux:select.option>
            <flux:select.option value="pending">{{ __('Belum Merespon') }}</flux:select.option>
            <flux:select.option value="attending">{{ __('Hadir') }}</flux:select.option>
            <flux:select.option value="not_attending">{{ __('Tidak Hadir') }}</flux:select.option>
        </flux:select>

        <div class="flex lg:justify-end">
            <flux:button icon="arrow-down-tray" variant="primary" :href="route('reports.rsvp.export')">
                {{ __('Export CSV') }}
            </flux:button>
        </div>
    </div>

    <flux:table :paginate="$this->alumniProfiles">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sort_by === 'full_name'" :direction="$sort_direction" wire:click="sort('full_name')">{{ __('Nama') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sort_by === 'whatsapp_number'" :direction="$sort_direction" wire:click="sort('whatsapp_number')">{{ __('WhatsApp') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sort_by === 'rsvp_status'" :direction="$sort_direction" wire:click="sort('rsvp_status')">{{ __('Status RSVP') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sort_by === 'attendance'" :direction="$sort_direction" wire:click="sort('attendance')">{{ __('Kehadiran & Keluarga') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sort_by === 'shirt'" :direction="$sort_direction" wire:click="sort('shirt')">{{ __('Data Kaos') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sort_by === 'updated_at'" :direction="$sort_direction" wire:click="sort('updated_at')">{{ __('Terakhir Diperbarui') }}</flux:table.column>
            <flux:table.column align="end">{{ __('Aksi') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->alumniProfiles as $profile)
                <flux:table.row :key="$profile->id">
                    <flux:table.cell variant="strong">
                        <div class="grid gap-1">
                            <span>{{ $profile->full_name }}</span>
                            <span class="text-xs font-normal text-zinc-500 dark:text-zinc-400">{{ $profile->nickname ?: __('Nama panggilan belum diisi') }}</span>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>{{ $profile->user?->whatsapp_number ?: '-' }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge color="{{ $this->rsvpStatusColor($profile->rsvp_status) }}">
                            {{ $this->rsvpStatusLabel($profile->rsvp_status) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="grid gap-1">
                            <span>{{ $this->partyTypeLabel($profile) }}</span>
                            @if ($profile->rsvp_status === 'attending')
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                    @if ($profile->rsvp_party_type === 'family')
                                        {{ __(':count tambahan', ['count' => $profile->family_members_count]) }}
                                        ·
                                    @endif
                                    {{ __(':count peserta', ['count' => 1 + ($profile->rsvp_party_type === 'family' ? $profile->family_members_count : 0)]) }}
                                </span>
                            @endif
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        @if (filled($profile->shirt_size) && filled($profile->shirt_type))
                            <div class="grid min-w-44 gap-1 text-sm">
                                <span>{{ __('Alumni: :type / :size', ['type' => $this->shirtTypeLabel($profile->shirt_type), 'size' => $profile->shirt_size]) }}</span>
                                @foreach ($profile->rsvpGuests as $guest)
                                    <span wire:key="rsvp-guest-{{ $guest->id }}" class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ __('Keluarga :sequence: :type / :size', [
                                            'sequence' => $guest->sequence,
                                            'type' => $this->shirtTypeLabel($guest->shirt_type),
                                            'size' => $guest->shirt_size,
                                        ]) }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            -
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>{{ $profile->updated_at?->translatedFormat('d F Y H:i') ?: '-' }}</flux:table.cell>
                    <flux:table.cell align="end">
                        <flux:button size="sm" variant="ghost" icon="eye" :href="route('admin.alumni.show', $profile)" wire:navigate>
                            {{ __('Detail') }}
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7">
                        <div class="py-10 text-center">
                            <flux:heading size="lg">{{ __('Tidak ada RSVP ditemukan') }}</flux:heading>
                            <flux:text>{{ __('Ubah kata kunci atau filter status untuk melihat data lain.') }}</flux:text>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</section>
