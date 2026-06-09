<?php

use App\Models\AuditLog;
use App\Models\Alumni;
use App\Models\Role;
use App\Models\User;
use App\Models\City;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Manajemen Alumni')] class extends Component {
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = 'all';

    #[Url(as: 'sort')]
    public string $sort_by = 'full_name';

    #[Url(as: 'direction')]
    public string $sort_direction = 'asc';

    public bool $show_create_form = false;

    public string $full_name = '';

    public string $whatsapp_number = '';

    public ?string $student_number = null;

    public ?string $email = null;

    public string $alumni_status = 'active';

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

    #[Computed]
    public function alumniProfiles(): LengthAwarePaginator
    {
        $search = trim($this->search);

        return Alumni::query()
            ->with(['currentCity', 'currentCountry', 'user.role'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('nickname', 'like', "%{$search}%")
                        ->orWhere('student_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('currentCity', function ($query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('currentCountry', function ($query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('user', function ($query) use ($search): void {
                            $query->where('whatsapp_number', 'like', "%{$search}%");
                        });
                });
            })
            ->when(in_array($this->status, ['active', 'deceased'], true), function ($query): void {
                $query->where('alumni_status', $this->status);
            })
            ->tap(fn ($query) => $this->applySorting($query))
            ->paginate(15);
    }

    public function showCreateForm(): void
    {
        $this->show_create_form = true;
        $this->resetCreateForm();
    }

    public function cancelCreate(): void
    {
        $this->show_create_form = false;
        $this->resetCreateForm();
    }

    public function createAlumni(): void
    {
        $this->whatsapp_number = User::normalizeWhatsappNumber($this->whatsapp_number) ?? '';

        $validated = $this->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'whatsapp_number' => ['required', 'string', 'max:30', Rule::unique(User::class, 'whatsapp_number')],
            'student_number' => ['nullable', 'string', 'max:50', Rule::unique(Alumni::class, 'student_number')],
            'email' => ['nullable', 'string', 'email', 'max:150', Rule::unique(User::class, 'email')],
            'alumni_status' => ['required', Rule::in(['active', 'deceased'])],
        ]);

        $alumni = DB::transaction(function () use ($validated): Alumni {
            $alumniRole = Role::query()->firstOrCreate(
                ['name' => 'alumni'],
                ['description' => 'Anggota alumni'],
            );
            $password = 'tgd'.substr($validated['whatsapp_number'], -4);

            $user = User::query()->create([
                'role_id' => $alumniRole->id,
                'name' => $validated['full_name'],
                'email' => $validated['email'] ?: "{$validated['whatsapp_number']}@geodesi96.local",
                'whatsapp_number' => $validated['whatsapp_number'],
                'password' => $password,
                'is_active' => true,
            ]);

            $alumni = Alumni::query()->create([
                'user_id' => $user->id,
                'student_number' => $validated['student_number'],
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'alumni_status' => $validated['alumni_status'],
                'rsvp_status' => 'pending',
                'is_profile_completed' => false,
            ]);

            AuditLog::record(
                action: 'alumni.created',
                entity: $alumni,
                newValues: [
                    'full_name' => $alumni->full_name,
                    'student_number' => $alumni->student_number,
                    'whatsapp_number' => $user->whatsapp_number,
                    'role' => $user->role?->name,
                ],
            );

            return $alumni;
        });

        $this->redirectRoute('admin.alumni.show', ['alumni' => $alumni], navigate: true);
    }

    private function resetCreateForm(): void
    {
        $this->reset(['full_name', 'whatsapp_number', 'student_number', 'email']);
        $this->alumni_status = 'active';
        $this->resetErrorBag();
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
            'city' => $query->orderBy(
                City::query()
                    ->select('name')
                    ->whereColumn('cities.id', 'alumni.current_city_id')
                    ->limit(1),
                $direction,
            ),
            'role' => $query->orderBy(
                Role::query()
                    ->select('roles.name')
                    ->join('users', 'users.role_id', '=', 'roles.id')
                    ->whereColumn('users.id', 'alumni.user_id')
                    ->limit(1),
                $direction,
            ),
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
            'student_number' => 'student_number',
            'whatsapp_number' => 'whatsapp_number',
            'city' => 'city',
            'role' => 'role',
            'alumni_status' => 'alumni_status',
            'rsvp_status' => 'rsvp_status',
        ];
    }
}; ?>

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="space-y-2">
            <flux:heading size="xl">{{ __('Manajemen Alumni') }}</flux:heading>
            <flux:text class="max-w-2xl">
                {{ __('Data awal alumni dari seed kontak WhatsApp. Gunakan pencarian untuk menelusuri nama, NIM, email, atau nomor WhatsApp.') }}
            </flux:text>
        </div>

        <div class="grid gap-3 lg:w-[34rem]">
            <div class="flex flex-wrap justify-end gap-3">
                <flux:button icon="plus" variant="primary" wire:click="showCreateForm">
                    {{ __('Tambah Alumni') }}
                </flux:button>

                <flux:button icon="arrow-down-tray" variant="primary" :href="route('reports.alumni.export')">
                    {{ __('Export Alumni') }}
                </flux:button>
            </div>

            <div class="grid gap-3 sm:grid-cols-[minmax(16rem,1fr)_12rem]">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    icon="magnifying-glass"
                    :label="__('Cari alumni')"
                    :placeholder="__('Nama, NIM, WhatsApp')"
                />

                <flux:select wire:model.live="status" :label="__('Status')">
                    <flux:select.option value="all">{{ __('Semua') }}</flux:select.option>
                    <flux:select.option value="active">{{ __('Aktif') }}</flux:select.option>
                    <flux:select.option value="deceased">{{ __('Wafat') }}</flux:select.option>
                </flux:select>
            </div>
        </div>
    </div>

    @if ($show_create_form)
        <form wire:submit="createAlumni" class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <flux:heading size="lg">{{ __('Tambah Alumni') }}</flux:heading>
                    <flux:text>{{ __('Buat akun alumni baru dengan role default alumni. Password awal mengikuti pola tgd + 4 digit terakhir WhatsApp.') }}</flux:text>
                </div>

                <div class="flex flex-wrap gap-2">
                    <flux:button type="button" variant="ghost" wire:click="cancelCreate">
                        {{ __('Batal') }}
                    </flux:button>
                    <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled">
                        {{ __('Simpan Alumni') }}
                    </flux:button>
                </div>
            </div>

            <div class="mt-5 grid gap-5 lg:grid-cols-2">
                <flux:input wire:model="full_name" :label="__('Nama lengkap')" required />
                <flux:input wire:model="whatsapp_number" :label="__('Nomor WhatsApp')" type="tel" required />
                <flux:input wire:model="student_number" :label="__('NIM')" />
                <flux:input wire:model="email" :label="__('Email')" type="email" />
                <flux:select wire:model="alumni_status" :label="__('Status alumni')">
                    <flux:select.option value="active">{{ __('Aktif') }}</flux:select.option>
                    <flux:select.option value="deceased">{{ __('Wafat') }}</flux:select.option>
                </flux:select>
            </div>
        </form>
    @endif

    <flux:table :paginate="$this->alumniProfiles" pagination:scroll-to="body">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sort_by === 'full_name'" :direction="$sort_direction" wire:click="sort('full_name')">{{ __('Nama') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sort_by === 'whatsapp_number'" :direction="$sort_direction" wire:click="sort('whatsapp_number')">{{ __('WhatsApp') }}</flux:table.column>
            <flux:table.column sortable :sorted="$sort_by === 'city'" :direction="$sort_direction" wire:click="sort('city')">{{ __('Domisili') }}</flux:table.column>
            @can('manage-user-roles')
                <flux:table.column sortable :sorted="$sort_by === 'role'" :direction="$sort_direction" wire:click="sort('role')">{{ __('Role') }}</flux:table.column>
            @endcan
            <flux:table.column sortable :sorted="$sort_by === 'rsvp_status'" :direction="$sort_direction" wire:click="sort('rsvp_status')">{{ __('RSVP') }}</flux:table.column>
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
                    <flux:table.cell>{{ collect([$profile->currentCity?->name, $profile->currentCountry?->name])->filter()->join(', ') ?: '-' }}</flux:table.cell>
                    @can('manage-user-roles')
                        <flux:table.cell>
                            <flux:badge color="{{ match ($profile->user?->role?->name) {
                                'superadmin' => 'red',
                                'administrator' => 'blue',
                                'bendahara' => 'amber',
                                default => 'zinc',
                            } }}">
                                {{ $profile->user?->role?->name ?: '-' }}
                            </flux:badge>
                        </flux:table.cell>
                    @endcan
                    <flux:table.cell>
                        <flux:badge color="{{ $profile->rsvp_status === 'attending' ? 'green' : ($profile->rsvp_status === 'not_attending' ? 'red' : 'amber') }}">
                            {{ match ($profile->rsvp_status) {
                                'attending' => __('Hadir'),
                                'not_attending' => __('Tidak Hadir'),
                                default => __('Pending'),
                            } }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell align="end">
                        <flux:button size="sm" variant="ghost" icon="eye" :href="route('admin.alumni.show', $profile)" wire:navigate>
                            {{ __('Detail') }}
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="{{ auth()->user()->canManageUserRoles() ? 6 : 5 }}">
                        <div class="py-10 text-center">
                            <flux:heading size="lg">{{ __('Tidak ada alumni ditemukan') }}</flux:heading>
                            <flux:text>{{ __('Ubah kata kunci atau filter status untuk melihat data lain.') }}</flux:text>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</section>
