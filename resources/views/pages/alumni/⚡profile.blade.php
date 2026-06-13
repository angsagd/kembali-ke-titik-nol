<?php

use App\Models\Alumni;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

new #[Title('Profil Alumni')] class extends Component {
    use WithFileUploads;

    public Alumni $alumni;

    public ?TemporaryUploadedFile $college_photo = null;

    public ?TemporaryUploadedFile $current_photo = null;

    public string $full_name = '';

    public ?string $nickname = null;

    public ?string $student_number = null;

    public ?string $email = null;

    public string $whatsapp_number = '';

    public string $rsvp_status = 'pending';

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

    public function mount(): void
    {
        $this->alumni = Auth::user()->alumni()
            ->with(['user.role'])
            ->firstOrFail();

        $this->fillForm();
    }

    public function fillForm(): void
    {
        $this->full_name = $this->alumni->full_name;
        $this->nickname = $this->alumni->nickname;
        $this->student_number = $this->alumni->student_number;
        $this->email = $this->alumni->email;
        $this->whatsapp_number = $this->alumni->user?->whatsapp_number ?? '';
        $this->rsvp_status = $this->alumni->rsvp_status;
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
    }

    public function updateProfile(): void
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
            'rsvp_status' => ['required', Rule::in(['pending', 'attending', 'not_attending'])],
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
        ]);

        DB::transaction(function () use ($validated): void {
            $isProfileCompleted = filled($validated['full_name'])
                && filled($validated['whatsapp_number'])
                && filled($validated['rsvp_status'])
                && (filled($validated['short_story']) || filled($validated['memorable_story']) || filled($validated['message_to_friends']));

            $this->alumni->update([
                'full_name' => $validated['full_name'],
                'nickname' => $validated['nickname'],
                'student_number' => $validated['student_number'],
                'email' => $validated['email'],
                'rsvp_status' => $validated['rsvp_status'],
                'company' => $validated['company'],
                'job_title' => $validated['job_title'],
                'city' => $validated['city'],
                'country' => $validated['country'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'short_story' => $validated['short_story'],
                'memorable_story' => $validated['memorable_story'],
                'message_to_friends' => $validated['message_to_friends'],
                'is_profile_completed' => $isProfileCompleted,
            ]);

            $this->alumni->user?->forceFill([
                'name' => $validated['full_name'],
                'whatsapp_number' => $validated['whatsapp_number'],
                'email' => $validated['email'] ?: $this->alumni->user->email,
            ])->save();
        });

        $this->alumni = $this->alumni->fresh(['user.role']);
        $this->fillForm();

        Flux::toast(variant: 'success', text: __('Profil alumni diperbarui.'));
    }

    public function updateMemoryBookPhotos(): void
    {
        $this->validate([
            'college_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'current_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($this->college_photo === null && $this->current_photo === null) {
            $this->addError('college_photo', __('Pilih minimal satu foto untuk diunggah.'));

            return;
        }

        $oldCollegePhotoPath = $this->alumni->college_photo_path;
        $oldCurrentPhotoPath = $this->alumni->current_photo_path;
        $collegePhotoPath = $this->college_photo?->store('alumni/memory-book/college', 'public');
        $currentPhotoPath = $this->current_photo?->store('alumni/memory-book/current', 'public');

        $this->alumni->update([
            'college_photo_path' => $collegePhotoPath ?: $oldCollegePhotoPath,
            'current_photo_path' => $currentPhotoPath ?: $oldCurrentPhotoPath,
        ]);

        if ($collegePhotoPath && $oldCollegePhotoPath) {
            Storage::disk('public')->delete($oldCollegePhotoPath);
        }

        if ($currentPhotoPath && $oldCurrentPhotoPath) {
            Storage::disk('public')->delete($oldCurrentPhotoPath);
        }

        $this->alumni->refresh();
        $this->reset('college_photo', 'current_photo');

        Flux::toast(variant: 'success', text: __('Foto buku kenangan diperbarui.'));
    }

    public function memoryPhotoUrl(?string $path): ?string
    {
        return $path ? Storage::disk('public')->url($path) : null;
    }
};
?>

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="space-y-2">
            <flux:heading size="xl">{{ __('Profil Alumni') }}</flux:heading>
            <flux:text class="max-w-3xl">
                {{ __('Lengkapi data diri, RSVP, cerita singkat, kenangan, dan pesan untuk teman seangkatan. Data ini menjadi dasar direktori dan arsip digital Kembali ke Titik Nol.') }}
            </flux:text>
        </div>

        <div class="flex flex-wrap gap-2">
            <flux:badge color="{{ $alumni->rsvp_status === 'attending' ? 'green' : ($alumni->rsvp_status === 'not_attending' ? 'red' : 'amber') }}">
                {{ match ($alumni->rsvp_status) {
                    'attending' => __('Hadir'),
                    'not_attending' => __('Tidak hadir'),
                    default => __('RSVP Pending'),
                } }}
            </flux:badge>
            <flux:badge color="{{ $alumni->is_profile_completed ? 'green' : 'amber' }}">
                {{ $alumni->is_profile_completed ? __('Profil Lengkap') : __('Profil Belum Lengkap') }}
            </flux:badge>
        </div>
    </div>

    <form wire:submit="updateMemoryBookPhotos" class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="lg">{{ __('Foto Buku Kenangan') }}</flux:heading>
                <flux:text>{{ __('Unggah foto masa kuliah dan foto saat ini untuk ditampilkan pada buku kenangan.') }}</flux:text>
            </div>

            <flux:button type="submit" variant="primary" icon="photo" wire:loading.attr="disabled">
                {{ __('Simpan Foto') }}
            </flux:button>
        </div>

        <div class="mt-5 grid gap-5 md:grid-cols-2">
            <div class="space-y-4">
                <div class="aspect-[4/5] overflow-hidden rounded-lg border border-zinc-200 bg-ktn-instrument dark:border-zinc-700">
                    @if ($college_photo)
                        <img src="{{ $college_photo->temporaryUrl() }}" alt="{{ __('Preview foto masa kuliah') }}" class="size-full object-cover">
                    @elseif ($this->memoryPhotoUrl($alumni->college_photo_path))
                        <img src="{{ $this->memoryPhotoUrl($alumni->college_photo_path) }}" alt="{{ __('Foto masa kuliah') }}" class="size-full object-cover">
                    @else
                        <div class="flex size-full items-center justify-center p-6 text-center text-sm font-medium text-ktn-muted">
                            {{ __('Foto masa kuliah belum diunggah') }}
                        </div>
                    @endif
                </div>

                <flux:input wire:model="college_photo" :label="__('Foto masa kuliah')" type="file" accept="image/jpeg,image/png,image/webp" />
            </div>

            <div class="space-y-4">
                <div class="aspect-[4/5] overflow-hidden rounded-lg border border-zinc-200 bg-ktn-forest dark:border-zinc-700">
                    @if ($current_photo)
                        <img src="{{ $current_photo->temporaryUrl() }}" alt="{{ __('Preview foto saat ini') }}" class="size-full object-cover">
                    @elseif ($this->memoryPhotoUrl($alumni->current_photo_path))
                        <img src="{{ $this->memoryPhotoUrl($alumni->current_photo_path) }}" alt="{{ __('Foto saat ini') }}" class="size-full object-cover">
                    @else
                        <div class="flex size-full items-center justify-center p-6 text-center text-sm font-medium text-white">
                            {{ __('Foto saat ini belum diunggah') }}
                        </div>
                    @endif
                </div>

                <flux:input wire:model="current_photo" :label="__('Foto saat ini')" type="file" accept="image/jpeg,image/png,image/webp" />
            </div>
        </div>

        <flux:text class="mt-4">{{ __('Format JPG, PNG, atau WebP. Maksimal 5 MB per foto.') }}</flux:text>
    </form>

    <form wire:submit="updateProfile" class="space-y-6">
        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <flux:heading size="lg">{{ __('Data Diri') }}</flux:heading>
                    <flux:text>{{ __('Nama, kontak, NIM, dan informasi pekerjaan saat ini.') }}</flux:text>
                </div>

                <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled">
                    {{ __('Simpan Profil') }}
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

                <flux:select wire:model="rsvp_status" :label="__('Status RSVP')">
                    <flux:select.option value="pending">{{ __('Belum memastikan') }}</flux:select.option>
                    <flux:select.option value="attending">{{ __('Insya Allah hadir') }}</flux:select.option>
                    <flux:select.option value="not_attending">{{ __('Belum bisa hadir') }}</flux:select.option>
                </flux:select>
            </div>
        </div>

        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg">{{ __('Cerita dan Kenangan') }}</flux:heading>
            <flux:text>{{ __('Bagian ini akan digunakan untuk halaman profil dan arsip nostalgia alumni.') }}</flux:text>

            <div class="mt-5 grid gap-5">
                <flux:textarea wire:model="short_story" :label="__('Cerita singkat saat ini')" rows="4" />
                <flux:textarea wire:model="memorable_story" :label="__('Kenangan masa kuliah')" rows="4" />
                <flux:textarea wire:model="message_to_friends" :label="__('Pesan untuk teman alumni')" rows="4" />
            </div>
        </div>

        <div class="flex justify-end">
            <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled">
                {{ __('Simpan Profil') }}
            </flux:button>
        </div>
    </form>
</section>
