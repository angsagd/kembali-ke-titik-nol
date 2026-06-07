<?php

namespace App\Services;

use App\Models\Alumni;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

class WhatsappChatAnalyzer
{
    /**
     * Analyze exported WhatsApp chat text into aggregate statistics only.
     *
     * @return array{
     *     import_start_date: string|null,
     *     import_end_date: string|null,
     *     total_messages: int,
     *     total_participants: int,
     *     statistics: array<int, array<string, mixed>>
     * }
     */
    public function analyze(string $contents): array
    {
        $participantCounts = [];
        $linkCounts = [];
        $imageCounts = [];
        $yearCounts = [];
        $monthCounts = [];
        $hourCounts = [];
        $wordCounts = [];
        $startDate = null;
        $endDate = null;

        foreach (preg_split('/\R/u', $contents) ?: [] as $line) {
            $message = $this->parseMessageLine($line);

            if ($message === null) {
                continue;
            }

            $sender = $message['sender'];
            $body = $message['body'];
            $date = $message['date'];

            $participantCounts[$sender] = ($participantCounts[$sender] ?? 0) + 1;
            $startDate = $startDate === null || $date->lt($startDate) ? $date : $startDate;
            $endDate = $endDate === null || $date->gt($endDate) ? $date : $endDate;

            $yearKey = $date->format('Y');
            $monthKey = $date->format('Y-m');
            $hourKey = $date->format('H');
            $yearCounts[$yearKey] = ($yearCounts[$yearKey] ?? 0) + 1;
            $monthCounts[$monthKey] = ($monthCounts[$monthKey] ?? 0) + 1;
            $hourCounts[$hourKey] = ($hourCounts[$hourKey] ?? 0) + 1;

            if (preg_match('/https?:\/\/\S+/i', $body)) {
                $linkCounts[$sender] = ($linkCounts[$sender] ?? 0) + 1;
            }

            if (str_contains(Str::lower($body), '<media omitted>') || str_contains(Str::lower($body), 'image omitted')) {
                $imageCounts[$sender] = ($imageCounts[$sender] ?? 0) + 1;
            }

            foreach ($this->words($body) as $word) {
                $wordCounts[$word] = ($wordCounts[$word] ?? 0) + 1;
            }
        }

        $statistics = [
            ...$this->rankedParticipantStats('active_member', $participantCounts),
            ...$this->rankedParticipantStats('link_poster', $linkCounts),
            ...$this->rankedParticipantStats('image_poster', $imageCounts),
            ...$this->rankedLabelStats('busiest_year', $yearCounts, 5),
            ...$this->rankedLabelStats('busiest_month', $monthCounts, 12),
            ...$this->rankedLabelStats('busiest_hour', $hourCounts, 24),
            ...$this->rankedLabelStats('word_cloud', $wordCounts, 30),
            ...$this->silentReaderStats(array_keys($participantCounts)),
        ];

        return [
            'import_start_date' => $startDate?->toDateString(),
            'import_end_date' => $endDate?->toDateString(),
            'total_messages' => array_sum($participantCounts),
            'total_participants' => count($participantCounts),
            'statistics' => $statistics,
        ];
    }

    /**
     * @return array{date: CarbonImmutable, sender: string, body: string}|null
     */
    private function parseMessageLine(string $line): ?array
    {
        $patterns = [
            '/^(?<date>\d{1,2}\/\d{1,2}\/\d{2,4}),?\s+(?<time>\d{1,2}[:.]\d{2}(?::\d{2})?)\s*(?<ampm>[AP]M|am|pm)?\s+-\s+(?<sender>[^:]+):\s+(?<body>.*)$/u',
            '/^\[(?<date>\d{1,2}\/\d{1,2}\/\d{2,4}),?\s+(?<time>\d{1,2}[:.]\d{2}(?::\d{2})?)\s*(?<ampm>[AP]M|am|pm)?\]\s+(?<sender>[^:]+):\s+(?<body>.*)$/u',
        ];

        foreach ($patterns as $pattern) {
            if (! preg_match($pattern, $line, $matches)) {
                continue;
            }

            $date = $this->parseDate($matches['date'], $matches['time'], $matches['ampm'] ?? null);

            if ($date === null) {
                return null;
            }

            return [
                'date' => $date,
                'sender' => trim($matches['sender']),
                'body' => trim($matches['body']),
            ];
        }

        return null;
    }

    private function parseDate(string $date, string $time, ?string $ampm): ?CarbonImmutable
    {
        $normalizedTime = str_replace('.', ':', $time).($ampm ? ' '.strtoupper($ampm) : '');
        $formats = $ampm
            ? ['m/d/y h:i A', 'd/m/y h:i A', 'm/d/Y h:i A', 'd/m/Y h:i A']
            : ['m/d/y H:i', 'd/m/y H:i', 'm/d/Y H:i', 'd/m/Y H:i', 'm/d/y H:i:s', 'd/m/y H:i:s', 'm/d/Y H:i:s', 'd/m/Y H:i:s'];

        foreach ($formats as $format) {
            try {
                return CarbonImmutable::createFromFormat($format, "{$date} {$normalizedTime}");
            } catch (\Throwable) {
                //
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function words(string $body): array
    {
        $body = Str::lower(strip_tags($body));
        preg_match_all('/[\pL\pN]{4,}/u', $body, $matches);

        $stopWords = [
            'yang', 'dengan', 'untuk', 'dari', 'dan', 'atau', 'ini', 'itu', 'akan', 'sudah',
            'https', 'media', 'omitted', 'image', 'para', 'kita', 'saya', 'kami', 'kamu',
        ];

        return collect($matches[0] ?? [])
            ->reject(fn (string $word): bool => in_array($word, $stopWords, true))
            ->values()
            ->all();
    }

    /**
     * @param  array<string, int>  $counts
     * @return array<int, array<string, mixed>>
     */
    private function rankedParticipantStats(string $category, array $counts): array
    {
        arsort($counts);

        return collect($counts)
            ->take(10)
            ->map(fn (int $count, string $label): array => [
                'category' => $category,
                'label' => $label,
                'alumni_id' => $this->matchAlumniId($label),
                'value' => $count,
                'metadata' => null,
            ])
            ->values()
            ->map(fn (array $row, int $index): array => $row + ['rank' => $index + 1])
            ->all();
    }

    /**
     * @param  array<string, int>  $counts
     * @return array<int, array<string, mixed>>
     */
    private function rankedLabelStats(string $category, array $counts, int $limit): array
    {
        arsort($counts);

        return collect($counts)
            ->take($limit)
            ->map(fn (int $count, string $label): array => [
                'category' => $category,
                'label' => $label,
                'alumni_id' => null,
                'value' => $count,
                'metadata' => null,
            ])
            ->values()
            ->map(fn (array $row, int $index): array => $row + ['rank' => $index + 1])
            ->all();
    }

    /**
     * @param  array<int, string>  $participantNames
     * @return array<int, array<string, mixed>>
     */
    private function silentReaderStats(array $participantNames): array
    {
        $normalizedParticipants = collect($participantNames)
            ->map(fn (string $name): string => $this->normalizeName($name))
            ->all();

        return Alumni::query()
            ->where('alumni_status', 'active')
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'nickname'])
            ->reject(function (Alumni $alumni) use ($normalizedParticipants): bool {
                return in_array($this->normalizeName($alumni->full_name), $normalizedParticipants, true)
                    || ($alumni->nickname && in_array($this->normalizeName($alumni->nickname), $normalizedParticipants, true));
            })
            ->take(10)
            ->values()
            ->map(fn (Alumni $alumni, int $index): array => [
                'category' => 'silent_reader',
                'label' => $alumni->full_name,
                'alumni_id' => $alumni->id,
                'value' => 0,
                'rank' => $index + 1,
                'metadata' => null,
            ])
            ->all();
    }

    private function matchAlumniId(string $label): ?int
    {
        $normalized = $this->normalizeName($label);

        return Alumni::query()
            ->where('alumni_status', 'active')
            ->get(['id', 'full_name', 'nickname'])
            ->first(fn (Alumni $alumni): bool => $this->normalizeName($alumni->full_name) === $normalized
                || ($alumni->nickname && $this->normalizeName($alumni->nickname) === $normalized))
            ?->id;
    }

    private function normalizeName(string $name): string
    {
        return Str::of($name)->lower()->replaceMatches('/[^a-z0-9]+/', '')->toString();
    }
}
