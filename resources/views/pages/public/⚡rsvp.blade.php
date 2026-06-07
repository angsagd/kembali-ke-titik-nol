<?php

use App\Models\Alumni;
use App\Models\ApplicationSetting;
use App\Models\City;
use App\Models\Country;
use App\Models\User;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
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

    public ?string $company = null;

    public ?string $job_title = null;

    public ?int $current_country_id = null;

    public ?int $current_city_id = null;

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

    public function save(): void
    {
        abort_unless($this->isFormOpen(), 403);

        if ($this->alumni === null) {
            $this->addError('lookup_whatsapp_number', __('Verifikasi nomor WhatsApp terlebih dahulu.'));

            return;
        }

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
            'rsvp_status' => ['required', Rule::in(['pending', 'attending', 'not_attending'])],
            'company' => ['nullable', 'string', 'max:150'],
            'job_title' => ['nullable', 'string', 'max:150'],
            'current_country_id' => ['nullable', Rule::exists(Country::class, 'id')],
            'current_city_id' => [
                'nullable',
                Rule::exists(City::class, 'id')
                    ->where(fn ($query) => $query->where('country_id', $this->current_country_id ?? 0)),
            ],
            'short_story' => ['nullable', 'string', 'max:5000'],
            'memorable_story' => ['nullable', 'string', 'max:5000'],
            'message_to_friends' => ['nullable', 'string', 'max:5000'],
            'special_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        DB::transaction(function () use ($validated): void {
            $isProfileCompleted = filled($validated['full_name'])
                && filled($validated['whatsapp_number'])
                && filled($validated['rsvp_status'])
                && (filled($validated['short_story']) || filled($validated['memorable_story']) || filled($validated['message_to_friends']));

            $this->alumni?->update([
                'full_name' => $validated['full_name'],
                'nickname' => $validated['nickname'],
                'student_number' => $validated['student_number'],
                'email' => $validated['email'],
                'rsvp_status' => $validated['rsvp_status'],
                'company' => $validated['company'],
                'job_title' => $validated['job_title'],
                'current_country_id' => $validated['current_country_id'],
                'current_city_id' => $validated['current_city_id'],
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
        });

        $this->alumni = $this->alumni?->fresh(['user']);
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
        $this->company = $this->alumni->company;
        $this->job_title = $this->alumni->job_title;
        $this->current_country_id = $this->alumni->current_country_id;
        $this->current_city_id = $this->alumni->current_city_id;
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
            'company',
            'job_title',
            'current_country_id',
            'current_city_id',
            'short_story',
            'memorable_story',
            'message_to_friends',
            'special_notes',
        ]);
        $this->rsvp_status = 'pending';
    }
}; ?>

<main class="min-h-screen bg-ktn-surface">
    <header class="border-b border-ktn-sage/20 bg-white">
        <nav class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/icon/favicon96.png') }}" alt="Logo Geodesi 96" class="size-9 rounded-lg border border-ktn-forest/20 bg-white object-contain p-1">
                <span class="font-display text-lg font-extrabold tracking-tight text-ktn-forest">Geodesi 96</span>
            </a>
            <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-lg bg-ktn-forest px-4 py-2.5 text-sm font-bold text-white transition hover:bg-ktn-forest-strong">
                Landing
            </a>
        </nav>
    </header>

    <section class="px-4 py-12 sm:px-6 lg:px-8">
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

                                <flux:select wire:model.live="current_country_id" :label="__('Negara domisili')">
                                    <flux:select.option value="">{{ __('Belum diisi') }}</flux:select.option>
                                    @foreach ($this->countries as $country)
                                        <flux:select.option :value="$country->id">{{ $country->name }}</flux:select.option>
                                    @endforeach
                                </flux:select>

                                <flux:select wire:model="current_city_id" :label="__('Kota domisili')" wire:key="public-rsvp-city-{{ $current_country_id ?: 'none' }}">
                                    <flux:select.option value="">{{ __('Belum diisi') }}</flux:select.option>
                                    @foreach ($this->cities as $city)
                                        <flux:select.option :value="$city->id">{{ $city->name }}</flux:select.option>
                                    @endforeach
                                </flux:select>

                                <flux:select wire:model="rsvp_status" :label="__('Status RSVP')">
                                    <flux:select.option value="pending">{{ __('Belum memastikan') }}</flux:select.option>
                                    <flux:select.option value="attending">{{ __('Insya Allah hadir') }}</flux:select.option>
                                    <flux:select.option value="not_attending">{{ __('Belum bisa hadir') }}</flux:select.option>
                                </flux:select>
                            </div>
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
