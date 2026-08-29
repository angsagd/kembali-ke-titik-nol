@props(['scheduleItems'])

@php
    $eventDays = [
        [
            'key' => 'day_one',
            'number' => '01',
            'date' => __('Minggu, 23 Agustus 2026'),
            'location' => __('Penginapan Joglo / Kampung Wisata Tembi'),
        ],
        [
            'key' => 'day_two',
            'number' => '02',
            'date' => __('Senin, 24 Agustus 2026'),
            'location' => __('Departemen Teknik Geodesi UGM'),
        ],
    ];
@endphp

<div {{ $attributes->class(['grid gap-5 lg:grid-cols-2']) }}>
    @foreach ($eventDays as $eventDay)
        <article class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-start gap-4">
                <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-ktn-forest font-mono text-xs font-bold text-white">
                    {{ $eventDay['number'] }}
                </span>
                <div>
                    <flux:heading size="lg">{{ $eventDay['date'] }}</flux:heading>
                    <flux:text class="mt-1 font-mono text-xs uppercase tracking-[0.16em]">
                        {{ $eventDay['location'] }}
                    </flux:text>
                </div>
            </div>

            <div class="mt-6 grid gap-3">
                @forelse ($scheduleItems->where('event_day', $eventDay['key']) as $scheduleItem)
                    <div class="grid grid-cols-[3.5rem_1fr] items-start gap-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700" wire:key="reunion-schedule-{{ $scheduleItem->id }}">
                        <span class="font-mono text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                            {{ $scheduleItem->displayTime() }}
                        </span>
                        <span class="font-medium">{{ $scheduleItem->activity }}</span>
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-zinc-300 p-5 text-center dark:border-zinc-700">
                        <flux:text>{{ __('Belum ada rangkaian acara yang dicatat untuk hari ini.') }}</flux:text>
                    </div>
                @endforelse
            </div>
        </article>
    @endforeach
</div>
