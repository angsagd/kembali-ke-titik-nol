<?php

use App\Models\Alumni;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('RSVP')] class extends Component {
    public Alumni $alumni;

    public ?string $rsvp_status = null;

    public function mount(): void
    {
        $this->alumni = Auth::user()->alumni()->firstOrFail();
        $this->rsvp_status = $this->alumni->rsvp_status === 'pending'
            ? null
            : $this->alumni->rsvp_status;
    }

    public function saveRsvp(): void
    {
        $validated = $this->validate([
            'rsvp_status' => ['required', Rule::in(['attending', 'not_attending'])],
        ]);

        $this->alumni->update([
            'rsvp_status' => $validated['rsvp_status'],
        ]);
        $this->alumni->refresh();

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

                <flux:radio.group wire:model="rsvp_status" variant="cards" class="max-sm:flex-col" :invalid="$errors->has('rsvp_status')">
                    <flux:radio value="attending" icon="check-circle" :label="__('Hadir')" :description="__('Saya berencana hadir pada kegiatan reuni.')" />
                    <flux:radio value="not_attending" icon="x-circle" :label="__('Tidak Hadir')" :description="__('Saya belum dapat hadir pada kegiatan reuni.')" />
                </flux:radio.group>

                @error('rsvp_status')
                    <flux:text class="text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                @enderror

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
