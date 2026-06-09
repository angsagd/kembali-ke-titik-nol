<?php

use App\Models\Alumni;
use App\Models\AuditLog;
use App\Models\City;
use App\Models\Country;
use App\Models\Role;
use App\Models\User;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Detail Alumni')] class extends Component {
    public Alumni $alumni;

    public string $full_name = '';

    public ?string $nickname = null;

    public ?string $student_number = null;

    public ?string $email = null;

    public string $whatsapp_number = '';

    public string $alumni_status = 'active';

    public string $rsvp_status = 'pending';

    public ?string $company = null;

    public ?string $job_title = null;

    public ?int $current_country_id = null;

    public ?int $current_city_id = null;

    public ?string $special_notes = null;

    public bool $is_profile_completed = false;

    public ?int $role_id = null;

    public function mount(Alumni $alumni): void
    {
        $this->alumni = $alumni->load(['user.role']);
        $this->fillForm();
    }

    public function fillForm(): void
    {
        $this->full_name = $this->alumni->full_name;
        $this->nickname = $this->alumni->nickname;
        $this->student_number = $this->alumni->student_number;
        $this->email = $this->alumni->email;
        $this->whatsapp_number = $this->alumni->user?->whatsapp_number ?? '';
        $this->alumni_status = $this->alumni->alumni_status;
        $this->rsvp_status = $this->alumni->rsvp_status;
        $this->company = $this->alumni->company;
        $this->job_title = $this->alumni->job_title;
        $this->current_country_id = $this->alumni->current_country_id;
        $this->current_city_id = $this->alumni->current_city_id;
        $this->special_notes = $this->alumni->special_notes;
        $this->is_profile_completed = $this->alumni->is_profile_completed;
        $this->role_id = $this->alumni->user?->role_id;
    }

    public function updatedCurrentCountryId(): void
    {
        $this->current_city_id = null;
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
            ->when($this->current_country_id, fn ($query) => $query->where('country_id', $this->current_country_id))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    #[Computed]
    public function roles(): Collection
    {
        return Role::query()
            ->orderByRaw("case name when 'superadmin' then 1 when 'administrator' then 2 when 'bendahara' then 3 when 'alumni' then 4 else 5 end")
            ->orderBy('name')
            ->get(['id', 'name', 'description']);
    }

    #[Computed]
    public function timelines(): Collection
    {
        return $this->alumni
            ->timelines()
            ->with(['city', 'country'])
            ->get();
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

    /**
     * Update the alumni profile and linked user account.
     */
    public function updateAlumni(): void
    {
        $this->whatsapp_number = User::normalizeWhatsappNumber($this->whatsapp_number) ?? '';

        $validated = $this->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'nickname' => ['nullable', 'string', 'max:100'],
            'student_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique(Alumni::class, 'student_number')->ignore($this->alumni),
            ],
            'email' => [
                'nullable',
                'string',
                'email',
                'max:150',
                Rule::unique(User::class, 'email')->ignore($this->alumni->user),
            ],
            'whatsapp_number' => [
                'required',
                'string',
                'max:30',
                Rule::unique(User::class, 'whatsapp_number')->ignore($this->alumni->user),
            ],
            'alumni_status' => ['required', Rule::in(['active', 'deceased'])],
            'rsvp_status' => ['required', Rule::in(['pending', 'attending', 'not_attending'])],
            'company' => ['nullable', 'string', 'max:150'],
            'job_title' => ['nullable', 'string', 'max:150'],
            'current_country_id' => ['nullable', Rule::exists(Country::class, 'id')],
            'current_city_id' => [
                'nullable',
                Rule::exists(City::class, 'id')
                    ->where(fn ($query) => $query->where('country_id', $this->current_country_id ?? 0)),
            ],
            'special_notes' => ['nullable', 'string', 'max:5000'],
            'is_profile_completed' => ['boolean'],
        ]);

        DB::transaction(function () use ($validated): void {
            $this->alumni->update([
                'full_name' => $validated['full_name'],
                'nickname' => $validated['nickname'],
                'student_number' => $validated['student_number'],
                'email' => $validated['email'],
                'alumni_status' => $validated['alumni_status'],
                'rsvp_status' => $validated['rsvp_status'],
                'company' => $validated['company'],
                'job_title' => $validated['job_title'],
                'current_country_id' => $validated['current_country_id'],
                'current_city_id' => $validated['current_city_id'],
                'special_notes' => $validated['special_notes'],
                'is_profile_completed' => $validated['is_profile_completed'],
            ]);

            $this->alumni->user?->forceFill([
                'name' => $validated['full_name'],
                'whatsapp_number' => $validated['whatsapp_number'],
                'email' => $validated['email'] ?: $this->alumni->user->email,
            ])->save();
        });

        $this->alumni = $this->alumni->fresh(['user.role']);
        $this->fillForm();

        Flux::toast(variant: 'success', text: __('Data alumni diperbarui.'));
    }

    /**
     * Update the linked user account role.
     */
    public function updateRole(): void
    {
        Gate::authorize('manage-user-roles');

        $validated = $this->validate([
            'role_id' => ['required', 'integer', Rule::exists(Role::class, 'id')],
        ]);

        $user = $this->alumni->user;

        abort_unless($user instanceof User, 404);

        $targetRole = Role::query()->findOrFail($validated['role_id']);
        $currentRole = $user->role?->name;

        if ($currentRole === $targetRole->name) {
            Flux::toast(variant: 'info', text: __('Role akun tidak berubah.'));

            return;
        }

        if ($currentRole === 'superadmin' && $targetRole->name !== 'superadmin' && ! $this->hasOtherSuperadmin($user)) {
            $this->addError('role_id', __('Minimal satu superadmin harus tetap tersedia.'));

            return;
        }

        $oldValues = [
            'role_id' => $user->role_id,
            'role' => $currentRole,
        ];

        $user->forceFill(['role_id' => $targetRole->id])->save();
        $user->refresh()->load('role');

        AuditLog::record(
            action: 'user.role_updated',
            entity: $user,
            oldValues: $oldValues,
            newValues: [
                'role_id' => $user->role_id,
                'role' => $user->role?->name,
            ],
        );

        $this->alumni = $this->alumni->fresh(['user.role']);
        $this->fillForm();

        Flux::toast(variant: 'success', text: __('Role akun diperbarui.'));
    }

    private function hasOtherSuperadmin(User $user): bool
    {
        return User::query()
            ->whereKeyNot($user->id)
            ->whereHas('role', fn ($query) => $query->where('name', 'superadmin'))
            ->exists();
    }
}; ?>

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-2">
            <flux:button variant="ghost" icon="arrow-left" :href="route('admin.alumni.index')" wire:navigate>
                {{ __('Kembali') }}
            </flux:button>
            <flux:heading size="xl">{{ $alumni->full_name }}</flux:heading>
            <flux:text>{{ __('Detail data alumni dan akun yang terhubung.') }}</flux:text>
        </div>

        <div class="flex flex-wrap gap-2">
            <flux:badge color="{{ $alumni->alumni_status === 'active' ? 'green' : 'zinc' }}">
                {{ $alumni->alumni_status === 'active' ? __('Aktif') : __('Wafat') }}
            </flux:badge>
            <flux:badge color="{{ $alumni->is_profile_completed ? 'green' : 'amber' }}">
                {{ $alumni->is_profile_completed ? __('Profil Lengkap') : __('Profil Awal') }}
            </flux:badge>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_22rem]">
        <div class="space-y-6">
            <form wire:submit="updateAlumni" class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <flux:heading size="lg">{{ __('Edit Data Inti') }}</flux:heading>
                        <flux:text>{{ __('Perubahan di sini memperbarui profil alumni dan akun login yang terhubung.') }}</flux:text>
                    </div>

                    <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled">
                        {{ __('Simpan') }}
                    </flux:button>
                </div>

                <div class="mt-5 grid gap-5 lg:grid-cols-2">
                    <flux:input wire:model="full_name" :label="__('Nama lengkap')" required />
                    <flux:input wire:model="nickname" :label="__('Nama panggilan')" />
                    <flux:input wire:model="student_number" :label="__('NIM')" />
                    <flux:input wire:model="whatsapp_number" :label="__('Nomor WhatsApp')" type="tel" required />
                    <flux:input wire:model="email" :label="__('Email')" type="email" />
                    <flux:input wire:model="company" :label="__('Instansi / Perusahaan')" />
                    <flux:input wire:model="job_title" :label="__('Jabatan / Pekerjaan')" />

                    <flux:select wire:model.live="current_country_id" :label="__('Negara domisili')">
                        <flux:select.option value="">{{ __('Belum diisi') }}</flux:select.option>
                        @foreach ($this->countries as $country)
                            <flux:select.option :value="$country->id">{{ $country->name }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model="current_city_id" :label="__('Kota domisili')" wire:key="admin-city-{{ $current_country_id ?: 'none' }}">
                        <flux:select.option value="">{{ __('Belum diisi') }}</flux:select.option>
                        @foreach ($this->cities as $city)
                            <flux:select.option :value="$city->id">{{ $city->name }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model="alumni_status" :label="__('Status alumni')">
                        <flux:select.option value="active">{{ __('Aktif') }}</flux:select.option>
                        <flux:select.option value="deceased">{{ __('Wafat') }}</flux:select.option>
                    </flux:select>

                    <flux:select wire:model="rsvp_status" :label="__('Status RSVP')">
                        <flux:select.option value="pending">{{ __('Pending') }}</flux:select.option>
                        <flux:select.option value="attending">{{ __('Hadir') }}</flux:select.option>
                        <flux:select.option value="not_attending">{{ __('Tidak hadir') }}</flux:select.option>
                    </flux:select>

                    <flux:field>
                        <flux:label>{{ __('Kelengkapan profil') }}</flux:label>
                        <flux:checkbox wire:model="is_profile_completed" :label="__('Tandai profil sudah lengkap')" />
                        <flux:error name="is_profile_completed" />
                    </flux:field>
                </div>

                <div class="mt-5">
                    <flux:textarea wire:model="special_notes" :label="__('Catatan admin')" rows="4" />
                </div>
            </form>

            <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg">{{ __('Identitas') }}</flux:heading>

                <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Nama lengkap') }}</dt>
                        <dd class="font-medium">{{ $alumni->full_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Nama panggilan') }}</dt>
                        <dd class="font-medium">{{ $alumni->nickname ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('NIM') }}</dt>
                        <dd class="font-medium">{{ $alumni->student_number ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Email') }}</dt>
                        <dd class="font-medium">{{ $alumni->email ?: '-' }}</dd>
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
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Kota domisili') }}</dt>
                        <dd class="font-medium">{{ $alumni->currentCity?->name ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Negara domisili') }}</dt>
                        <dd class="font-medium">{{ $alumni->currentCountry?->name ?: '-' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg">{{ __('Cerita Alumni') }}</flux:heading>

                <div class="mt-5 grid gap-5">
                    <div>
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Cerita singkat') }}</h3>
                        <p class="mt-1 whitespace-pre-line">{{ $alumni->short_story ?: __('Belum diisi.') }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Kenangan kuliah') }}</h3>
                        <p class="mt-1 whitespace-pre-line">{{ $alumni->memorable_story ?: __('Belum diisi.') }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Pesan untuk teman') }}</h3>
                        <p class="mt-1 whitespace-pre-line">{{ $alumni->message_to_friends ?: __('Belum diisi.') }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <flux:heading size="lg">{{ __('Timeline Lokasi') }}</flux:heading>
                        <flux:text>{{ __('Riwayat lokasi yang diisi alumni dari tahun ke tahun.') }}</flux:text>
                    </div>

                    <flux:badge>{{ $this->timelines->count() }}</flux:badge>
                </div>

                <div class="mt-5 grid gap-4">
                    @forelse ($this->timelines as $timeline)
                        <article wire:key="admin-timeline-{{ $timeline->id }}" class="rounded-md border border-zinc-200 p-4 dark:border-zinc-700">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                <div>
                                    <div class="font-semibold">
                                        {{ $timeline->month ? $this->monthName($timeline->month).' ' : '' }}{{ $timeline->year }}
                                    </div>
                                    <div class="text-sm text-zinc-600 dark:text-zinc-300">
                                        {{ collect([$timeline->city?->name, $timeline->country?->name])->filter()->join(', ') ?: __('Lokasi belum diisi') }}
                                    </div>
                                </div>

                                <flux:badge color="{{ $timeline->location_source === 'manual' ? 'amber' : 'zinc' }}">
                                    {{ $timeline->location_source === 'manual' ? __('Manual') : __('Geocoded') }}
                                </flux:badge>
                            </div>

                            @if ($timeline->notes)
                                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $timeline->notes }}</p>
                            @endif
                        </article>
                    @empty
                        <div class="rounded-md border border-dashed border-zinc-300 p-6 text-center dark:border-zinc-700">
                            <flux:text>{{ __('Belum ada riwayat lokasi.') }}</flux:text>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg">{{ __('Akun') }}</flux:heading>

                <dl class="mt-5 grid gap-4">
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Nama akun') }}</dt>
                        <dd class="font-medium">{{ $alumni->user?->name ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('WhatsApp') }}</dt>
                        <dd class="font-medium">{{ $alumni->user?->whatsapp_number ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Role') }}</dt>
                        <dd class="font-medium">{{ $alumni->user?->role?->name ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Login terakhir') }}</dt>
                        <dd class="font-medium">{{ $alumni->user?->last_login_at?->diffForHumans() ?: '-' }}</dd>
                    </div>
                </dl>

                @can('manage-user-roles')
                    <form wire:submit="updateRole" class="mt-5 border-t border-zinc-200 pt-5 dark:border-zinc-700">
                        <flux:select wire:model="role_id" :label="__('Ubah role akun')" :description="__('Hanya superadmin yang dapat mengubah role user.')">
                            @foreach ($this->roles as $role)
                                <flux:select.option :value="$role->id">
                                    {{ $role->description ? "{$role->name} - {$role->description}" : $role->name }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>

                        <flux:button type="submit" variant="primary" icon="shield-check" class="mt-4 w-full" wire:loading.attr="disabled">
                            {{ __('Simpan Role') }}
                        </flux:button>
                    </form>
                @endcan
            </div>

            <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg">{{ __('Status RSVP') }}</flux:heading>
                <flux:text class="mt-2">
                    {{ match ($alumni->rsvp_status) {
                        'attending' => __('Hadir'),
                        'not_attending' => __('Tidak hadir'),
                        default => __('Pending konfirmasi'),
                    } }}
                </flux:text>
            </div>

            <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg">{{ __('Catatan Admin') }}</flux:heading>
                <flux:text class="mt-2 whitespace-pre-line">{{ $alumni->special_notes ?: __('Belum ada catatan.') }}</flux:text>
            </div>
        </aside>
    </div>
</section>
