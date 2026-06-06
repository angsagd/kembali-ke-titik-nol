<?php

use App\Models\RoomAssignment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Kamar Saya')] class extends Component {
    #[Computed]
    public function assignment(): ?RoomAssignment
    {
        return Auth::user()->alumni()
            ->firstOrFail()
            ->roomAssignment()
            ->with(['room.assignments.alumni'])
            ->first();
    }

    #[Computed]
    public function roommates(): Collection
    {
        if ($this->assignment === null) {
            return new Collection;
        }

        return $this->assignment->room->assignments
            ->sortBy(fn (RoomAssignment $assignment): string => $assignment->alumni?->full_name ?? '')
            ->values();
    }
}; ?>

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="space-y-2">
            <flux:heading size="xl">{{ __('Kamar Saya') }}</flux:heading>
            <flux:text class="max-w-3xl">
                {{ __('Informasi kamar dan teman sekamar selama kegiatan reuni.') }}
            </flux:text>
        </div>

        <flux:button variant="ghost" icon="check-circle" :href="route('alumni.rsvp')" wire:navigate>
            {{ __('RSVP') }}
        </flux:button>
    </div>

    @if ($this->assignment)
        <div class="grid gap-6 lg:grid-cols-[1fr_24rem]">
            <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <flux:heading size="lg">{{ $this->assignment->room->room_name }}</flux:heading>
                        <flux:text class="mt-2">{{ $this->assignment->room->room_type ?: __('Tipe kamar belum dicatat') }}</flux:text>
                    </div>

                    <flux:badge color="green">
                        {{ __(':count/:capacity penghuni', ['count' => $this->roommates->count(), 'capacity' => $this->assignment->room->capacity]) }}
                    </flux:badge>
                </div>

                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Kapasitas') }}</dt>
                        <dd class="font-medium">{{ $this->assignment->room->capacity }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Lokasi') }}</dt>
                        <dd class="font-medium">{{ $this->assignment->room->location_notes ?: '-' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Catatan kamar') }}</dt>
                        <dd class="font-medium whitespace-pre-line">{{ $this->assignment->room->notes ?: '-' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Catatan assignment') }}</dt>
                        <dd class="font-medium whitespace-pre-line">{{ $this->assignment->notes ?: '-' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg">{{ __('Penghuni Kamar') }}</flux:heading>
                <div class="mt-5 grid gap-3">
                    @foreach ($this->roommates as $roomAssignment)
                        <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700" wire:key="roommate-{{ $roomAssignment->id }}">
                            <div class="font-medium">{{ $roomAssignment->alumni?->full_name }}</div>
                            <flux:text>{{ $roomAssignment->alumni?->student_number ?: __('NIM belum diisi') }}</flux:text>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="rounded-lg border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-700">
            <flux:heading size="lg">{{ __('Belum ada data kamar') }}</flux:heading>
            <flux:text class="mt-2">{{ __('Panitia belum menetapkan kamar untuk akun alumni ini.') }}</flux:text>
        </div>
    @endif
</section>
