<?php

use App\Models\Alumni;
use App\Models\Room;
use App\Models\RoomAssignment;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Rooming')] class extends Component {
    public ?int $selected_room_id = null;

    public string $room_name = '';

    public ?string $room_type = null;

    public int|string $capacity = 2;

    public ?string $location_notes = null;

    public ?string $notes = null;

    public ?int $assignment_alumni_id = null;

    public ?string $assignment_notes = null;

    public function mount(): void
    {
        $this->selectRoom(Room::query()->orderBy('room_name')->value('id'));
    }

    #[Computed]
    public function rooms(): Collection
    {
        return Room::query()
            ->with(['assignments.alumni'])
            ->withCount('assignments')
            ->orderBy('room_name')
            ->get();
    }

    #[Computed]
    public function selectedRoom(): ?Room
    {
        if ($this->selected_room_id === null) {
            return null;
        }

        return Room::query()
            ->with(['assignments.alumni'])
            ->withCount('assignments')
            ->find($this->selected_room_id);
    }

    #[Computed]
    public function availableAlumni(): Collection
    {
        return Alumni::query()
            ->with('user')
            ->whereDoesntHave('roomAssignment')
            ->where('alumni_status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'user_id', 'full_name', 'student_number']);
    }

    /**
     * @return array<string, int>
     */
    #[Computed]
    public function summary(): array
    {
        $capacity = (int) Room::query()->sum('capacity');
        $assigned = RoomAssignment::query()->count();

        return [
            'rooms' => Room::query()->count(),
            'capacity' => $capacity,
            'assigned' => $assigned,
            'available' => max(0, $capacity - $assigned),
        ];
    }

    public function selectRoom(?int $roomId): void
    {
        $this->selected_room_id = $roomId;
        $this->assignment_alumni_id = null;
        $this->assignment_notes = null;

        $room = $this->selectedRoom;

        $this->room_name = $room?->room_name ?? '';
        $this->room_type = $room?->room_type;
        $this->capacity = $room?->capacity ?? 2;
        $this->location_notes = $room?->location_notes;
        $this->notes = $room?->notes;
    }

    public function newRoom(): void
    {
        $this->selected_room_id = null;
        $this->room_name = '';
        $this->room_type = null;
        $this->capacity = 2;
        $this->location_notes = null;
        $this->notes = null;
        $this->assignment_alumni_id = null;
        $this->assignment_notes = null;
    }

    public function saveRoom(): void
    {
        $validated = $this->validate([
            'room_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique(Room::class, 'room_name')->ignore($this->selectedRoom),
            ],
            'room_type' => ['nullable', 'string', 'max:100'],
            'capacity' => ['required', 'integer', 'min:1', 'max:20'],
            'location_notes' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($this->selectedRoom && (int) $validated['capacity'] < $this->selectedRoom->assignments_count) {
            $this->addError('capacity', __('Kapasitas tidak boleh lebih kecil dari jumlah penghuni saat ini.'));

            return;
        }

        $room = Room::query()->updateOrCreate(
            ['id' => $this->selected_room_id],
            $validated,
        );

        $this->selectRoom($room->id);
        unset($this->rooms, $this->selectedRoom, $this->summary);

        Flux::toast(variant: 'success', text: __('Kamar disimpan.'));
    }

    public function assignAlumni(): void
    {
        $validated = $this->validate([
            'selected_room_id' => ['required', Rule::exists(Room::class, 'id')],
            'assignment_alumni_id' => [
                'required',
                Rule::exists(Alumni::class, 'id'),
                Rule::unique(RoomAssignment::class, 'alumni_id'),
            ],
            'assignment_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $room = Room::query()
            ->withCount('assignments')
            ->findOrFail($validated['selected_room_id']);

        if ($room->assignments_count >= $room->capacity) {
            $this->addError('assignment_alumni_id', __('Kamar sudah penuh.'));

            return;
        }

        RoomAssignment::query()->create([
            'room_id' => $validated['selected_room_id'],
            'alumni_id' => $validated['assignment_alumni_id'],
            'assigned_by' => Auth::id(),
            'notes' => $validated['assignment_notes'],
        ]);

        $this->assignment_alumni_id = null;
        $this->assignment_notes = null;
        unset($this->rooms, $this->selectedRoom, $this->availableAlumni, $this->summary);

        Flux::toast(variant: 'success', text: __('Alumni ditempatkan ke kamar.'));
    }

    public function removeAssignment(int $assignmentId): void
    {
        RoomAssignment::query()
            ->whereKey($assignmentId)
            ->delete();

        unset($this->rooms, $this->selectedRoom, $this->availableAlumni, $this->summary);

        Flux::toast(variant: 'success', text: __('Assignment kamar dihapus.'));
    }
}; ?>

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="space-y-2">
        <flux:heading size="xl">{{ __('Rooming') }}</flux:heading>
        <flux:text class="max-w-3xl">
            {{ __('Kelola daftar kamar penginapan dan penempatan alumni ke kamar reuni.') }}
        </flux:text>
    </div>

    <div class="flex flex-wrap gap-2 lg:justify-end">
        <flux:button variant="ghost" icon="arrow-down-tray" :href="route('reports.rooming.export')">
            {{ __('Export CSV') }}
        </flux:button>
        <flux:button variant="ghost" icon="printer" :href="route('reports.rooming.print')" target="_blank">
            {{ __('Cetak Rooming List') }}
        </flux:button>
        <flux:button variant="primary" icon="plus" wire:click="newRoom">
            {{ __('Tambah Kamar') }}
        </flux:button>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <flux:card>
            <flux:text>{{ __('Total Kamar') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['rooms'] }}</div>
        </flux:card>
        <flux:card>
            <flux:text>{{ __('Total Kapasitas') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['capacity'] }}</div>
        </flux:card>
        <flux:card>
            <flux:text>{{ __('Sudah Ditempatkan') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['assigned'] }}</div>
        </flux:card>
        <flux:card>
            <flux:text>{{ __('Sisa Kapasitas') }}</flux:text>
            <div class="mt-2 text-3xl font-semibold tabular-nums">{{ $this->summary['available'] }}</div>
        </flux:card>
    </div>

    <div class="grid gap-6 xl:grid-cols-[24rem_1fr]">
        <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg">{{ __('Daftar Kamar') }}</flux:heading>
            <div class="mt-5 grid gap-2">
                @forelse ($this->rooms as $room)
                    <button
                        type="button"
                        wire:key="room-{{ $room->id }}"
                        wire:click="selectRoom({{ $room->id }})"
                        class="rounded-md border p-3 text-left transition hover:bg-zinc-50 dark:hover:bg-zinc-800 {{ $selected_room_id === $room->id ? 'border-amber-500 bg-amber-50 dark:border-amber-400 dark:bg-amber-950/30' : 'border-zinc-200 dark:border-zinc-700' }}"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-medium">{{ $room->room_name }}</div>
                                <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $room->room_type ?: __('Tipe belum dicatat') }}</div>
                            </div>
                            <flux:badge color="{{ $room->assignments_count >= $room->capacity ? 'red' : 'green' }}">
                                {{ $room->assignments_count }}/{{ $room->capacity }}
                            </flux:badge>
                        </div>
                    </button>
                @empty
                    <flux:text>{{ __('Belum ada kamar.') }}</flux:text>
                @endforelse
            </div>
        </div>

        <div class="space-y-6">
            <form wire:submit="saveRoom" class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <flux:heading size="lg">{{ $selected_room_id ? __('Edit Kamar') : __('Tambah Kamar') }}</flux:heading>
                        <flux:text>{{ __('Catat nama kamar, kapasitas, dan catatan lokasi.') }}</flux:text>
                    </div>
                    <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled">
                        {{ __('Simpan Kamar') }}
                    </flux:button>
                </div>

                <div class="mt-5 grid gap-5 lg:grid-cols-2">
                    <flux:input wire:model="room_name" :label="__('Nama kamar')" />
                    <flux:input wire:model="room_type" :label="__('Tipe kamar')" />
                    <flux:input wire:model="capacity" :label="__('Kapasitas')" type="number" min="1" max="20" />
                    <flux:input wire:model="location_notes" :label="__('Catatan lokasi')" />
                    <flux:textarea wire:model="notes" :label="__('Catatan kamar')" rows="3" class="lg:col-span-2" />
                </div>
            </form>

            @if ($this->selectedRoom)
                <form wire:submit="assignAlumni" class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <flux:heading size="lg">{{ __('Assign Alumni') }}</flux:heading>
                            <flux:text>{{ __('Tempatkan alumni aktif yang belum memiliki kamar.') }}</flux:text>
                        </div>
                        <flux:button type="submit" variant="primary" icon="user-plus" wire:loading.attr="disabled">
                            {{ __('Assign') }}
                        </flux:button>
                    </div>

                    <div class="mt-5 grid gap-5 lg:grid-cols-2">
                        <flux:select wire:model="assignment_alumni_id" :label="__('Alumni')">
                            <flux:select.option value="">{{ __('Pilih alumni') }}</flux:select.option>
                            @foreach ($this->availableAlumni as $profile)
                                <flux:select.option value="{{ $profile->id }}">
                                    {{ $profile->full_name }}{{ $profile->student_number ? ' - '.$profile->student_number : '' }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>

                        <flux:textarea wire:model="assignment_notes" :label="__('Catatan assignment')" rows="2" />
                    </div>
                </form>

                <div class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:heading size="lg">{{ __('Penghuni :room', ['room' => $this->selectedRoom->room_name]) }}</flux:heading>
                    <div class="mt-5 grid gap-3">
                        @forelse ($this->selectedRoom->assignments as $assignment)
                            <div class="flex flex-col gap-3 rounded-lg border border-zinc-200 p-3 sm:flex-row sm:items-start sm:justify-between dark:border-zinc-700" wire:key="room-assignment-{{ $assignment->id }}">
                                <div>
                                    <div class="font-medium">{{ $assignment->alumni?->full_name }}</div>
                                    <flux:text>{{ $assignment->alumni?->student_number ?: __('NIM belum diisi') }}</flux:text>
                                    @if ($assignment->notes)
                                        <flux:text>{{ $assignment->notes }}</flux:text>
                                    @endif
                                </div>
                                <flux:button size="sm" variant="danger" icon="trash" wire:click="removeAssignment({{ $assignment->id }})" wire:confirm="{{ __('Hapus assignment kamar ini?') }}">
                                    {{ __('Hapus') }}
                                </flux:button>
                            </div>
                        @empty
                            <flux:text>{{ __('Belum ada penghuni kamar.') }}</flux:text>
                        @endforelse
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
