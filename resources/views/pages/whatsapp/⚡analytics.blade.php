<?php

use App\Models\WhatsappActivity;
use App\Models\WhatsappImport;
use App\Models\WhatsappMemberEventStat;
use App\Models\WhatsappMemberStat;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('WhatsApp Analytics')] class extends Component {
    public string $tab = 'group';

    public function selectTab(string $tab): void
    {
        if (in_array($tab, ['group', 'top10', 'personal'], true)) {
            $this->tab = $tab;
        }
    }

    #[Computed]
    public function latestImport(): ?WhatsappImport
    {
        return WhatsappImport::query()
            ->where('status', 'completed')
            ->latest('processed_at')
            ->first();
    }

    /**
     * @return Collection<int, WhatsappMemberStat>
     */
    public function topMembers(string $metric = 'total_messages', int $limit = 10): Collection
    {
        if ($this->latestImport === null || ! in_array($metric, $this->memberStatMetricKeys(), true)) {
            return new Collection();
        }

        return WhatsappMemberStat::query()
            ->with('whatsappMember:id,display_name')
            ->whereBelongsTo($this->latestImport)
            ->where($metric, '>', 0)
            ->orderByDesc($metric)
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, WhatsappMemberEventStat>
     */
    public function topEventMembers(string $metric, int $limit = 10): Collection
    {
        if ($this->latestImport === null) {
            return new Collection();
        }

        return WhatsappMemberEventStat::query()
            ->with('whatsappMember:id,display_name')
            ->whereBelongsTo($this->latestImport)
            ->orderByDesc($metric)
            ->limit($limit)
            ->get();
    }

    /**
     * @return array<int, array{metric: string, title: string, description: string}>
     */
    public function topMetricDefinitions(): array
    {
        return [
            [
                'metric' => 'total_messages',
                'title' => __('Top 10 Tukang Ketik'),
                'description' => __('Mereka bukan sekadar anggota grup. Mereka adalah mesin penggerak percakapan.'),
            ],
            [
                'metric' => 'media_messages',
                'title' => __('Top 10 Juragan Dokumentasi'),
                'description' => __('Foto, video, dokumen, dan segala bentuk kenangan visual biasanya lewat tangan mereka.'),
            ],
            [
                'metric' => 'sticker_messages',
                'title' => __('Top 10 Stikerwan-Stikerwati'),
                'description' => __('Saat kata-kata tidak cukup, sticker mengambil alih percakapan.'),
            ],
            [
                'metric' => 'link_messages',
                'title' => __('Top 10 Agen Link Nasional'),
                'description' => __('Mulai dari berita penting, undangan acara, sampai link yang entah valid entah hoax.'),
            ],
            [
                'metric' => 'emoji_messages',
                'title' => __('Top 10 Duta Emoji'),
                'description' => __('Karena ekspresi wajah tidak kelihatan, emoji pun bekerja lembur.'),
            ],
            [
                'metric' => 'deleted_messages',
                'title' => __('Top 10 Jejak Terhapus'),
                'description' => __('Pernah ada. Lalu hilang. Tapi statistik tetap mencatat.'),
            ],
            [
                'metric' => 'location_messages',
                'title' => __('Top 10 Shareloc Warrior'),
                'description' => __('Mereka tidak banyak menjelaskan. Cukup kirim lokasi, semua paham arah perjuangan.'),
            ],
            [
                'metric' => 'morning_messages',
                'title' => __('Top 10 Pasukan Subuh Produktif'),
                'description' => __('Aktif antara jam 04.00-08.00 WIB. Saat sebagian masih mimpi, mereka sudah mengetik.'),
            ],
            [
                'metric' => 'working_hour_messages',
                'title' => __('Top 10 Produktif Tapi Fleksibel'),
                'description' => __('Aktif di weekday jam 08.00-16.00 WIB. Antara kerja, break, atau multitasking tingkat alumni.'),
            ],
            [
                'metric' => 'after_work_messages',
                'title' => __('Top 10 After Office Club'),
                'description' => __('Aktif jam 16.00-23.00 WIB. Saat laptop mulai ditutup, grup mulai dibuka.'),
            ],
            [
                'metric' => 'midnight_messages',
                'title' => __('Top 10 Kalong Digital'),
                'description' => __('Aktif jam 23.00-04.00 WIB. Grup tidur? Mereka belum tentu.'),
            ],
            [
                'metric' => 'weekend_messages',
                'title' => __('Top 10 Weekend Warrior'),
                'description' => __('Sabtu-Minggu bukan libur dari grup. Justru kadang makin rame.'),
            ],
            [
                'metric' => 'total_words',
                'title' => __('Top 10 Kultum Terpanjang'),
                'description' => __('Mereka bukan cuma sering muncul, tapi juga meninggalkan jejak kata paling panjang.'),
            ],
            [
                'metric' => 'active_days',
                'title' => __('Top 10 Paling Konsisten'),
                'description' => __('Tidak selalu paling ramai, tapi paling sering hadir dari hari ke hari.'),
            ],
            [
                'metric' => 'average_words_per_message_low',
                'title' => __('Top 10 Mode Hemat Kata'),
                'description' => __('Singkat, padat, dan langsung selesai. Ranking ini memakai rata-rata kata per pesan paling rendah.'),
            ],
        ];
    }

    /**
     * @return array<int, array{metric: string, title: string, description: string}>
     */
    public function visibleTopMetricDefinitions(): array
    {
        return array_values(array_filter(
            $this->topMetricDefinitions(),
            fn (array $definition): bool => $this->hasTopMetricData($definition['metric']),
        ));
    }

    /**
     * @return array<int, string>
     */
    private function topMetricKeys(): array
    {
        return array_column($this->topMetricDefinitions(), 'metric');
    }

    private function hasTopMetricData(string $metric): bool
    {
        return $this->topMemberRankingRows($metric, 1) !== [];
    }

    /**
     * @return array<string, mixed>
     */
    public function topMemberChartOption(string $metric): array
    {
        $rows = $this->topMemberRankingRows($metric);
        $labels = array_column($rows, 'label');
        $values = array_column($rows, 'value');

        return [
            'color' => ['#173f25'],
            'topBarTooltip' => true,
            'tooltip' => ['trigger' => 'axis', 'axisPointer' => ['type' => 'shadow']],
            'grid' => ['left' => 8, 'right' => 24, 'top' => 12, 'bottom' => 24, 'containLabel' => true],
            'xAxis' => [
                'type' => 'value',
                'splitLine' => ['lineStyle' => ['color' => 'rgba(23, 63, 37, 0.12)']],
            ],
            'yAxis' => [
                'type' => 'category',
                'data' => $labels,
                'inverse' => true,
                'axisTick' => ['show' => false],
            ],
            'series' => [[
                'name' => __('Aktivitas'),
                'type' => 'bar',
                'barMaxWidth' => 22,
                'data' => $values,
                'itemStyle' => ['borderRadius' => [0, 6, 6, 0]],
            ]],
        ];
    }

    /**
     * @return array<int, array{label: string, value: int|float}>
     */
    private function topMemberRankingRows(string $metric, int $limit = 10): array
    {
        if ($this->latestImport === null || ! in_array($metric, $this->topMetricKeys(), true)) {
            return [];
        }

        if (array_key_exists($metric, $this->activityFlagMetricColumns())) {
            return $this->topActivityFlagRows($metric, $limit);
        }

        if ($metric === 'average_words_per_message_low') {
            return $this->topLowAverageWordRows($limit);
        }

        return $this->topMembers($metric, $limit)
            ->map(fn (WhatsappMemberStat $row): array => [
                'label' => $this->memberLabel($row),
                'value' => (int) $row->{$metric},
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function activityFlagMetricColumns(): array
    {
        return [
            'media_messages' => 'has_media',
            'sticker_messages' => 'has_sticker',
            'link_messages' => 'has_link',
            'emoji_messages' => 'has_emoji',
            'deleted_messages' => 'is_deleted_message',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function memberStatMetricKeys(): array
    {
        return [
            'total_messages',
            'location_messages',
            'morning_messages',
            'working_hour_messages',
            'after_work_messages',
            'midnight_messages',
            'weekend_messages',
            'total_words',
            'active_days',
        ];
    }

    /**
     * @return array<int, array{label: string, value: int}>
     */
    private function topActivityFlagRows(string $metric, int $limit): array
    {
        $column = $this->activityFlagMetricColumns()[$metric];

        return WhatsappActivity::query()
            ->with('whatsappMember:id,display_name')
            ->whereBelongsTo($this->latestImport)
            ->whereNotNull('whatsapp_member_id')
            ->where($column, true)
            ->select('whatsapp_member_id')
            ->selectRaw('COUNT(*) as metric_value')
            ->groupBy('whatsapp_member_id')
            ->orderByDesc('metric_value')
            ->limit($limit)
            ->get()
            ->map(fn (WhatsappActivity $row): array => [
                'label' => $row->whatsappMember?->display_name ?? '-',
                'value' => (int) $row->metric_value,
            ])
            ->all();
    }

    /**
     * @return array<int, array{label: string, value: float}>
     */
    private function topLowAverageWordRows(int $limit): array
    {
        return WhatsappMemberStat::query()
            ->with('whatsappMember:id,display_name')
            ->whereBelongsTo($this->latestImport)
            ->where('total_messages', '>=', 5)
            ->where('total_words', '>', 0)
            ->get()
            ->map(fn (WhatsappMemberStat $row): array => [
                'label' => $this->memberLabel($row),
                'value' => round($row->total_words / $row->total_messages, 1),
            ])
            ->sortBy('value')
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function dailyActivityChartOption(): array
    {
        if ($this->latestImport === null) {
            return ['series' => []];
        }

        $rows = $this->latestImport->dailyStats()
            ->orderBy('stat_date')
            ->get(['stat_date', 'total_activities']);

        $monthlyAverages = $this->monthlyAverages($rows);

        return [
            'color' => ['#1f5133', '#c57f17'],
            'tooltip' => ['trigger' => 'axis'],
            'dataZoom' => [
                ['type' => 'inside', 'xAxisIndex' => 0, 'filterMode' => 'none'],
                ['type' => 'slider', 'xAxisIndex' => 0, 'height' => 24, 'bottom' => 8, 'filterMode' => 'none'],
            ],
            'grid' => ['left' => 16, 'right' => 16, 'top' => 24, 'bottom' => 56, 'containLabel' => true],
            'xAxis' => ['type' => 'category', 'data' => $rows->map(fn ($row): string => $row->stat_date->format('Y-m-d'))->all()],
            'yAxis' => ['type' => 'value'],
            'series' => [
                ['name' => __('Aktivitas Harian'), 'type' => 'line', 'smooth' => true, 'data' => $rows->pluck('total_activities')->all()],
                ['name' => __('Rata-rata Bulanan'), 'type' => 'line', 'step' => 'middle', 'showSymbol' => false, 'lineStyle' => ['width' => 2], 'areaStyle' => ['opacity' => 0.12], 'data' => $monthlyAverages],
            ],
        ];
    }

    /**
     * @param  Collection<int, mixed>  $rows
     * @return array<int, float>
     */
    private function monthlyAverages(Collection $rows): array
    {
        return $rows
            ->groupBy(fn ($row): string => $row->stat_date->format('Y-m'))
            ->flatMap(function (Collection $monthRows): array {
                $average = round($monthRows->avg('total_activities'), 2);

                return $monthRows->map(fn (): float => $average)->all();
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function hourlyRadarOption(): array
    {
        $values = array_fill(0, 24, 0);

        if ($this->latestImport !== null) {
            $hourlyCounts = $this->activityCountsByExpression($this->hourExpression());

            foreach ($hourlyCounts as $hour => $total) {
                $values[(int) $hour] = (int) $total;
            }
        }

        $labels = collect(range(0, 23))
            ->map(fn (int $hour): string => $hour % 3 === 0 ? str_pad((string) $hour, 2, '0', STR_PAD_LEFT) : '')
            ->all();
        $tooltipLabels = collect(range(0, 23))
            ->map(fn (int $hour): string => str_pad((string) $hour, 2, '0', STR_PAD_LEFT).'.00')
            ->all();

        return $this->radarOption($labels, $values, $tooltipLabels);
    }

    /**
     * @return array<string, mixed>
     */
    public function dayRadarOption(): array
    {
        $values = array_fill(0, 7, 0);

        if ($this->latestImport !== null) {
            $dailyCounts = $this->activityCountsByExpression($this->dayOfWeekExpression());

            foreach ($dailyCounts as $day => $total) {
                $values[((int) $day) - 1] = (int) $total;
            }
        }

        return $this->radarOption([
            __('Senin'),
            __('Selasa'),
            __('Rabu'),
            __('Kamis'),
            __('Jumat'),
            __('Sabtu'),
            __('Minggu'),
        ], $values);
    }

    /**
     * @return array<string, mixed>
     */
    public function monthRadarOption(): array
    {
        $values = array_fill(0, 12, 0);

        if ($this->latestImport !== null) {
            $monthlyCounts = $this->activityCountsByExpression($this->monthExpression());

            foreach ($monthlyCounts as $month => $total) {
                $values[((int) $month) - 1] = (int) $total;
            }
        }

        return $this->radarOption([
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
        ], $values);
    }

    /**
     * @return array<string, mixed>
     */
    public function activityHeatmapOption(): array
    {
        $hours = collect(range(0, 23))
            ->map(fn (int $hour): string => str_pad((string) $hour, 2, '0', STR_PAD_LEFT).'.00')
            ->all();
        $days = [
            __('Senin'),
            __('Selasa'),
            __('Rabu'),
            __('Kamis'),
            __('Jumat'),
            __('Sabtu'),
            __('Minggu'),
        ];
        $values = [];

        foreach (range(0, 6) as $dayIndex) {
            foreach (range(0, 23) as $hourIndex) {
                $values["{$dayIndex}:{$hourIndex}"] = [$hourIndex, $dayIndex, 0];
            }
        }

        if ($this->latestImport !== null) {
            $hourExpression = $this->hourExpression();
            $dayExpression = $this->dayOfWeekExpression();
            $rows = WhatsappActivity::query()
                ->whereBelongsTo($this->latestImport)
                ->selectRaw("{$hourExpression} as activity_hour, {$dayExpression} as activity_day, COUNT(*) as total")
                ->groupBy(DB::raw($hourExpression), DB::raw($dayExpression))
                ->get();

            foreach ($rows as $row) {
                $hourIndex = (int) $row->activity_hour;
                $dayIndex = ((int) $row->activity_day) - 1;

                if ($dayIndex >= 0 && $dayIndex <= 6 && $hourIndex >= 0 && $hourIndex <= 23) {
                    $values["{$dayIndex}:{$hourIndex}"] = [$hourIndex, $dayIndex, (int) $row->total];
                }
            }
        }

        $data = array_values($values);
        $max = max(array_map(fn (array $point): int => (int) $point[2], $data)) ?: 1;

        return [
            'color' => ['#173f25'],
            'heatmapTooltip' => ['hours' => $hours, 'days' => $days],
            'tooltip' => ['position' => 'top'],
            'grid' => ['left' => 68, 'right' => 24, 'top' => 24, 'bottom' => 92, 'containLabel' => true],
            'xAxis' => [
                'type' => 'category',
                'data' => $hours,
                'splitArea' => ['show' => true],
                'axisLabel' => ['interval' => 2],
            ],
            'yAxis' => [
                'type' => 'category',
                'data' => $days,
                'splitArea' => ['show' => true],
            ],
            'visualMap' => [
                'min' => 0,
                'max' => $max,
                'calculable' => true,
                'orient' => 'horizontal',
                'left' => 'center',
                'bottom' => 12,
                'inRange' => ['color' => ['#eef7ef', '#8cb58f', '#173f25']],
            ],
            'series' => [[
                'name' => __('Aktivitas'),
                'type' => 'heatmap',
                'data' => $data,
                'emphasis' => [
                    'itemStyle' => [
                        'shadowBlur' => 10,
                        'shadowColor' => 'rgba(23, 63, 37, 0.35)',
                    ],
                ],
            ]],
        ];
    }

    /**
     * @param  array<int, string>  $labels
     * @param  array<int, int>  $values
     * @param  array<int, string>|null  $tooltipLabels
     * @return array<string, mixed>
     */
    private function radarOption(array $labels, array $values, ?array $tooltipLabels = null): array
    {
        $radarData = $this->clockwiseRadarData($labels, $values, $tooltipLabels ?? $labels);
        $max = max($values) ?: 1;

        return [
            'color' => ['#173f25'],
            'tooltip' => ['trigger' => 'item'],
            'radarTooltipLabels' => $radarData['tooltipLabels'],
            'radar' => [
                'shape' => 'circle',
                'startAngle' => 90,
                'splitNumber' => 4,
                'axisName' => ['color' => '#4b574d'],
                'axisLine' => ['lineStyle' => ['color' => 'rgba(23, 63, 37, 0.18)']],
                'splitLine' => ['lineStyle' => ['color' => 'rgba(23, 63, 37, 0.14)']],
                'splitArea' => ['areaStyle' => ['color' => ['rgba(23, 63, 37, 0.03)', 'rgba(23, 63, 37, 0.07)']]],
                'indicator' => collect($radarData['labels'])
                    ->map(fn (string $label): array => ['name' => $label, 'max' => $max])
                    ->all(),
            ],
            'series' => [[
                'name' => __('Aktivitas'),
                'type' => 'radar',
                'symbolSize' => 4,
                'lineStyle' => ['width' => 2.5],
                'areaStyle' => ['opacity' => 0.28],
                'emphasis' => ['lineStyle' => ['width' => 3]],
                'data' => [['name' => __('Aktivitas'), 'value' => $radarData['values']]],
            ]],
        ];
    }

    /**
     * @param  array<int, string>  $labels
     * @param  array<int, int>  $values
     * @param  array<int, string>  $tooltipLabels
     * @return array{labels: array<int, string>, values: array<int, int>, tooltipLabels: array<int, string>}
     */
    private function clockwiseRadarData(array $labels, array $values, array $tooltipLabels): array
    {
        return [
            'labels' => $this->clockwiseRadarOrder($labels),
            'values' => $this->clockwiseRadarOrder($values),
            'tooltipLabels' => $this->clockwiseRadarOrder($tooltipLabels),
        ];
    }

    /**
     * @template T
     *
     * @param  array<int, T>  $items
     * @return array<int, T>
     */
    private function clockwiseRadarOrder(array $items): array
    {
        if (count($items) <= 2) {
            return $items;
        }

        return array_merge([array_shift($items)], array_reverse($items));
    }

    /**
     * @return \Illuminate\Support\Collection<int|string, int>
     */
    private function activityCountsByExpression(string $expression): \Illuminate\Support\Collection
    {
        return WhatsappActivity::query()
            ->whereBelongsTo($this->latestImport)
            ->selectRaw("{$expression} as activity_bucket, COUNT(*) as total")
            ->groupBy(DB::raw($expression))
            ->pluck('total', 'activity_bucket');
    }

    private function hourExpression(): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "CAST(strftime('%H', occurred_at_display) AS INTEGER)",
            'pgsql' => 'EXTRACT(HOUR FROM occurred_at_display)',
            default => 'HOUR(occurred_at_display)',
        };
    }

    private function dayOfWeekExpression(): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "((CAST(strftime('%w', occurred_at_display) AS INTEGER) + 6) % 7) + 1",
            'pgsql' => 'EXTRACT(ISODOW FROM occurred_at_display)',
            default => 'WEEKDAY(occurred_at_display) + 1',
        };
    }

    private function monthExpression(): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "CAST(strftime('%m', occurred_at_display) AS INTEGER)",
            'pgsql' => 'EXTRACT(MONTH FROM occurred_at_display)',
            default => 'MONTH(occurred_at_display)',
        };
    }

    /**
     * @return array<int, array{title: string, value: string, unit: string|null}>
     */
    public function groupFactCards(): array
    {
        if ($this->latestImport === null) {
            return [];
        }

        $firstActivity = $this->latestImport->first_activity_at;
        $lastActivity = $this->latestImport->last_activity_at;
        $totalDays = $firstActivity && $lastActivity
            ? max(1, (int) CarbonImmutable::parse($firstActivity->format('Y-m-d'))->diffInDays(CarbonImmutable::parse($lastActivity->format('Y-m-d'))) + 1)
            : 0;
        $averageMessagesPerDay = $totalDays > 0
            ? round($this->latestImport->total_messages / $totalDays, 1)
            : 0;
        $eventCounts = $this->groupSystemEventCounts();

        $cards = [
            ['title' => __('Aktivitas Pertama'), 'value' => $this->dateValue($firstActivity), 'unit' => $this->timeUnit($firstActivity)],
            ['title' => __('Aktivitas Terakhir'), 'value' => $this->dateValue($lastActivity), 'unit' => $this->timeUnit($lastActivity)],
            ['title' => __('Total Hari'), 'value' => $this->formatNumber($totalDays), 'unit' => __('Hari')],
            ['title' => __('Total Aktivitas'), 'value' => $this->formatNumber($this->latestImport->total_activities), 'unit' => __('Aktivitas')],
            ['title' => __('Total Pesan'), 'value' => $this->formatNumber($this->latestImport->total_messages), 'unit' => __('Pesan')],
            ['title' => __('Aktivitas Non-Pesan'), 'value' => $this->formatNumber($this->latestImport->total_system_events), 'unit' => __('Aktivitas')],
            ['title' => __('Rata-rata Pesan'), 'value' => $this->formatNumber($averageMessagesPerDay), 'unit' => __('Pesan / Hari')],
            ['title' => __('Total Kata (> 1 huruf)'), 'value' => $this->formatNumber($this->latestImport->total_words), 'unit' => __('Kata')],
            ['title' => __('Jumlah Anggota'), 'value' => $this->formatNumber($this->latestImport->total_participants), 'unit' => __('Orang')],
            ['title' => __('Pesan dengan Emoji'), 'value' => $this->formatNumber($this->latestImport->total_emoji_messages), 'unit' => __('Pesan')],
            ['title' => __('Pesan dengan Media'), 'value' => $this->formatNumber($this->latestImport->total_media_messages), 'unit' => __('Pesan')],
            ['title' => __('Pesan dengan Sticker'), 'value' => $this->formatNumber($this->latestImport->total_sticker_messages), 'unit' => __('Pesan')],
            ['title' => __('Pesan dengan Link'), 'value' => $this->formatNumber($this->latestImport->total_link_messages), 'unit' => __('Pesan')],
            ['title' => __('Pesan Dihapus'), 'value' => $this->formatNumber($this->latestImport->total_deleted_messages), 'unit' => __('Pesan')],
            ['title' => __('Anggota Keluar'), 'value' => $this->formatNumber($eventCounts['member_left'] ?? 0), 'unit' => __('Kali')],
            ['title' => __('Anggota Ditambahkan'), 'value' => $this->formatNumber($eventCounts['member_added'] ?? 0), 'unit' => __('Kali')],
            ['title' => __('Anggota Dikeluarkan'), 'value' => $this->formatNumber($eventCounts['member_removed'] ?? 0), 'unit' => __('Kali')],
            ['title' => __('Ganti Nomor'), 'value' => $this->formatNumber($eventCounts['phone_number_changed'] ?? 0), 'unit' => __('Kali')],
            ['title' => __('Ganti Perangkat'), 'value' => $this->formatNumber($eventCounts['security_code_changed'] ?? 0), 'unit' => __('Kali')],
            ['title' => __('Ganti Nama Grup'), 'value' => $this->formatNumber($eventCounts['group_name_changed'] ?? 0), 'unit' => __('Kali')],
            ['title' => __('Ganti Deskripsi'), 'value' => $this->formatNumber($eventCounts['group_description_changed'] ?? 0), 'unit' => __('Kali')],
            ['title' => __('Ganti Icon Grup'), 'value' => $this->formatNumber($eventCounts['group_icon_changed'] ?? 0), 'unit' => __('Kali')],
        ];

        return array_values(array_filter(
            $cards,
            fn (array $card): bool => ! $this->isZeroCard($card),
        ));
    }

    /**
     * @return array<string, int>
     */
    private function groupSystemEventCounts(): array
    {
        if ($this->latestImport === null) {
            return [];
        }

        return WhatsappActivity::query()
            ->whereBelongsTo($this->latestImport)
            ->where('activity_type', 'system')
            ->whereNotNull('system_event_type')
            ->selectRaw('system_event_type, COUNT(*) as total')
            ->groupBy('system_event_type')
            ->pluck('total', 'system_event_type')
            ->map(fn (mixed $total): int => (int) $total)
            ->all();
    }

    private function dateValue(?CarbonInterface $date): string
    {
        return $date?->format('d/m/y') ?? '-';
    }

    private function timeUnit(?CarbonInterface $date): ?string
    {
        return $date ? $date->format('H:i').' WIB' : null;
    }

    private function formatNumber(int|float $value): string
    {
        return number_format($value, is_float($value) && floor($value) !== $value ? 1 : 0, ',', '.');
    }

    /**
     * @param  array{title: string, value: string, unit: string|null}  $card
     */
    private function isZeroCard(array $card): bool
    {
        return $card['value'] === '0';
    }

    /**
     * @return array<string, mixed>
     */
    public function personalMessageChartOption(): array
    {
        $rows = $this->topMembers('total_messages', 5);
        $members = $rows->map(fn (WhatsappMemberStat $row): string => $row->whatsappMember?->display_name ?? '-')->all();

        return [
            'tooltip' => ['trigger' => 'axis', 'axisPointer' => ['type' => 'shadow']],
            'legend' => [],
            'grid' => ['left' => 120, 'right' => 24, 'top' => 48, 'bottom' => 24],
            'xAxis' => ['type' => 'value'],
            'yAxis' => ['type' => 'category', 'data' => $members],
            'series' => [
                ['name' => __('Teks Murni'), 'type' => 'bar', 'stack' => 'total', 'data' => $rows->pluck('pure_text_messages')->all()],
                ['name' => __('Emoji'), 'type' => 'bar', 'stack' => 'total', 'data' => $rows->pluck('emoji_messages')->all()],
                ['name' => __('Media'), 'type' => 'bar', 'stack' => 'total', 'data' => $rows->pluck('media_messages')->all()],
                ['name' => __('Sticker'), 'type' => 'bar', 'stack' => 'total', 'data' => $rows->pluck('sticker_messages')->all()],
                ['name' => __('Link'), 'type' => 'bar', 'stack' => 'total', 'data' => $rows->pluck('link_messages')->all()],
                ['name' => __('Deleted'), 'type' => 'bar', 'stack' => 'total', 'data' => $rows->pluck('deleted_messages')->all()],
            ],
        ];
    }

    public function memberLabel(WhatsappMemberStat|WhatsappMemberEventStat $row): string
    {
        return $row->whatsappMember?->display_name ?? '-';
    }
}; ?>

<section class="w-full space-y-6 p-6 lg:p-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="space-y-2">
            <flux:heading size="xl">{{ __('WhatsApp Group Analyzer') }}</flux:heading>
            <flux:text class="max-w-3xl">
                {{ __('Statistik visual grup WhatsApp alumni dalam WIB. Sistem menyimpan aktivitas untuk audit ulang dan mapping personal.') }}
            </flux:text>
        </div>

        @can('import-whatsapp-analytics')
            <flux:button variant="ghost" :href="route('admin.whatsapp.index')" wire:navigate>
                {{ __('Kelola Import') }}
            </flux:button>
        @endcan
    </div>

    <div class="flex flex-wrap gap-2">
        <flux:button size="sm" variant="{{ $tab === 'group' ? 'primary' : 'ghost' }}" wire:click="selectTab('group')">{{ __('Statistik Grup') }}</flux:button>
        <flux:button size="sm" variant="{{ $tab === 'top10' ? 'primary' : 'ghost' }}" wire:click="selectTab('top10')">{{ __('Top 10') }}</flux:button>
        <flux:button size="sm" variant="{{ $tab === 'personal' ? 'primary' : 'ghost' }}" wire:click="selectTab('personal')">{{ __('Statistik Personal') }}</flux:button>
    </div>

    @if ($this->latestImport)
        @if ($tab === 'group')
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($this->groupFactCards() as $card)
                    <flux:card class="flex min-h-36 flex-col items-center justify-center gap-3 text-center" wire:key="group-fact-{{ $card['title'] }}">
                        <flux:text class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $card['title'] }}</flux:text>
                        <div class="font-display text-4xl font-extrabold leading-none text-ktn-forest tabular-nums dark:text-ktn-sage-light">
                            {{ $card['value'] }}
                        </div>
                        @if ($card['unit'])
                            <div class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $card['unit'] }}</div>
                        @endif
                    </flux:card>
                @endforeach
            </div>

            <div class="space-y-4">
                <flux:card class="space-y-4">
                    <div>
                        <flux:heading size="lg">{{ __('Denyut Nadi Grup') }}</flux:heading>
                        <flux:text>{{ __('Melihat kapan grup ramai, sunyi, dan tiba-tiba nostalgia berjamaah.') }}</flux:text>
                    </div>
                    <div class="h-80 w-full" data-echarts data-echarts-option='@json($this->dailyActivityChartOption())'></div>
                </flux:card>

                <flux:card class="space-y-4">
                    <div>
                        <flux:heading size="lg">{{ __('Peta Panas Aktivitas Grup') }}</flux:heading>
                        <flux:text>{{ __('Pola keramaian grup berdasarkan hari dan jam, lengkap dengan legenda intensitas di bawah chart.') }}</flux:text>
                    </div>
                    <div class="h-96 w-full" data-echarts data-echarts-option='@json($this->activityHeatmapOption())'></div>
                </flux:card>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <flux:card class="space-y-4">
                        <div>
                            <flux:heading size="lg">{{ __('Jam-Jam Rawan Nostalgia') }}</flux:heading>
                            <flux:text>{{ __('Jam favorit grup untuk ngobrol, bercanda, atau bahas masa lalu.') }}</flux:text>
                        </div>
                        <div class="h-80 w-full" data-echarts data-echarts-option='@json($this->hourlyRadarOption())'></div>
                    </flux:card>

                    <flux:card class="space-y-4">
                        <div>
                            <flux:heading size="lg">{{ __('Hari Favorit Buat Rame-Rame') }}</flux:heading>
                            <flux:text>{{ __('Apakah grup lebih aktif saat weekday serius, atau weekend mode santai?') }}</flux:text>
                        </div>
                        <div class="h-80 w-full" data-echarts data-echarts-option='@json($this->dayRadarOption())'></div>
                    </flux:card>

                    <flux:card class="space-y-4">
                        <div>
                            <flux:heading size="lg">{{ __('Kalender Keramaian Alumni') }}</flux:heading>
                            <flux:text>{{ __('Bulan-bulan langganan ramai untuk reuni, lebaran, lustrum, atau ulang tahun.') }}</flux:text>
                        </div>
                        <div class="h-80 w-full" data-echarts data-echarts-option='@json($this->monthRadarOption())'></div>
                    </flux:card>
                </div>
            </div>
        @elseif ($tab === 'top10')
            <div class="grid gap-4 lg:grid-cols-2 2xl:grid-cols-3">
                @forelse ($this->visibleTopMetricDefinitions() as $definition)
                    <flux:card class="space-y-4" wire:key="top-{{ $definition['metric'] }}">
                        <div>
                            <flux:heading size="lg">{{ $definition['title'] }}</flux:heading>
                            <flux:text>{{ $definition['description'] }}</flux:text>
                        </div>
                        <div class="h-80 w-full" data-echarts data-echarts-option='@json($this->topMemberChartOption($definition['metric']))'></div>
                    </flux:card>
                @empty
                    <flux:card class="space-y-3 lg:col-span-2 2xl:col-span-3">
                        <flux:heading size="lg">{{ __('Belum ada Top 10') }}</flux:heading>
                        <flux:text>{{ __('Ranking akan muncul setelah import memiliki statistik anggota yang bernilai lebih dari nol.') }}</flux:text>
                    </flux:card>
                @endforelse
            </div>
        @else
            <div class="grid gap-4 xl:grid-cols-2">
                <flux:card class="space-y-4">
                    <div>
                        <flux:heading size="lg">{{ __('Pola Chat Personal') }}</flux:heading>
                        <flux:text>{{ __('Default menampilkan top 5 paling aktif agar chart tetap terbaca.') }}</flux:text>
                    </div>
                    <div class="h-96 w-full" data-echarts data-echarts-option='@json($this->personalMessageChartOption())'></div>
                </flux:card>

                <flux:card class="space-y-4">
                    <flux:heading size="lg">{{ __('Jejak Sistem Personal') }}</flux:heading>
                    <div class="space-y-3">
                        @foreach ([
                            'member_added_as_actor' => __('Menambahkan Anggota'),
                            'member_added_as_target' => __('Ditambahkan'),
                            'member_removed_as_actor' => __('Mengeluarkan Anggota'),
                            'member_left' => __('Keluar Grup'),
                            'security_code_changed' => __('Ganti Perangkat'),
                        ] as $metric => $title)
                            <div wire:key="event-{{ $metric }}">
                                <div class="mb-2 flex items-center justify-between gap-3">
                                    <span class="font-medium">{{ $title }}</span>
                                </div>
                                <div class="space-y-2">
                                    @foreach ($this->topEventMembers($metric, 5) as $row)
                                        <div class="flex items-center justify-between gap-4 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700" wire:key="{{ $metric }}-{{ $row->id }}">
                                            <span class="truncate">{{ $this->memberLabel($row) }}</span>
                                            <span class="font-semibold tabular-nums">{{ (int) $row->{$metric} }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </flux:card>
            </div>
        @endif
    @else
        <flux:card class="space-y-3">
            <flux:heading size="lg">{{ __('Belum ada WhatsApp Analytics') }}</flux:heading>
            <flux:text>{{ __('Statistik akan tersedia setelah panitia mengunggah dan memproses file export WhatsApp.') }}</flux:text>
        </flux:card>
    @endif
</section>
