<?php

use App\Models\WhatsappActivity;
use App\Models\WhatsappImport;
use App\Models\WhatsappMemberEventStat;
use App\Models\WhatsappMemberStat;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

new #[Title('WhatsApp Analytics')] class extends Component {
    public string $tab = 'group';

    /**
     * @var array<int, int>
     */
    public array $selectedWhatsappMemberIds = [];

    public function mount(): void
    {
        $this->selectedWhatsappMemberIds = $this->defaultSelectedPersonalMemberIds();
    }

    public function selectTab(string $tab): void
    {
        if ($tab === 'mapping' && ! $this->canMapWhatsappAlumni()) {
            return;
        }

        if (in_array($tab, ['group', 'top10', 'personal', 'mapping'], true)) {
            $this->tab = $tab;
        }
    }

    public function downloadAnalysisSource(): BinaryFileResponse
    {
        abort_if($this->latestImport === null || ! $this->latestImport->file_path, 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($this->latestImport->file_path), 404);

        $sourcePath = $disk->path($this->latestImport->file_path);
        $baseName = $this->analysisSourceBaseName();

        if (str_ends_with(strtolower($this->latestImport->file_path), '.zip')) {
            return response()->download($sourcePath, "{$baseName}.zip");
        }

        $zipDirectory = storage_path('framework/cache/whatsapp-downloads');

        if (! is_dir($zipDirectory)) {
            mkdir($zipDirectory, 0755, true);
        }

        $zipPath = $zipDirectory.'/'.uniqid('whatsapp-analysis-', true).'.txt.zip';
        $zip = new \ZipArchive();

        abort_unless($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true, 500);

        $zip->addFile($sourcePath, "{$baseName}.txt");
        $zip->close();

        return response()
            ->download($zipPath, "{$baseName}.txt.zip")
            ->deleteFileAfterSend(true);
    }

    public function togglePersonalMember(int $memberId): void
    {
        if (in_array($memberId, $this->selectedWhatsappMemberIds, true)) {
            $this->selectedWhatsappMemberIds = array_values(array_filter(
                $this->selectedWhatsappMemberIds,
                fn (int $selectedMemberId): bool => $selectedMemberId !== $memberId,
            ));

            return;
        }

        $this->selectedWhatsappMemberIds[] = $memberId;
        $this->selectedWhatsappMemberIds = array_slice(array_values(array_unique($this->selectedWhatsappMemberIds)), -10);
    }

    public function canMapWhatsappAlumni(): bool
    {
        return auth()->user()?->canManageAlumni() ?? false;
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
        if ($this->latestImport === null || ! in_array($metric, $this->eventMetricKeys(), true)) {
            return new Collection();
        }

        return WhatsappMemberEventStat::query()
            ->with('whatsappMember:id,display_name')
            ->whereBelongsTo($this->latestImport)
            ->where($metric, '>', 0)
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
                'description' => __('Aktif di weekday jam 08.00-16.00 WIB. Antara kerja, break, multitasking atau makan gaji buta?'),
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
            [
                'metric' => 'member_added_as_actor',
                'title' => __('Top 10 Menambahkan Anggota'),
                'description' => __('Mereka yang paling sering membuka pintu grup untuk anggota lain.'),
            ],
            [
                'metric' => 'member_left',
                'title' => __('Top 10 Titik Hilang dari Jaringan'),
                'description' => __('Pernah menjadi bagian dari jaringan pengamatan, lalu menghilang dari peta grup. Namun jejaknya tetap tercatat dalam sejarah.'),
            ],
            [
                'metric' => 'security_code_changed',
                'title' => __('Top 10 Reobservasi Perangkat'),
                'description' => __('Instrumen boleh berganti, tetapi pengamatnya tetap sama. Statistik ini mencatat mereka yang paling sering melakukan kalibrasi digital.'),
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

        if (in_array($metric, $this->eventMetricKeys(), true)) {
            return $this->topEventMembers($metric, $limit)
                ->map(fn (WhatsappMemberEventStat $row): array => [
                    'label' => $this->memberLabel($row),
                    'value' => (int) $row->{$metric},
                ])
                ->all();
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
     * @return array<int, string>
     */
    private function eventMetricKeys(): array
    {
        return [
            'member_added_as_actor',
            'member_added_as_target',
            'member_removed_as_actor',
            'member_left',
            'security_code_changed',
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
     * @return array<int, array{name: string, value: int, textStyle: array{color: string}}>
     */
    public function groupWordCloud(): array
    {
        if ($this->latestImport === null) {
            return [];
        }

        $stopWords = $this->wordCloudStopWords();
        $counts = [];

        WhatsappActivity::query()
            ->whereBelongsTo($this->latestImport)
            ->where('activity_type', 'message')
            ->whereNotNull('message_text')
            ->select('message_text')
            ->cursor()
            ->each(function (WhatsappActivity $activity) use (&$counts, $stopWords): void {
                preg_match_all('/[\pL\pN]{3,}/u', mb_strtolower($activity->message_text ?? ''), $matches);

                foreach ($matches[0] ?? [] as $word) {
                    if (isset($stopWords[$word]) || is_numeric($word)) {
                        continue;
                    }

                    $counts[$word] = ($counts[$word] ?? 0) + 1;
                }
            });

        arsort($counts);
        $topCounts = array_slice($counts, 0, 120, true);
        $palette = ['#173f25', '#1f5133', '#5f7f63', '#c5a059', '#96783d', '#7c2d12', '#4b574d', '#0e2d1a', '#6f4e37', '#2f6840'];
        $words = [];
        $index = 0;

        foreach ($topCounts as $word => $count) {
            $words[] = [
                'name' => $word,
                'value' => $count,
                'textStyle' => [
                    'color' => $palette[$index % count($palette)],
                ],
            ];

            $index++;
        }

        return $words;
    }

    /**
     * @return array<string, mixed>
     */
    public function groupWordCloudOption(): array
    {
        $words = $this->groupWordCloud();

        if ($words === []) {
            return ['series' => []];
        }

        return [
            'backgroundColor' => 'transparent',
            'tooltip' => [
                'show' => true,
                'formatter' => '{b}<br><strong>{c}</strong> kali',
            ],
            'series' => [[
                'type' => 'wordCloud',
                'shape' => 'circle',
                'keepAspect' => false,
                'left' => 'center',
                'top' => 'center',
                'width' => '96%',
                'height' => '94%',
                'right' => null,
                'bottom' => null,
                'sizeRange' => [13, 86],
                'rotationRange' => [-90, 90],
                'rotationStep' => 90,
                'gridSize' => 6,
                'drawOutOfBound' => false,
                'layoutAnimation' => true,
                'textStyle' => [
                    'fontFamily' => 'ui-sans-serif, system-ui, sans-serif',
                    'fontWeight' => 700,
                ],
                'emphasis' => [
                    'focus' => 'self',
                    'textStyle' => [
                        'shadowBlur' => 8,
                        'shadowColor' => 'rgba(23, 63, 37, 0.25)',
                    ],
                ],
                'data' => $words,
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
                'center' => ['50%', '43%'],
                'radius' => '70%',
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
     * @param  array<string, array<int, int>>  $memberValues
     * @param  array<int, string>|null  $tooltipLabels
     * @return array<string, mixed>
     */
    private function multiRadarOption(array $labels, array $memberValues, ?array $tooltipLabels = null): array
    {
        $orderedLabels = $this->clockwiseRadarOrder($labels);
        $orderedTooltipLabels = $this->clockwiseRadarOrder($tooltipLabels ?? $labels);
        $allValues = collect($memberValues)
            ->flatMap(fn (array $values): array => $values)
            ->all();
        $max = $allValues === [] ? 1 : (max($allValues) ?: 1);

        return [
            'color' => $this->chartPalette(),
            'tooltip' => ['trigger' => 'item'],
            'radarTooltipLabels' => $orderedTooltipLabels,
            'legend' => ['type' => 'scroll', 'bottom' => 0],
            'radar' => [
                'shape' => 'circle',
                'center' => ['50%', '42%'],
                'radius' => '75%',
                'startAngle' => 90,
                'splitNumber' => 4,
                'axisName' => ['color' => '#4b574d'],
                'axisLine' => ['lineStyle' => ['color' => 'rgba(23, 63, 37, 0.18)']],
                'splitLine' => ['lineStyle' => ['color' => 'rgba(23, 63, 37, 0.14)']],
                'splitArea' => ['areaStyle' => ['color' => ['rgba(23, 63, 37, 0.03)', 'rgba(23, 63, 37, 0.07)']]],
                'indicator' => collect($orderedLabels)
                    ->map(fn (string $label): array => ['name' => $label, 'max' => $max])
                    ->all(),
            ],
            'series' => collect($memberValues)
                ->map(fn (array $values, string $member): array => [
                    'name' => $member,
                    'type' => 'radar',
                    'symbolSize' => 4,
                    'lineStyle' => ['width' => 2.5],
                    'areaStyle' => ['opacity' => 0.12],
                    'emphasis' => ['lineStyle' => ['width' => 3]],
                    'data' => [['name' => $member, 'value' => $this->clockwiseRadarOrder($values)]],
                ])
                ->values()
                ->all(),
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

    /**
     * @return array<string, true>
     */
    private function wordCloudStopWords(): array
    {
        return array_fill_keys([
            'yang', 'dan', 'dari', 'untuk', 'dengan', 'atau', 'ini', 'itu', 'ada', 'jadi', 'sudah', 'belum', 'bisa', 'tidak', 'akan', 'kalau', 'karena', 'saya', 'kita', 'kami', 'anda', 'aku', 'nya', 'the', 'and', 'for', 'you', 'are', 'this', 'that', 'with', 'not', 'was', 'have', 'has', 'but', 'from', 'media', 'omitted', 'message', 'deleted',
        ], true);
    }

    private function dateValue(?CarbonInterface $date): string
    {
        return $date?->format('d/m/y') ?? '-';
    }

    private function timeUnit(?CarbonInterface $date): ?string
    {
        return $date ? $date->format('H:i').' WIB' : null;
    }

    private function analysisSourceBaseName(): string
    {
        $name = pathinfo($this->latestImport?->file_name ?: 'whatsapp-analysis', PATHINFO_FILENAME);
        $name = preg_replace('/[^A-Za-z0-9._-]+/', '-', $name) ?: 'whatsapp-analysis';

        return trim($name, '.-') ?: 'whatsapp-analysis';
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
        $rows = $this->selectedPersonalMemberStats();
        $members = $rows->map(fn (WhatsappMemberStat $row): string => $this->memberLabel($row))->all();

        return [
            'color' => $this->chartPalette(),
            'personalStackedBarTooltip' => true,
            'tooltip' => ['trigger' => 'item'],
            'legend' => ['type' => 'scroll', 'bottom' => 0],
            'grid' => ['left' => 8, 'right' => 24, 'top' => 16, 'bottom' => 56, 'containLabel' => true],
            'xAxis' => [
                'type' => 'value',
                'name' => __('Jumlah Aktivitas'),
                'nameLocation' => 'end',
                'splitLine' => ['lineStyle' => ['color' => 'rgba(23, 63, 37, 0.1)']],
            ],
            'yAxis' => [
                'type' => 'category',
                'data' => $members,
                'inverse' => true,
                'axisTick' => ['show' => false],
            ],
            'series' => [
                ['name' => __('Teks Murni'), 'type' => 'bar', 'stack' => 'total', 'data' => $rows->pluck('pure_text_messages')->all()],
                ['name' => __('Berbagi Media'), 'type' => 'bar', 'stack' => 'total', 'data' => $rows->pluck('media_messages')->all()],
                ['name' => __('Teks dengan Emoji'), 'type' => 'bar', 'stack' => 'total', 'data' => $rows->pluck('emoji_messages')->all()],
                ['name' => __('Teks dengan Link'), 'type' => 'bar', 'stack' => 'total', 'data' => $rows->pluck('link_messages')->all()],
                ['name' => __('Berbagi Lokasi'), 'type' => 'bar', 'stack' => 'total', 'data' => $rows->pluck('location_messages')->all()],
                ['name' => __('Menghapus Pesan'), 'type' => 'bar', 'stack' => 'total', 'data' => $rows->pluck('deleted_messages')->all()],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function personalSystemActivityChartOption(): array
    {
        $memberRows = $this->selectedPersonalMemberStats();
        $eventRows = $this->selectedPersonalMemberEventStats()->keyBy('whatsapp_member_id');

        return [
            'color' => $this->chartPalette(),
            'personalStackedBarTooltip' => true,
            'tooltip' => ['trigger' => 'item'],
            'legend' => ['type' => 'scroll', 'bottom' => 0],
            'grid' => ['left' => 8, 'right' => 24, 'top' => 16, 'bottom' => 56, 'containLabel' => true],
            'xAxis' => [
                'type' => 'value',
                'name' => __('Jumlah Aktivitas'),
                'nameLocation' => 'end',
                'splitLine' => ['lineStyle' => ['color' => 'rgba(23, 63, 37, 0.1)']],
            ],
            'yAxis' => [
                'type' => 'category',
                'data' => $memberRows->map(fn (WhatsappMemberStat $row): string => $this->memberLabel($row))->all(),
                'inverse' => true,
                'axisTick' => ['show' => false],
            ],
            'series' => [
                ['name' => __('Mengganti Deskripsi Grup'), 'type' => 'bar', 'stack' => 'total', 'data' => $this->personalEventMetricValues($memberRows, $eventRows, 'group_description_changed')],
                ['name' => __('Menambahkan Anggota'), 'type' => 'bar', 'stack' => 'total', 'data' => $this->personalEventMetricValues($memberRows, $eventRows, 'member_added_as_actor')],
                ['name' => __('Mengeluarkan Anggota'), 'type' => 'bar', 'stack' => 'total', 'data' => $this->personalEventMetricValues($memberRows, $eventRows, 'member_removed_as_actor')],
                ['name' => __('Keluar Grup'), 'type' => 'bar', 'stack' => 'total', 'data' => $this->personalEventMetricValues($memberRows, $eventRows, 'member_left')],
                ['name' => __('Mengganti Perangkat'), 'type' => 'bar', 'stack' => 'total', 'data' => $this->personalEventMetricValues($memberRows, $eventRows, 'security_code_changed')],
                ['name' => __('Lainnya'), 'type' => 'bar', 'stack' => 'total', 'data' => $this->personalOtherEventValues($memberRows, $eventRows)],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function personalMonthlyActivityChartOption(): array
    {
        $memberRows = $this->selectedPersonalMemberStats();

        if ($this->latestImport === null || $memberRows->isEmpty()) {
            return ['series' => []];
        }

        $activities = WhatsappActivity::query()
            ->whereBelongsTo($this->latestImport)
            ->whereIn('whatsapp_member_id', $memberRows->pluck('whatsapp_member_id'))
            ->orderBy('occurred_at_display')
            ->get(['whatsapp_member_id', 'occurred_at_display']);

        $monthKeys = $this->personalMonthlyActivityKeys($activities);
        $activityCounts = $activities
            ->groupBy(fn (WhatsappActivity $activity): string => $activity->whatsapp_member_id.'|'.$activity->occurred_at_display->format('Y-m'))
            ->map(fn (Collection $rows): int => $rows->count());

        return [
            'color' => $this->chartPalette(),
            'tooltip' => ['trigger' => 'axis', 'axisPointer' => ['type' => 'cross']],
            'legend' => ['type' => 'scroll', 'right' => 8, 'top' => 32, 'orient' => 'vertical'],
            'dataZoom' => [
                ['type' => 'inside', 'xAxisIndex' => 0, 'filterMode' => 'none'],
                ['type' => 'slider', 'xAxisIndex' => 0, 'height' => 24, 'bottom' => 8, 'filterMode' => 'none'],
            ],
            'grid' => ['left' => 8, 'right' => 220, 'top' => 32, 'bottom' => 64, 'containLabel' => true],
            'xAxis' => [
                'type' => 'category',
                'name' => __('Bulan/Tahun'),
                'nameLocation' => 'end',
                'boundaryGap' => false,
                'data' => array_map(fn (string $month): string => CarbonImmutable::createFromFormat('Y-m-d', $month.'-01')->format('m/Y'), $monthKeys),
            ],
            'yAxis' => [
                'type' => 'value',
                'name' => __('Jumlah'),
                'splitLine' => ['lineStyle' => ['color' => 'rgba(23, 63, 37, 0.1)']],
            ],
            'series' => $memberRows
                ->map(fn (WhatsappMemberStat $row): array => [
                    'name' => $this->memberLabel($row),
                    'type' => 'line',
                    'smooth' => true,
                    'showSymbol' => false,
                    'emphasis' => ['focus' => 'series'],
                    'lineStyle' => ['width' => 2.5],
                    'areaStyle' => ['opacity' => 0.1],
                    'data' => array_map(
                        fn (string $month): int => (int) ($activityCounts->get($row->whatsapp_member_id.'|'.$month) ?? 0),
                        $monthKeys,
                    ),
                ])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function personalHourlyRadarOption(): array
    {
        $memberRows = $this->selectedPersonalMemberStats();
        $memberValues = $this->personalRadarValuesByExpression($memberRows, $this->hourExpression(), 24);

        $labels = collect(range(0, 23))
            ->map(fn (int $hour): string => $hour % 3 === 0 ? str_pad((string) $hour, 2, '0', STR_PAD_LEFT) : '')
            ->all();
        $tooltipLabels = collect(range(0, 23))
            ->map(fn (int $hour): string => str_pad((string) $hour, 2, '0', STR_PAD_LEFT).'.00')
            ->all();

        return $this->multiRadarOption($labels, $memberValues, $tooltipLabels);
    }

    /**
     * @return array<string, mixed>
     */
    public function personalDayRadarOption(): array
    {
        $memberRows = $this->selectedPersonalMemberStats();
        $memberValues = $this->personalRadarValuesByExpression($memberRows, $this->dayOfWeekExpression(), 7, -1);

        return $this->multiRadarOption([
            __('Senin'),
            __('Selasa'),
            __('Rabu'),
            __('Kamis'),
            __('Jumat'),
            __('Sabtu'),
            __('Minggu'),
        ], $memberValues);
    }

    /**
     * @return array<string, mixed>
     */
    public function personalMonthRadarOption(): array
    {
        $memberRows = $this->selectedPersonalMemberStats();
        $memberValues = $this->personalRadarValuesByExpression($memberRows, $this->monthExpression(), 12, -1);

        return $this->multiRadarOption([
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
        ], $memberValues);
    }

    /**
     * @return Collection<int, WhatsappMemberStat>
     */
    public function personalMemberButtons(): Collection
    {
        if ($this->latestImport === null) {
            return new Collection();
        }

        return WhatsappMemberStat::query()
            ->with('whatsappMember:id,display_name')
            ->whereBelongsTo($this->latestImport)
            ->where('total_messages', '>', 0)
            ->get()
            ->sortBy(fn (WhatsappMemberStat $row): string => mb_strtolower($this->memberLabel($row)))
            ->values();
    }

    /**
     * @return array<int, int>
     */
    private function defaultSelectedPersonalMemberIds(): array
    {
        if ($this->latestImport === null) {
            return [];
        }

        return WhatsappMemberStat::query()
            ->whereBelongsTo($this->latestImport)
            ->where('total_messages', '>', 0)
            ->orderByDesc('total_messages')
            ->limit(5)
            ->pluck('whatsapp_member_id')
            ->map(fn (mixed $id): int => (int) $id)
            ->all();
    }

    /**
     * @return Collection<int, WhatsappMemberStat>
     */
    private function selectedPersonalMemberStats(): Collection
    {
        if ($this->latestImport === null || $this->selectedWhatsappMemberIds === []) {
            return new Collection();
        }

        return WhatsappMemberStat::query()
            ->with('whatsappMember:id,display_name')
            ->whereBelongsTo($this->latestImport)
            ->whereIn('whatsapp_member_id', $this->selectedWhatsappMemberIds)
            ->get()
            ->sortBy(fn (WhatsappMemberStat $row): string => mb_strtolower($this->memberLabel($row)))
            ->values();
    }

    /**
     * @return Collection<int, WhatsappMemberEventStat>
     */
    private function selectedPersonalMemberEventStats(): Collection
    {
        if ($this->latestImport === null || $this->selectedWhatsappMemberIds === []) {
            return new Collection();
        }

        return WhatsappMemberEventStat::query()
            ->whereBelongsTo($this->latestImport)
            ->whereIn('whatsapp_member_id', $this->selectedWhatsappMemberIds)
            ->get();
    }

    /**
     * @return \Illuminate\Support\Collection<int|string, int>
     */
    /**
     * @param  Collection<int, WhatsappMemberStat>  $memberRows
     * @return array<string, array<int, int>>
     */
    private function personalRadarValuesByExpression(Collection $memberRows, string $expression, int $bucketCount, int $bucketOffset = 0): array
    {
        if ($this->latestImport === null || $memberRows->isEmpty()) {
            return [];
        }

        $counts = WhatsappActivity::query()
            ->whereBelongsTo($this->latestImport)
            ->whereIn('whatsapp_member_id', $memberRows->pluck('whatsapp_member_id'))
            ->selectRaw("whatsapp_member_id, {$expression} as activity_bucket, COUNT(*) as total")
            ->groupBy('whatsapp_member_id', DB::raw($expression))
            ->get()
            ->groupBy('whatsapp_member_id');

        return $memberRows
            ->mapWithKeys(function (WhatsappMemberStat $row) use ($bucketCount, $bucketOffset, $counts): array {
                $values = array_fill(0, $bucketCount, 0);

                foreach ($counts->get($row->whatsapp_member_id, collect()) as $countRow) {
                    $bucketIndex = ((int) $countRow->activity_bucket) + $bucketOffset;

                    if ($bucketIndex >= 0 && $bucketIndex < $bucketCount) {
                        $values[$bucketIndex] = (int) $countRow->total;
                    }
                }

                return [$this->memberLabel($row) => $values];
            })
            ->all();
    }

    /**
     * @param  Collection<int, WhatsappMemberStat>  $memberRows
     * @param  Collection<int, WhatsappMemberEventStat>  $eventRows
     * @return array<int, int>
     */
    private function personalEventMetricValues(Collection $memberRows, Collection $eventRows, string $metric): array
    {
        return $memberRows
            ->map(fn (WhatsappMemberStat $row): int => (int) ($eventRows->get($row->whatsapp_member_id)?->{$metric} ?? 0))
            ->all();
    }

    /**
     * @param  Collection<int, WhatsappMemberStat>  $memberRows
     * @param  Collection<int, WhatsappMemberEventStat>  $eventRows
     * @return array<int, int>
     */
    private function personalOtherEventValues(Collection $memberRows, Collection $eventRows): array
    {
        return $memberRows
            ->map(function (WhatsappMemberStat $row) use ($eventRows): int {
                $eventRow = $eventRows->get($row->whatsapp_member_id);

                if ($eventRow === null) {
                    return 0;
                }

                return $eventRow->member_added_as_target
                    + $eventRow->member_removed_as_target
                    + $eventRow->phone_number_changed
                    + $eventRow->group_name_changed
                    + $eventRow->group_icon_changed
                    + $eventRow->disappearing_message_changed;
            })
            ->all();
    }

    /**
     * @param  Collection<int, WhatsappActivity>  $activities
     * @return array<int, string>
     */
    private function personalMonthlyActivityKeys(Collection $activities): array
    {
        $dates = $activities->pluck('occurred_at_display')->filter();

        $start = $dates->isNotEmpty()
            ? CarbonImmutable::parse($dates->min())->startOfMonth()
            : CarbonImmutable::parse($this->latestImport?->first_activity_at ?? now())->startOfMonth();
        $end = $dates->isNotEmpty()
            ? CarbonImmutable::parse($dates->max())->startOfMonth()
            : CarbonImmutable::parse($this->latestImport?->last_activity_at ?? now())->startOfMonth();
        $months = [];

        while ($start->lessThanOrEqualTo($end)) {
            $months[] = $start->format('Y-m');
            $start = $start->addMonth();
        }

        return $months;
    }

    /**
     * @return array<int, string>
     */
    private function chartPalette(): array
    {
        return [
            '#173f25',
            '#c5a059',
            '#5f7f63',
            '#0e2d1a',
            '#dfc27a',
            '#4b574d',
            '#8cb58f',
            '#96783d',
            '#2f6840',
            '#d7ddd5',
        ];
    }

    public function personalMemberButtonStyle(WhatsappMemberStat $row, int $maxMessages): string
    {
        $intensity = max(0.12, min(1, $row->total_messages / $maxMessages));
        $startOpacity = round(0.08 + ($intensity * 0.22), 3);
        $endOpacity = round(0.16 + ($intensity * 0.48), 3);

        return "background: linear-gradient(135deg, rgba(23, 63, 37, {$startOpacity}), rgba(31, 81, 51, {$endOpacity}));";
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
        <flux:button class="cursor-pointer" size="sm" variant="{{ $tab === 'group' ? 'primary' : 'ghost' }}" wire:click="selectTab('group')">{{ __('Statistik Grup') }}</flux:button>
        <flux:button class="cursor-pointer" size="sm" variant="{{ $tab === 'top10' ? 'primary' : 'ghost' }}" wire:click="selectTab('top10')">{{ __('Top 10 Alumni') }}</flux:button>
        <flux:button class="cursor-pointer" size="sm" variant="{{ $tab === 'personal' ? 'primary' : 'ghost' }}" wire:click="selectTab('personal')">{{ __('Statistik Personal') }}</flux:button>
        @if ($this->canMapWhatsappAlumni())
            <flux:button class="cursor-pointer" size="sm" variant="{{ $tab === 'mapping' ? 'primary' : 'ghost' }}" wire:click="selectTab('mapping')">{{ __('Mapping Alumni') }}</flux:button>
        @endif
        <flux:button class="cursor-pointer" size="sm" variant="ghost" wire:click="downloadAnalysisSource">{{ __('Bahan Analisis') }}</flux:button>
    </div>

    @if ($tab === 'mapping' && $this->canMapWhatsappAlumni())
        <flux:card class="space-y-3">
            <flux:heading size="lg">{{ __('Mapping Alumni') }}</flux:heading>
            <flux:text>{{ __('Pemetaan anggota WhatsApp ke data alumni akan disiapkan pada tahap berikutnya.') }}</flux:text>
        </flux:card>
    @elseif ($this->latestImport)
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
                        <flux:heading size="lg">{{ __('Kontur Keramaian Grup') }}</flux:heading>
                        <flux:text>{{ __('Seperti peta kontur, warna dan pola aktivitas ini menunjukkan puncak, lereng, dan lembah percakapan sepanjang sejarah grup.') }}</flux:text>
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

                <flux:card class="space-y-4">
                    <div>
                        <flux:heading size="lg">{{ __('Jejak Kata di Lapangan') }}</flux:heading>
                        <flux:text>{{ __('Kata-kata yang paling banyak tertinggal setelah gangguan, noise, dan kata-kata umum dieliminasi.') }}</flux:text>
                    </div>
                    @if ($this->groupWordCloud())
                        <div class="h-[34rem] w-full overflow-hidden rounded-lg bg-white" data-echarts data-echarts-option='@json($this->groupWordCloudOption())'></div>
                    @else
                        <flux:text>{{ __('Belum cukup kata untuk membentuk word cloud.') }}</flux:text>
                    @endif
                </flux:card>

                <livewire:whatsapp.digital-calendar :import-id="$this->latestImport->id" wire:key="digital-calendar-{{ $this->latestImport->id }}" />
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
            <div class="space-y-4">
                @php
                    $personalMembers = $this->personalMemberButtons();
                    $maxPersonalMessages = max(1, (int) $personalMembers->max('total_messages'));
                    $selectedPersonalMemberKey = implode('-', $selectedWhatsappMemberIds);
                @endphp

                <flux:card class="overflow-visible">
                    <div class="grid grid-cols-2 gap-1 overflow-visible md:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-6">
                        @foreach ($personalMembers as $memberRow)
                            @php
                                $isSelected = in_array($memberRow->whatsapp_member_id, $selectedWhatsappMemberIds, true);
                            @endphp
                            <flux:tooltip
                                position="top"
                                content="{{ number_format($memberRow->total_messages, 0, ',', '.') }}"
                                wire:key="personal-member-tooltip-{{ $memberRow->whatsapp_member_id }}"
                            >
                                <button
                                    type="button"
                                    wire:click="togglePersonalMember({{ $memberRow->whatsapp_member_id }})"
                                    class="flex h-12 w-full items-center justify-center rounded-lg border p-2 text-center text-sm font-semibold leading-none shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-ktn-forest/40 sm:text-base {{ $isSelected ? 'border-orange-500 text-orange-700 ring-2 ring-orange-500 dark:text-orange-300' : 'border-zinc-200 text-zinc-900 dark:border-zinc-700 dark:text-zinc-100' }}"
                                    style="{{ $this->personalMemberButtonStyle($memberRow, $maxPersonalMessages) }}"
                                >
                                    <span class="block w-full truncate whitespace-nowrap text-center">{{ $this->memberLabel($memberRow) }}</span>
                                </button>
                            </flux:tooltip>
                        @endforeach
                    </div>
                </flux:card>

                <flux:card class="space-y-4">
                    <div>
                        <flux:heading size="lg">{{ __('Produktivitas Titik Kontrol') }}</flux:heading>
                        <flux:text>{{ __('Mengukur seberapa aktif setiap titik kontrol berkontribusi dalam membangun jaringan percakapan grup.') }}</flux:text>
                    </div>
                    <div
                        class="w-full"
                        style="height: 32rem;"
                        wire:key="personal-message-chart-{{ $selectedPersonalMemberKey }}"
                        data-echarts
                        data-echarts-option='@json($this->personalMessageChartOption())'
                    ></div>
                </flux:card>

                <flux:card class="space-y-4">
                    <div>
                        <flux:heading size="lg">{{ __('Observasi Non-Verbal') }}</flux:heading>
                        <flux:text>{{ __('Tidak semua pengamatan dilakukan dengan kata-kata. Sticker, emoji, media, dan reaksi juga meninggalkan jejak.') }}</flux:text>
                    </div>
                    <div
                        class="w-full"
                        style="height: 32rem;"
                        wire:key="personal-system-chart-{{ $selectedPersonalMemberKey }}"
                        data-echarts
                        data-echarts-option='@json($this->personalSystemActivityChartOption())'
                    ></div>
                </flux:card>

                <flux:card class="space-y-4">
                    <div>
                        <flux:heading size="lg">{{ __('Ephemeris Kehadiran') }}</flux:heading>
                        <flux:text>{{ __('Pola kemunculan anggota sepanjang siklus tahunan, memperlihatkan kapan sebuah titik paling sering teramati.') }}</flux:text>
                    </div>
                    <div
                        class="w-full"
                        style="height: 32rem;"
                        wire:key="personal-monthly-chart-{{ $selectedPersonalMemberKey }}"
                        data-echarts
                        data-echarts-option='@json($this->personalMonthlyActivityChartOption())'
                    ></div>
                </flux:card>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <flux:card class="space-y-4">
                        <div>
                            <flux:heading size="lg">{{ __('Azimuth Aktivitas Harian') }}</flux:heading>
                            <flux:text>{{ __('Menunjukkan arah waktu favorit seseorang untuk muncul, merespons, atau memulai percakapan.') }}</flux:text>
                        </div>
                        <div
                            class="h-96 w-full"
                            wire:key="personal-hourly-radar-{{ $selectedPersonalMemberKey }}"
                            data-echarts
                            data-echarts-option='@json($this->personalHourlyRadarOption())'
                        ></div>
                    </flux:card>

                    <flux:card class="space-y-4">
                        <div>
                            <flux:heading size="lg">{{ __('Jadwal Survei Sosial') }}</flux:heading>
                            <flux:text>{{ __('Memetakan hari-hari ketika seorang anggota paling sering turun ke lapangan percakapan.') }}</flux:text>
                        </div>
                        <div
                            class="h-96 w-full"
                            wire:key="personal-day-radar-{{ $selectedPersonalMemberKey }}"
                            data-echarts
                            data-echarts-option='@json($this->personalDayRadarOption())'
                        ></div>
                    </flux:card>

                    <flux:card class="space-y-4">
                        <div>
                            <flux:heading size="lg">{{ __('Musim Pengamatan Personal') }}</flux:heading>
                            <flux:text>{{ __('Ada yang aktif sepanjang tahun, ada yang hanya muncul ketika musim nostalgia kembali datang.') }}</flux:text>
                        </div>
                        <div
                            class="h-96 w-full"
                            wire:key="personal-month-radar-{{ $selectedPersonalMemberKey }}"
                            data-echarts
                            data-echarts-option='@json($this->personalMonthRadarOption())'
                        ></div>
                    </flux:card>
                </div>
            </div>
        @endif
    @else
        <flux:card class="space-y-3">
            <flux:heading size="lg">{{ __('Belum ada WhatsApp Analytics') }}</flux:heading>
            <flux:text>{{ __('Statistik akan tersedia setelah panitia mengunggah dan memproses file export WhatsApp.') }}</flux:text>
        </flux:card>
    @endif
</section>
