<?php

use App\Models\Alumni;
use App\Models\ApplicationSetting;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::public')]
#[Title('RSVP Publik')] class extends Component {
    public ?Alumni $alumni = null;

    public string $lookup_whatsapp_number = '';

    public string $full_name = '';

    public ?string $nickname = null;

    public ?string $student_number = null;

    public ?string $email = null;

    public string $whatsapp_number = '';

    public string $rsvp_status = 'pending';

    public string $rsvp_party_type = 'self';

    public int|string $family_members_count = 0;

    public int|string|null $brings_private_vehicle = null;

    public ?string $shirt_size = null;

    public ?string $shirt_type = null;

    /** @var array<int, array{shirt_size: string|null, shirt_type: string|null}> */
    public array $family_members = [];

    public ?string $company = null;

    public ?string $job_title = null;

    public ?string $city = null;

    public ?string $country = null;

    public float|string|null $latitude = null;

    public float|string|null $longitude = null;

    public string $location_search = '';

    public ?string $short_story = null;

    public ?string $memorable_story = null;

    public ?string $message_to_friends = null;

    public ?string $special_notes = null;

    public bool $saved = false;

    public function verifyWhatsappNumber(): void
    {
        abort_unless($this->isFormOpen(), 403);

        $this->saved = false;
        $this->lookup_whatsapp_number = User::normalizeWhatsappNumber($this->lookup_whatsapp_number) ?? '';

        $this->validate([
            'lookup_whatsapp_number' => ['required', 'string', 'max:30'],
        ]);

        $user = User::query()
            ->with('alumni')
            ->where('whatsapp_number', $this->lookup_whatsapp_number)
            ->first();

        if ($user?->alumni === null) {
            $this->resetAlumniForm();
            $this->addError('lookup_whatsapp_number', __('Nomor WhatsApp tidak ditemukan pada data alumni.'));

            return;
        }

        $this->alumni = $user->alumni;
        $this->fillForm();
    }

    public function updatedRsvpStatus(): void
    {
        if ($this->rsvp_status !== 'attending') {
            $this->resetRsvpParty();
            $this->brings_private_vehicle = null;
        }
    }

    public function updatedRsvpPartyType(): void
    {
        if ($this->rsvp_party_type === 'self') {
            $this->resetRsvpParty();

            return;
        }

        if ((int) $this->family_members_count < 1) {
            $this->family_members_count = 1;
        }

        $this->syncFamilyMembers();
    }

    public function updatedFamilyMembersCount(): void
    {
        if ($this->rsvp_party_type === 'self') {
            $this->family_members_count = 0;
            $this->family_members = [];

            return;
        }

        $this->syncFamilyMembers();
    }

    public function save(): void
    {
        abort_unless($this->isFormOpen(), 403);

        if ($this->alumni === null) {
            $this->addError('lookup_whatsapp_number', __('Verifikasi nomor WhatsApp terlebih dahulu.'));

            return;
        }

        $this->whatsapp_number = User::normalizeWhatsappNumber($this->whatsapp_number) ?? '';

        $validated = ValidatorFacade::make($this->rsvpFormData(), [
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
            'rsvp_status' => ['required', Rule::in(['pending', 'attending', 'not_attending'])],
            'rsvp_party_type' => ['required', Rule::in(['self', 'family'])],
            'family_members_count' => ['required', 'integer', 'min:0', 'max:20'],
            'brings_private_vehicle' => ['nullable', 'boolean'],
            'shirt_size' => ['nullable', Rule::in(['S', 'M', 'L', 'XL', 'XXL'])],
            'shirt_type' => ['nullable', Rule::in(['child', 'male', 'female'])],
            'family_members' => ['array'],
            'family_members.*.shirt_size' => ['nullable', Rule::in(['S', 'M', 'L', 'XL', 'XXL'])],
            'family_members.*.shirt_type' => ['nullable', Rule::in(['child', 'male', 'female'])],
            'company' => ['nullable', 'string', 'max:150'],
            'job_title' => ['nullable', 'string', 'max:150'],
            'location_search' => ['nullable', 'string', 'max:300'],
            'city' => ['nullable', 'required_with:location_search', 'string', 'max:120'],
            'country' => ['nullable', 'required_with:location_search', 'string', 'max:100'],
            'latitude' => ['nullable', 'required_with:location_search', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:location_search', 'numeric', 'between:-180,180'],
            'short_story' => ['nullable', 'string', 'max:5000'],
            'memorable_story' => ['nullable', 'string', 'max:5000'],
            'message_to_friends' => ['nullable', 'string', 'max:5000'],
            'special_notes' => ['nullable', 'string', 'max:5000'],
        ], [], $this->validationAttributes())->after(function (Validator $validator): void {
            $this->validateRsvpParty($validator);
        })->validate();

        DB::transaction(function () use ($validated): void {
            $isProfileCompleted = filled($validated['full_name'])
                && filled($validated['whatsapp_number'])
                && filled($validated['rsvp_status'])
                && ($validated['rsvp_status'] !== 'attending' || (filled($validated['shirt_size']) && filled($validated['shirt_type'])))
                && (filled($validated['short_story']) || filled($validated['memorable_story']) || filled($validated['message_to_friends']));

            $this->alumni?->update([
                'full_name' => $validated['full_name'],
                'nickname' => $validated['nickname'],
                'student_number' => $validated['student_number'],
                'email' => $validated['email'],
                'rsvp_status' => $validated['rsvp_status'],
                'rsvp_party_type' => $validated['rsvp_party_type'],
                'family_members_count' => $validated['rsvp_party_type'] === 'family' ? (int) $validated['family_members_count'] : 0,
                'brings_private_vehicle' => $validated['rsvp_status'] === 'attending'
                    ? (bool) $validated['brings_private_vehicle']
                    : null,
                'shirt_size' => $validated['shirt_size'],
                'shirt_type' => $validated['shirt_type'],
                'company' => $validated['company'],
                'job_title' => $validated['job_title'],
                'city' => $validated['city'],
                'country' => $validated['country'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'short_story' => $validated['short_story'],
                'memorable_story' => $validated['memorable_story'],
                'message_to_friends' => $validated['message_to_friends'],
                'special_notes' => $validated['special_notes'],
                'is_profile_completed' => $isProfileCompleted,
            ]);

            $this->alumni?->user?->forceFill([
                'name' => $validated['full_name'],
                'whatsapp_number' => $validated['whatsapp_number'],
                'email' => $validated['email'] ?: $this->alumni->user->email,
            ])->save();

            $this->syncRsvpGuests($validated);
        });

        $this->alumni = $this->alumni?->fresh(['user', 'rsvpGuests']);
        $this->lookup_whatsapp_number = $this->alumni?->user?->whatsapp_number ?? $this->lookup_whatsapp_number;
        $this->fillForm();
        $this->saved = true;

        Flux::toast(variant: 'success', text: __('Data RSVP berhasil disimpan.'));
    }

    public function isFormOpen(): bool
    {
        return ApplicationSetting::boolean(ApplicationSetting::PUBLIC_RSVP_FORM_ENABLED, true);
    }

    private function fillForm(): void
    {
        if ($this->alumni === null) {
            return;
        }

        $this->full_name = $this->alumni->full_name;
        $this->nickname = $this->alumni->nickname;
        $this->student_number = $this->alumni->student_number;
        $this->email = $this->alumni->email;
        $this->whatsapp_number = $this->alumni->user?->whatsapp_number ?? '';
        $this->rsvp_status = $this->alumni->rsvp_status;
        $this->rsvp_party_type = $this->alumni->rsvp_party_type;
        $this->family_members_count = $this->alumni->family_members_count;
        $this->brings_private_vehicle = $this->alumni->brings_private_vehicle === null
            ? null
            : (int) $this->alumni->brings_private_vehicle;
        $this->shirt_size = $this->alumni->shirt_size;
        $this->shirt_type = $this->alumni->shirt_type;
        $this->family_members = $this->alumni->rsvpGuests
            ->map(fn ($guest): array => [
                'shirt_size' => $guest->shirt_size,
                'shirt_type' => $guest->shirt_type,
            ])
            ->values()
            ->all();

        if ($this->rsvp_status === 'attending') {
            $this->syncFamilyMembers();
        } else {
            $this->resetRsvpParty();
        }

        $this->company = $this->alumni->company;
        $this->job_title = $this->alumni->job_title;
        $this->city = $this->alumni->city;
        $this->country = $this->alumni->country;
        $this->latitude = $this->alumni->latitude;
        $this->longitude = $this->alumni->longitude;
        $this->location_search = collect([$this->city, $this->country])->filter()->join(', ');
        $this->short_story = $this->alumni->short_story;
        $this->memorable_story = $this->alumni->memorable_story;
        $this->message_to_friends = $this->alumni->message_to_friends;
        $this->special_notes = $this->alumni->special_notes;
    }

    private function resetAlumniForm(): void
    {
        $this->alumni = null;
        $this->reset([
            'full_name',
            'nickname',
            'student_number',
            'email',
            'whatsapp_number',
            'brings_private_vehicle',
            'shirt_size',
            'shirt_type',
            'company',
            'job_title',
            'city',
            'country',
            'latitude',
            'longitude',
            'location_search',
            'short_story',
            'memorable_story',
            'message_to_friends',
            'special_notes',
        ]);
        $this->rsvp_status = 'pending';
        $this->rsvp_party_type = 'self';
        $this->family_members_count = 0;
        $this->brings_private_vehicle = null;
        $this->family_members = [];
    }

    protected function syncFamilyMembers(): void
    {
        $count = max(0, min(20, (int) $this->family_members_count));

        if ($this->rsvp_party_type === 'family' && $count < 1) {
            $count = 1;
        }

        $this->family_members_count = $this->rsvp_party_type === 'family' ? $count : 0;
        $members = array_values($this->family_members);

        for ($index = 0; $index < $this->family_members_count; $index++) {
            $members[$index] ??= ['shirt_size' => null, 'shirt_type' => null];
        }

        $this->family_members = array_slice($members, 0, $this->family_members_count);
    }

    protected function resetRsvpParty(): void
    {
        $this->rsvp_party_type = 'self';
        $this->family_members_count = 0;
        $this->family_members = [];
    }

    protected function validateRsvpParty(Validator $validator): void
    {
        if ($this->rsvp_party_type === 'family' && (int) $this->family_members_count < 1) {
            $validator->errors()->add('family_members_count', __('Jumlah tambahan keluarga wajib diisi minimal 1.'));
        }

        if ($this->rsvp_status !== 'attending') {
            return;
        }

        if ($this->brings_private_vehicle === null || $this->brings_private_vehicle === '') {
            $validator->errors()->add('brings_private_vehicle', __('Pilihan kendaraan pribadi wajib diisi.'));
        }

        if (blank($this->shirt_size)) {
            $validator->errors()->add('shirt_size', __('Ukuran kaos alumni wajib diisi.'));
        }

        if (blank($this->shirt_type)) {
            $validator->errors()->add('shirt_type', __('Jenis kaos alumni wajib diisi.'));
        }

        if ($this->rsvp_party_type !== 'family') {
            return;
        }

        foreach ($this->family_members as $index => $member) {
            if (blank($member['shirt_size'] ?? null)) {
                $validator->errors()->add("family_members.{$index}.shirt_size", __('Ukuran kaos keluarga wajib diisi.'));
            }

            if (blank($member['shirt_type'] ?? null)) {
                $validator->errors()->add("family_members.{$index}.shirt_type", __('Jenis kaos keluarga wajib diisi.'));
            }
        }
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    protected function syncRsvpGuests(array $validated): void
    {
        if ($this->alumni === null) {
            return;
        }

        $this->alumni->rsvpGuests()->delete();

        if ($validated['rsvp_status'] !== 'attending' || $validated['rsvp_party_type'] !== 'family') {
            return;
        }

        collect($validated['family_members'] ?? [])
            ->take((int) $validated['family_members_count'])
            ->values()
            ->each(function (array $member, int $index): void {
                $this->alumni?->rsvpGuests()->create([
                    'sequence' => $index + 1,
                    'shirt_size' => $member['shirt_size'],
                    'shirt_type' => $member['shirt_type'],
                ]);
            });
    }

    /**
     * @return array<string, mixed>
     */
    protected function rsvpFormData(): array
    {
        return [
            'full_name' => $this->full_name,
            'nickname' => $this->nickname,
            'student_number' => $this->student_number,
            'email' => $this->email,
            'whatsapp_number' => $this->whatsapp_number,
            'rsvp_status' => $this->rsvp_status,
            'rsvp_party_type' => $this->rsvp_party_type,
            'family_members_count' => $this->family_members_count,
            'brings_private_vehicle' => $this->rsvp_status === 'attending'
                ? $this->brings_private_vehicle
                : null,
            'shirt_size' => blank($this->shirt_size) ? null : $this->shirt_size,
            'shirt_type' => blank($this->shirt_type) ? null : $this->shirt_type,
            'family_members' => collect($this->family_members)
                ->map(fn (array $member): array => [
                    'shirt_size' => blank($member['shirt_size'] ?? null) ? null : $member['shirt_size'],
                    'shirt_type' => blank($member['shirt_type'] ?? null) ? null : $member['shirt_type'],
                ])
                ->all(),
            'company' => $this->company,
            'job_title' => $this->job_title,
            'location_search' => $this->location_search,
            'city' => $this->city,
            'country' => $this->country,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'short_story' => $this->short_story,
            'memorable_story' => $this->memorable_story,
            'message_to_friends' => $this->message_to_friends,
            'special_notes' => $this->special_notes,
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'rsvp_party_type' => __('opsi kehadiran'),
            'family_members_count' => __('jumlah tambahan keluarga'),
            'brings_private_vehicle' => __('pilihan kendaraan pribadi'),
            'shirt_size' => __('ukuran kaos alumni'),
            'shirt_type' => __('jenis kaos alumni'),
            'family_members.*.shirt_size' => __('ukuran kaos keluarga'),
            'family_members.*.shirt_type' => __('jenis kaos keluarga'),
            'city' => __('kota domisili'),
            'country' => __('negara domisili'),
            'latitude' => __('koordinat latitude'),
            'longitude' => __('koordinat longitude'),
        ];
    }
}; ?>

<main class="min-h-screen bg-ktn-surface">
    <x-public-header />

    <section class="px-4 pb-12 pt-24 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl space-y-6">
            <div class="space-y-3 text-center">
                <p class="font-mono text-xs font-semibold uppercase tracking-[0.22em] text-ktn-forest">RSVP Publik</p>
                <flux:heading size="xl">{{ __('Konfirmasi Data Alumni') }}</flux:heading>
                <flux:text class="mx-auto max-w-2xl">
                    {{ __('Masukkan nomor WhatsApp yang terdaftar untuk membuka dan memperbarui data RSVP serta profil alumni.') }}
                </flux:text>
            </div>

            @if (! $this->isFormOpen())
                <div class="rounded-xl border border-ktn-sage/20 bg-white p-8 text-center shadow-sm">
                    <flux:heading size="lg">{{ __('Form RSVP sedang ditutup') }}</flux:heading>
                    <flux:text class="mt-2">{{ __('Panitia sedang menutup pengisian RSVP publik. Silakan hubungi panitia jika perlu memperbarui data.') }}</flux:text>
                </div>
            @else
                <form wire:submit="verifyWhatsappNumber" class="rounded-xl border border-ktn-sage/20 bg-white p-5 shadow-sm">
                    <div class="grid gap-4 sm:grid-cols-[1fr_auto] sm:items-end">
                        <flux:input wire:model="lookup_whatsapp_number" :label="__('Nomor WhatsApp terdaftar')" type="tel" :placeholder="__('Contoh: 6281234567890')" required />
                        <flux:button type="submit" variant="primary" icon="magnifying-glass" wire:loading.attr="disabled">
                            {{ __('Verifikasi') }}
                        </flux:button>
                    </div>
                </form>

                @if ($alumni)
                    <form wire:submit="save" class="space-y-6">
                        @if ($saved)
                            <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-800">
                                {{ __('Data berhasil disimpan. Anda masih bisa menyesuaikan isian lalu menyimpan kembali.') }}
                            </div>
                        @endif

                        <div class="rounded-xl border border-ktn-sage/20 bg-white p-5 shadow-sm">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <flux:heading size="lg">{{ __('Data Diri dan RSVP') }}</flux:heading>
                                    <flux:text>{{ __('Periksa kembali data yang akan digunakan panitia untuk reuni.') }}</flux:text>
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

                                <x-city-autocomplete
                                    city-model="city"
                                    country-model="country"
                                    latitude-model="latitude"
                                    longitude-model="longitude"
                                    search-model="location_search"
                                    :city="$city"
                                    :country="$country"
                                />

                                <flux:select wire:model.live="rsvp_status" :label="__('Status RSVP')">
                                    <flux:select.option value="pending">{{ __('Belum memastikan') }}</flux:select.option>
                                    <flux:select.option value="attending">{{ __('Insya Allah hadir') }}</flux:select.option>
                                    <flux:select.option value="not_attending">{{ __('Belum bisa hadir') }}</flux:select.option>
                                </flux:select>

                                @if ($rsvp_status === 'attending')
                                    <flux:select wire:model.live="rsvp_party_type" :label="__('Kehadiran')">
                                        <flux:select.option value="self">{{ __('Sendiri') }}</flux:select.option>
                                        <flux:select.option value="family">{{ __('Bersama keluarga') }}</flux:select.option>
                                    </flux:select>

                                    @if ($rsvp_party_type === 'family')
                                        <flux:input wire:model.live="family_members_count" :label="__('Jumlah tambahan keluarga')" type="number" min="1" max="20" />
                                    @endif

                                    <flux:radio.group
                                        wire:model="brings_private_vehicle"
                                        :label="__('Membawa kendaraan pribadi?')"
                                        variant="segmented"
                                        :invalid="$errors->has('brings_private_vehicle')"
                                    >
                                        <flux:radio value="1" :label="__('Ya')" />
                                        <flux:radio value="0" :label="__('Tidak')" />
                                    </flux:radio.group>
                                @endif

                                <flux:select wire:model="shirt_size" :label="__('Ukuran kaos alumni')">
                                    <flux:select.option value="">{{ __('Pilih ukuran') }}</flux:select.option>
                                    @foreach (['S', 'M', 'L', 'XL', 'XXL'] as $size)
                                        <flux:select.option :value="$size">{{ $size }}</flux:select.option>
                                    @endforeach
                                </flux:select>

                                <flux:select wire:model="shirt_type" :label="__('Jenis kaos alumni')">
                                    <flux:select.option value="">{{ __('Pilih jenis') }}</flux:select.option>
                                    <flux:select.option value="child">{{ __('Anak') }}</flux:select.option>
                                    <flux:select.option value="male">{{ __('Pria') }}</flux:select.option>
                                    <flux:select.option value="female">{{ __('Wanita') }}</flux:select.option>
                                </flux:select>
                            </div>

                            @if ($rsvp_status === 'attending' && $rsvp_party_type === 'family')
                                <div class="mt-6 rounded-lg border border-ktn-sage/20 bg-ktn-surface/60 p-4">
                                    <flux:heading size="sm">{{ __('Kaos Anggota Keluarga') }}</flux:heading>
                                    <flux:text class="mt-1">
                                        {{ __('Isi ukuran dan jenis kaos untuk anggota keluarga tambahan. Pasangan yang juga alumni tetap mengisi data sendiri; anak dicatat pada alumni pasangan laki-laki.') }}
                                    </flux:text>

                                    <div class="mt-4 grid gap-4">
                                        @foreach ($family_members as $index => $member)
                                            <div wire:key="public-family-shirt-{{ $index }}" class="grid gap-3 rounded-md border border-ktn-sage/20 bg-white p-4 lg:grid-cols-[8rem_1fr_1fr] lg:items-end">
                                                <div class="font-mono text-xs font-semibold uppercase tracking-[0.18em] text-ktn-muted">
                                                    {{ __('Keluarga :number', ['number' => $index + 1]) }}
                                                </div>

                                                <flux:select wire:model="family_members.{{ $index }}.shirt_size" :label="__('Ukuran kaos')">
                                                    <flux:select.option value="">{{ __('Pilih ukuran') }}</flux:select.option>
                                                    @foreach (['S', 'M', 'L', 'XL', 'XXL'] as $size)
                                                        <flux:select.option :value="$size">{{ $size }}</flux:select.option>
                                                    @endforeach
                                                </flux:select>

                                                <flux:select wire:model="family_members.{{ $index }}.shirt_type" :label="__('Jenis kaos')">
                                                    <flux:select.option value="">{{ __('Pilih jenis') }}</flux:select.option>
                                                    <flux:select.option value="child">{{ __('Anak') }}</flux:select.option>
                                                    <flux:select.option value="male">{{ __('Pria') }}</flux:select.option>
                                                    <flux:select.option value="female">{{ __('Wanita') }}</flux:select.option>
                                                </flux:select>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="rounded-xl border border-ktn-sage/20 bg-white p-5 shadow-sm">
                            <flux:heading size="lg">{{ __('Cerita dan Catatan') }}</flux:heading>
                            <flux:text>{{ __('Isian ini membantu panitia menyiapkan arsip dan kebutuhan acara.') }}</flux:text>

                            <div class="mt-5 grid gap-5">
                                <flux:textarea wire:model="short_story" :label="__('Cerita singkat saat ini')" rows="4" />
                                <flux:textarea wire:model="memorable_story" :label="__('Kenangan masa kuliah')" rows="4" />
                                <flux:textarea wire:model="message_to_friends" :label="__('Pesan untuk teman alumni')" rows="4" />
                                <flux:textarea wire:model="special_notes" :label="__('Catatan khusus untuk panitia')" rows="4" />
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled">
                                {{ __('Simpan Data RSVP') }}
                            </flux:button>
                        </div>
                    </form>
                @endif
            @endif
        </div>
    </section>
</main>
