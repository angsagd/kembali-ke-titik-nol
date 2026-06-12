<?php

use App\Models\AuditLog;
use App\Models\EventScheduleItem;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Rangkaian Acara')] class extends Component {
    public ?int $editing_id = null;

    public string $event_day = 'day_one';

    public string $start_time = '';

    public string $activity = '';

    #[Computed]
    public function scheduleItems(): Collection
    {
        return EventScheduleItem::query()
            ->orderByRaw("case event_day when 'day_one' then 1 else 2 end")
            ->orderBy('start_time')
            ->orderBy('id')
            ->get();
    }

    public function edit(EventScheduleItem $eventScheduleItem): void
    {
        $this->editing_id = $eventScheduleItem->id;
        $this->event_day = $eventScheduleItem->event_day;
        $this->start_time = substr($eventScheduleItem->start_time, 0, 5);
        $this->activity = $eventScheduleItem->activity;
        $this->resetErrorBag();
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'event_day' => ['required', Rule::in(['day_one', 'day_two'])],
            'start_time' => ['required', 'date_format:H:i'],
            'activity' => ['required', 'string', 'max:200'],
        ]);

        $eventScheduleItem = $this->editing_id
            ? EventScheduleItem::query()->findOrFail($this->editing_id)
            : new EventScheduleItem;

        $oldValues = $eventScheduleItem->exists
            ? $eventScheduleItem->only(['event_day', 'start_time', 'activity'])
            : null;

        $eventScheduleItem->fill($validated)->save();

        AuditLog::record(
            action: $oldValues === null ? 'event_schedule.created' : 'event_schedule.updated',
            entity: $eventScheduleItem,
            oldValues: $oldValues,
            newValues: $eventScheduleItem->only(['event_day', 'start_time', 'activity']),
        );

        $this->resetForm();
        unset($this->scheduleItems);

        Flux::toast(variant: 'success', text: __('Rangkaian acara disimpan.'));
    }

    public function delete(EventScheduleItem $eventScheduleItem): void
    {
        $oldValues = $eventScheduleItem->only(['event_day', 'start_time', 'activity']);
        $eventScheduleItem->delete();

        AuditLog::record(
            action: 'event_schedule.deleted',
            entity: $eventScheduleItem,
            oldValues: $oldValues,
        );

        if ($this->editing_id === $eventScheduleItem->id) {
            $this->resetForm();
        }

        unset($this->scheduleItems);
        Flux::toast(variant: 'success', text: __('Kegiatan dihapus.'));
    }

    /**
     * @return array{date: string, location: string, number: string}
     */
    public function dayDetails(string $eventDay): array
    {
        return match ($eventDay) {
            'day_two' => [
                'date' => __('Senin, 24 Agustus 2026'),
                'location' => __('Departemen Teknik Geodesi UGM'),
                'number' => '02',
            ],
            default => [
                'date' => __('Minggu, 23 Agustus 2026'),
                'location' => __('Penginapan Joglo / Kampung Wisata Tembi'),
                'number' => '01',
            ],
        };
    }

    protected function resetForm(): void
    {
        $this->reset(['editing_id', 'start_time', 'activity']);
        $this->event_day = 'day_one';
        $this->resetErrorBag();
    }
}; ?>

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="space-y-2">
        <flux:heading size="xl">{{ __('Rangkaian Acara') }}</flux:heading>
        <flux:text class="max-w-3xl">
            {{ __('Kelola waktu dan kegiatan yang tampil pada landing page. Hari, tanggal, dan lokasi acara bersifat tetap.') }}
        </flux:text>
    </div>

    <div class="grid gap-6 xl:grid-cols-[22rem_1fr]">
        <form wire:submit="save" class="h-fit rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div>
                <flux:heading size="lg">{{ $editing_id ? __('Edit Kegiatan') : __('Tambah Kegiatan') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Pilih hari acara, lalu isi waktu mulai dan nama kegiatan.') }}</flux:text>
            </div>

            <div class="mt-5 grid gap-5">
                <flux:select wire:model="event_day" :label="__('Hari acara')">
                    <flux:select.option value="day_one">{{ __('Hari 1 - 23 Agustus 2026') }}</flux:select.option>
                    <flux:select.option value="day_two">{{ __('Hari 2 - 24 Agustus 2026') }}</flux:select.option>
                </flux:select>

                <flux:input wire:model="start_time" :label="__('Waktu mulai')" type="time" />
                <flux:input wire:model="activity" :label="__('Kegiatan')" :placeholder="__('Contoh: Registrasi peserta')" />
            </div>

            <div class="mt-5 flex gap-3">
                <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled">
                    {{ $editing_id ? __('Simpan Perubahan') : __('Tambah Kegiatan') }}
                </flux:button>

                @if ($editing_id)
                    <flux:button type="button" variant="ghost" wire:click="cancelEdit">
                        {{ __('Batal') }}
                    </flux:button>
                @endif
            </div>
        </form>

        <div class="grid gap-5 lg:grid-cols-2">
            @foreach (['day_one', 'day_two'] as $eventDay)
                @php($details = $this->dayDetails($eventDay))

                <article class="rounded-lg border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-start gap-4">
                        <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-ktn-forest font-mono text-xs font-bold text-white">
                            {{ $details['number'] }}
                        </span>
                        <div>
                            <flux:heading size="lg">{{ $details['date'] }}</flux:heading>
                            <flux:text class="mt-1 font-mono text-xs uppercase tracking-[0.18em]">
                                {{ $details['location'] }}
                            </flux:text>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-3">
                        @forelse ($this->scheduleItems->where('event_day', $eventDay) as $item)
                            <div wire:key="event-schedule-{{ $item->id }}" class="grid grid-cols-[3.5rem_1fr_auto] items-center gap-3 rounded-md border border-zinc-200 p-3 dark:border-zinc-700">
                                <span class="font-mono text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                                    {{ $item->displayTime() }}
                                </span>
                                <span class="min-w-0 font-medium">{{ $item->activity }}</span>
                                <div class="flex">
                                    <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $item->id }})" :aria-label="__('Edit kegiatan')" />
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="trash"
                                        wire:click="delete({{ $item->id }})"
                                        wire:confirm="{{ __('Hapus kegiatan ini?') }}"
                                        :aria-label="__('Hapus kegiatan')"
                                    />
                                </div>
                            </div>
                        @empty
                            <div class="rounded-md border border-dashed border-zinc-300 p-6 text-center dark:border-zinc-700">
                                <flux:text>{{ __('Belum ada kegiatan pada hari ini.') }}</flux:text>
                            </div>
                        @endforelse
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
