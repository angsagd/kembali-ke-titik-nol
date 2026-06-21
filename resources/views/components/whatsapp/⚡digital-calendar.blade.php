<?php

use App\Models\WhatsappActivity;
use App\Models\WhatsappImport;
use App\Models\WhatsappMemberStat;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public int $importId;

    public ?int $selectedDigitalYear = null;

    public ?string $selectedDigitalDate = null;

    public function mount(int $importId): void
    {
        $this->importId = $importId;
        $this->selectedDigitalYear = $this->defaultDigitalYear();
    }

    public function selectDigitalYear(int $year): void
    {
        if (! in_array($year, $this->digitalYears(), true)) {
            return;
        }

        $this->selectedDigitalYear = $year;
        $this->selectedDigitalDate = null;
    }

    public function selectDigitalDate(string $date): void
    {
        if ($this->selectedDigitalYear === null || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return;
        }

        $clickedDate = CarbonImmutable::parse($date);

        if ((int) $clickedDate->format('Y') !== $this->selectedDigitalYear) {
            return;
        }

        $this->selectedDigitalDate = $date;
    }

    #[Computed]
    public function latestImport(): ?WhatsappImport
    {
        return WhatsappImport::query()->find($this->importId);
    }

    /**
     * @return array<int, int>
     */
    public function digitalYears(): array
    {
        if ($this->latestImport === null) {
            return [];
        }

        $firstYear = (int) ($this->latestImport->first_activity_at?->format('Y') ?? now()->format('Y'));
        $lastYear = (int) ($this->latestImport->last_activity_at?->format('Y') ?? $firstYear);

        return range($lastYear, $firstYear);
    }

    /**
     * @return array<int, array<int, array{date: string, day: int, in_year: bool, count: int, intensity: int, selected: bool}>>
     */
    public function digitalCalendarWeeks(): array
    {
        if ($this->latestImport === null) {
            return [];
        }

        $year = $this->selectedDigitalYear ?? $this->defaultDigitalYear();
        $start = CarbonImmutable::create($year, 1, 1)->startOfWeek();
        $end = CarbonImmutable::create($year, 12, 31)->endOfWeek();
        $counts = $this->digitalCalendarCounts($year);
        $max = max($counts ?: [1]);
        $weeks = [];
        $cursor = $start;

        while ($cursor->lessThanOrEqualTo($end)) {
            $week = [];

            for ($day = 0; $day < 7; $day++) {
                $date = $cursor->toDateString();
                $count = $counts[$date] ?? 0;

                $week[] = [
                    'date' => $date,
                    'day' => $day,
                    'in_year' => (int) $cursor->format('Y') === $year,
                    'count' => $count,
                    'intensity' => $this->calendarIntensity($count, $max),
                    'selected' => $this->selectedDigitalDate === $date,
                ];

                $cursor = $cursor->addDay();
            }

            $weeks[] = $week;
        }

        return $weeks;
    }

    /**
     * @return Collection<int, WhatsappActivity>
     */
    public function selectedDigitalDateActivities(): Collection
    {
        if ($this->latestImport === null || $this->selectedDigitalDate === null) {
            return new Collection();
        }

        return WhatsappActivity::query()
            ->with('whatsappMember:id,display_name')
            ->whereBelongsTo($this->latestImport)
            ->whereDate('occurred_at_display', $this->selectedDigitalDate)
            ->orderBy('occurred_at_display')
            ->get();
    }

    public function digitalCalendarDayClass(array $day): string
    {
        $classes = [
            'h-3 w-3 rounded-[2px] border transition hover:scale-125 focus:outline-none focus:ring-2 focus:ring-ktn-forest/40',
            $day['in_year'] ? 'cursor-pointer' : 'cursor-default opacity-25',
            $day['selected'] ? 'border-orange-500 ring-2 ring-orange-500' : 'border-transparent',
        ];

        $classes[] = match ($day['intensity']) {
            4 => 'bg-ktn-forest',
            3 => 'bg-ktn-forest/75',
            2 => 'bg-ktn-forest/50',
            1 => 'bg-ktn-forest/25',
            default => 'bg-zinc-100 dark:bg-zinc-800',
        };

        return implode(' ', $classes);
    }

    /**
     * @return array<int, string>
     */
    public function digitalCalendarMonthLabels(): array
    {
        return [
            __('Jan'),
            __('Feb'),
            __('Mar'),
            __('Apr'),
            __('Mei'),
            __('Jun'),
            __('Jul'),
            __('Agu'),
            __('Sep'),
            __('Okt'),
            __('Nov'),
            __('Des'),
        ];
    }

    public function digitalConversationAlignment(WhatsappActivity $activity): string
    {
        $memberId = $this->currentMappedWhatsappMemberId();

        if ($memberId === null || $activity->activity_type !== 'message') {
            return 'left';
        }

        return $activity->whatsapp_member_id === $memberId ? 'right' : 'left';
    }

    public function digitalConversationDateLabel(): string
    {
        if ($this->selectedDigitalDate === null) {
            return __('Pilih salah satu kotak hari untuk melihat percakapan.');
        }

        return CarbonImmutable::parse($this->selectedDigitalDate)->translatedFormat('d F Y');
    }

    public function digitalConversationText(WhatsappActivity $activity): string
    {
        if ($activity->activity_type === 'message') {
            return $activity->message_text ?: $activity->raw_text;
        }

        return $activity->raw_text;
    }

    public function isDigitalMediaActivity(WhatsappActivity $activity): bool
    {
        $text = str($activity->message_text ?? $activity->raw_text)->lower();

        return $activity->has_media
            || $activity->has_sticker
            || $text->contains('<media omitted>')
            || $text->contains('<sticker omitted>');
    }

    private function defaultDigitalYear(): ?int
    {
        return $this->latestImport?->last_activity_at
            ? (int) $this->latestImport->last_activity_at->format('Y')
            : null;
    }

    /**
     * @return array<string, int>
     */
    private function digitalCalendarCounts(int $year): array
    {
        if ($this->latestImport === null) {
            return [];
        }

        return $this->latestImport->dailyStats()
            ->whereYear('stat_date', $year)
            ->pluck('total_activities', 'stat_date')
            ->mapWithKeys(fn (mixed $count, mixed $date): array => [
                CarbonImmutable::parse((string) $date)->toDateString() => (int) $count,
            ])
            ->all();
    }

    private function calendarIntensity(int $count, int $max): int
    {
        if ($count <= 0) {
            return 0;
        }

        return max(1, min(4, (int) ceil(($count / max(1, $max)) * 4)));
    }

    private function currentMappedWhatsappMemberId(): ?int
    {
        if ($this->latestImport === null) {
            return null;
        }

        $alumniId = auth()->user()?->alumni()->value('id');

        if ($alumniId === null) {
            return null;
        }

        return WhatsappMemberStat::query()
            ->whereBelongsTo($this->latestImport)
            ->where('alumni_id', $alumniId)
            ->value('whatsapp_member_id');
    }
};
?>

<flux:card class="space-y-4">
    <div>
        <flux:heading size="lg">{{ __('Jejak Digital Tahunan') }}</flux:heading>
        <flux:text>{{ __('Layaknya buku ukur lapangan, setiap kotak menyimpan catatan aktivitas, observasi, dan dinamika jaringan alumni sepanjang tahun.') }}</flux:text>
    </div>

    @php
        $weeks = $this->digitalCalendarWeeks();
    @endphp
    <div class="overflow-x-auto pb-2">
        <div class="flex min-w-max gap-1">
            <div class="mr-1 grid grid-rows-7 gap-1 pt-0 text-[10px] font-medium leading-3 text-zinc-500 dark:text-zinc-400">
                <span>{{ __('Sen') }}</span>
                <span></span>
                <span>{{ __('Rab') }}</span>
                <span></span>
                <span>{{ __('Jum') }}</span>
                <span></span>
                <span>{{ __('Min') }}</span>
            </div>

            @foreach ($weeks as $weekIndex => $week)
                <div class="grid grid-rows-7 gap-1" wire:key="digital-week-{{ $selectedDigitalYear }}-{{ $weekIndex }}">
                    @foreach ($week as $day)
                        @if ($day['in_year'])
                            <button
                                type="button"
                                class="{{ $this->digitalCalendarDayClass($day) }}"
                                title="{{ Carbon\CarbonImmutable::parse($day['date'])->format('d/m/Y') }}: {{ number_format($day['count'], 0, ',', '.') }} aktivitas"
                                wire:click="selectDigitalDate('{{ $day['date'] }}')"
                                aria-label="{{ Carbon\CarbonImmutable::parse($day['date'])->format('d/m/Y') }}: {{ number_format($day['count'], 0, ',', '.') }} aktivitas"
                            ></button>
                        @else
                            <button
                                type="button"
                                class="{{ $this->digitalCalendarDayClass($day) }}"
                                title="{{ Carbon\CarbonImmutable::parse($day['date'])->format('d/m/Y') }}: {{ number_format($day['count'], 0, ',', '.') }} aktivitas"
                                aria-label="{{ Carbon\CarbonImmutable::parse($day['date'])->format('d/m/Y') }}: {{ number_format($day['count'], 0, ',', '.') }} aktivitas"
                                disabled
                            ></button>
                        @endif
                    @endforeach
                </div>
            @endforeach
        </div>
        <div class="ml-8 mt-2 grid min-w-max grid-cols-12 text-center text-[10px] font-semibold uppercase text-zinc-500 dark:text-zinc-400" style="width: {{ max(1, count($weeks)) * 16 }}px;">
            @foreach ($this->digitalCalendarMonthLabels() as $monthLabel)
                <span>{{ $monthLabel }}</span>
            @endforeach
        </div>
    </div>

    <div class="flex flex-wrap gap-2">
        @foreach ($this->digitalYears() as $year)
            <flux:button
                class="cursor-pointer"
                size="xs"
                variant="{{ $selectedDigitalYear === $year ? 'primary' : 'ghost' }}"
                wire:click="selectDigitalYear({{ $year }})"
            >
                {{ $year }}
            </flux:button>
        @endforeach
    </div>

    <div
        class="space-y-3 rounded-xl border border-zinc-200 bg-[#efe7d7] bg-top bg-repeat p-4 dark:border-zinc-700 dark:bg-zinc-900"
        style="background-image: url('{{ asset('images/wabg.png') }}'); background-size: cover;"
    >
        <div class="text-center text-xs font-semibold text-zinc-600 dark:text-zinc-300">
            {{ $this->digitalConversationDateLabel() }}
        </div>

        @php
            $selectedDayActivities = $this->selectedDigitalDateActivities();
        @endphp
        @if ($selectedDigitalDate && $selectedDayActivities->isNotEmpty())
            <div class="max-h-[32rem] space-y-3 overflow-y-auto pr-2">
                @foreach ($selectedDayActivities as $activity)
                    @php
                        $alignment = $this->digitalConversationAlignment($activity);
                    @endphp

                    @if ($activity->activity_type === 'system')
                        <div class="flex justify-center" wire:key="digital-chat-system-{{ $activity->id }}">
                            <div class="max-w-[85%] rounded-full bg-white/80 px-3 py-1 text-center text-xs font-medium text-zinc-600 shadow-sm dark:bg-zinc-800 dark:text-zinc-300">
                                {{ $this->digitalConversationText($activity) }}
                            </div>
                        </div>
                    @else
                        <div class="flex {{ $alignment === 'right' ? 'justify-end' : 'justify-start' }}" wire:key="digital-chat-message-{{ $activity->id }}">
                            <div class="max-w-[82%] rounded-2xl px-4 py-2 shadow-sm {{ $alignment === 'right' ? 'rounded-br-sm bg-[#d9fdd3] text-zinc-900' : 'rounded-bl-sm bg-white text-zinc-900 dark:bg-zinc-800 dark:text-zinc-100' }}">
                                <div class="mb-1 text-xs font-semibold {{ $alignment === 'right' ? 'text-ktn-forest' : 'text-orange-700 dark:text-orange-300' }}">
                                    {{ $activity->whatsappMember?->display_name ?? $activity->sender_name ?? '-' }}
                                </div>
                                @if ($this->isDigitalMediaActivity($activity))
                                    <div class="flex items-center gap-3 rounded-xl bg-zinc-100/80 p-3 text-zinc-700 dark:bg-zinc-700/70 dark:text-zinc-100">
                                        <svg class="size-8 shrink-0 text-ktn-forest dark:text-ktn-sage-light" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                        </svg>
                                        <div>
                                            <div class="text-sm font-semibold">{{ __('Dokumen multimedia') }}</div>
                                            <div class="text-xs text-zinc-500 dark:text-zinc-300">{{ $this->digitalConversationText($activity) }}</div>
                                        </div>
                                    </div>
                                @else
                                    <div class="whitespace-pre-wrap break-words text-sm leading-relaxed">{{ $this->digitalConversationText($activity) }}</div>
                                @endif
                                <div class="mt-1 text-right text-[10px] text-zinc-500">
                                    {{ $activity->occurred_at_display->format('H:i') }}
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @elseif ($selectedDigitalDate)
            <flux:text>{{ __('Tidak ada aktivitas pada tanggal ini.') }}</flux:text>
        @else
            <flux:text>{{ __('Klik salah satu kotak hari untuk membuka percakapan pada tanggal tersebut.') }}</flux:text>
        @endif
    </div>
</flux:card>
