<?php

use App\Models\Alumni;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('RSVP')] class extends Component {
    public Alumni $alumni;

    public ?string $rsvp_status = null;

    public string $rsvp_party_type = 'self';

    public int|string $family_members_count = 0;

    public int|string|null $brings_private_vehicle = null;

    public ?string $shirt_size = null;

    public ?string $shirt_type = null;

    /** @var array<int, array{shirt_size: string|null, shirt_type: string|null}> */
    public array $family_members = [];

    public function mount(): void
    {
        $this->alumni = Auth::user()->alumni()->with('rsvpGuests')->firstOrFail();
        $this->rsvp_status = $this->alumni->rsvp_status === 'pending'
            ? null
            : $this->alumni->rsvp_status;
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

    public function updatedRsvpStatus(): void
    {
        if ($this->rsvp_status !== 'attending') {
            $this->resetRsvpParty();
            $this->brings_private_vehicle = null;
        }
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

    public function saveRsvp(): void
    {
        $validated = ValidatorFacade::make($this->rsvpFormData(), [
            'rsvp_status' => ['required', Rule::in(['attending', 'not_attending'])],
            'rsvp_party_type' => ['required', Rule::in(['self', 'family'])],
            'family_members_count' => ['required', 'integer', 'min:0', 'max:20'],
            'brings_private_vehicle' => ['nullable', 'boolean'],
            'shirt_size' => ['nullable', Rule::in(['S', 'M', 'L', 'XL', 'XXL'])],
            'shirt_type' => ['nullable', Rule::in(['child', 'male', 'female'])],
            'family_members' => ['array'],
            'family_members.*.shirt_size' => ['nullable', Rule::in(['S', 'M', 'L', 'XL', 'XXL'])],
            'family_members.*.shirt_type' => ['nullable', Rule::in(['child', 'male', 'female'])],
        ], [], $this->validationAttributes())->after(function (Validator $validator): void {
            $this->validateRsvpParty($validator);
        })->validate();

        $this->alumni->update([
            'rsvp_status' => $validated['rsvp_status'],
            'rsvp_party_type' => $validated['rsvp_party_type'],
            'family_members_count' => $validated['rsvp_party_type'] === 'family' ? (int) $validated['family_members_count'] : 0,
            'brings_private_vehicle' => $validated['rsvp_status'] === 'attending'
                ? (bool) $validated['brings_private_vehicle']
                : null,
            'shirt_size' => $validated['shirt_size'],
            'shirt_type' => $validated['shirt_type'],
        ]);
        $this->syncRsvpGuests($validated);
        $this->alumni->refresh()->load('rsvpGuests');

        Flux::toast(variant: 'success', text: __('RSVP berhasil diperbarui.'));
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

    /**
     * @param  array<string, mixed>  $validated
     */
    protected function syncRsvpGuests(array $validated): void
    {
        $this->alumni->rsvpGuests()->delete();

        if ($validated['rsvp_status'] !== 'attending' || $validated['rsvp_party_type'] !== 'family') {
            return;
        }

        collect($validated['family_members'] ?? [])
            ->take((int) $validated['family_members_count'])
            ->values()
            ->each(function (array $member, int $index): void {
                $this->alumni->rsvpGuests()->create([
                    'sequence' => $index + 1,
                    'shirt_size' => $member['shirt_size'],
                    'shirt_type' => $member['shirt_type'],
                ]);
            });
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
     * @return array<string, mixed>
     */
    protected function rsvpFormData(): array
    {
        return [
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
        ];
    }
}; ?>

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="space-y-2">
            <flux:heading size="xl">{{ __('RSVP') }}</flux:heading>
            <flux:text class="max-w-3xl">
                {{ __('Konfirmasi status kehadiran Anda untuk reuni Teknik Geodesi UGM Angkatan 1996.') }}
            </flux:text>
        </div>

        <flux:badge color="{{ $this->rsvpStatusColor($alumni->rsvp_status) }}">
            {{ $this->rsvpStatusLabel($alumni->rsvp_status) }}
        </flux:badge>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_22rem]">
        <form wire:submit="saveRsvp" class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="space-y-5">
                <div>
                    <flux:heading size="lg">{{ __('Status Kehadiran') }}</flux:heading>
                    <flux:text class="mt-2">{{ __('Pilih salah satu status, lalu simpan perubahan RSVP Anda.') }}</flux:text>
                </div>

                <flux:radio.group wire:model.live="rsvp_status" variant="cards" class="max-sm:flex-col" :invalid="$errors->has('rsvp_status')">
                    <flux:radio value="attending" icon="check-circle" :label="__('Hadir')" :description="__('Saya berencana hadir pada kegiatan reuni.')" />
                    <flux:radio value="not_attending" icon="x-circle" :label="__('Tidak Hadir')" :description="__('Saya belum dapat hadir pada kegiatan reuni.')" />
                </flux:radio.group>

                @error('rsvp_status')
                    <flux:text class="text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                @enderror

                <div class="grid gap-5 lg:grid-cols-2">
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
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800">
                        <flux:heading size="sm">{{ __('Kaos Anggota Keluarga') }}</flux:heading>
                        <flux:text class="mt-1">
                            {{ __('Isi ukuran dan jenis kaos untuk anggota keluarga tambahan. Pasangan yang juga alumni tetap mengisi data sendiri; anak dicatat pada alumni pasangan laki-laki.') }}
                        </flux:text>

                        <div class="mt-4 grid gap-4">
                            @foreach ($family_members as $index => $member)
                                <div wire:key="private-family-shirt-{{ $index }}" class="grid gap-3 rounded-md border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900 lg:grid-cols-[8rem_1fr_1fr] lg:items-end">
                                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-zinc-500 dark:text-zinc-400">
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

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <flux:text>{{ __('Terakhir diperbarui: :date', ['date' => $alumni->updated_at?->translatedFormat('d F Y H:i')]) }}</flux:text>

                    <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled">
                        {{ __('Simpan RSVP') }}
                    </flux:button>
                </div>
            </div>
        </form>

        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg">{{ $alumni->full_name }}</flux:heading>
            <dl class="mt-5 grid gap-4">
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Nama panggilan') }}</dt>
                    <dd class="font-medium">{{ $alumni->nickname ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('NIM') }}</dt>
                    <dd class="font-medium">{{ $alumni->student_number ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Status saat ini') }}</dt>
                    <dd>
                        <flux:badge color="{{ $this->rsvpStatusColor($alumni->rsvp_status) }}">
                            {{ $this->rsvpStatusLabel($alumni->rsvp_status) }}
                        </flux:badge>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</section>
