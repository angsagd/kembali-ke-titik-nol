<?php

namespace App\Services\WhatsAppAnalyzer;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

class WhatsappParser
{
    /**
     * @return array<int, ParsedWhatsappActivity>
     */
    public function parse(string $contents): array
    {
        $activities = [];
        $current = null;

        foreach (preg_split('/\R/u', $contents) ?: [] as $index => $line) {
            $lineNumber = $index + 1;
            $timestamped = $this->parseTimestampedLine($line, $lineNumber);

            if ($timestamped !== null) {
                if ($current !== null) {
                    $activities[] = $this->classify($current);
                }

                $current = $timestamped;

                continue;
            }

            if ($current === null) {
                continue;
            }

            $current['raw_lines'][] = $line;

            if ($current['message_text'] !== null) {
                $current['message_text'] .= "\n".$line;
            }
        }

        if ($current !== null) {
            $activities[] = $this->classify($current);
        }

        return $activities;
    }

    public function normalizeName(string $name): string
    {
        return Str::of($name)
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '')
            ->toString();
    }

    /**
     * @return array{
     *     line_number: int,
     *     occurred_at_source: CarbonImmutable,
     *     occurred_at_display: CarbonImmutable,
     *     content: string,
     *     sender_name: string|null,
     *     sender_normalized: string|null,
     *     message_text: string|null,
     *     raw_lines: array<int, string>
     * }|null
     */
    private function parseTimestampedLine(string $line, int $lineNumber): ?array
    {
        $patterns = [
            '/^(?<date>\d{1,2}\/\d{1,2}\/\d{2,4}),\s+(?<time>\d{1,2}[:.]\d{2}(?::\d{2})?)\s*(?<ampm>[AP]M|am|pm)?\s+-\s+(?<content>.+)$/u',
            '/^\[(?<date>\d{1,2}\/\d{1,2}\/\d{2,4}),\s+(?<time>\d{1,2}[:.]\d{2}(?::\d{2})?)\s*(?<ampm>[AP]M|am|pm)?\]\s+(?<content>.+)$/u',
        ];

        foreach ($patterns as $pattern) {
            if (! preg_match($pattern, $line, $matches)) {
                continue;
            }

            $occurredAtSource = $this->parseDate($matches['date'], $matches['time'], $matches['ampm'] ?? null);

            if ($occurredAtSource === null) {
                return null;
            }

            $content = trim($matches['content']);
            $senderName = null;
            $senderNormalized = null;
            $messageText = null;

            if (preg_match('/^(?<sender>.+?):\s(?<message>.*)$/us', $content, $messageMatches)) {
                $senderName = trim($messageMatches['sender']);
                $senderNormalized = $this->normalizeName($senderName);
                $messageText = trim($messageMatches['message']);
            }

            return [
                'line_number' => $lineNumber,
                'occurred_at_source' => $occurredAtSource,
                'occurred_at_display' => $occurredAtSource->setTimezone('Asia/Jakarta'),
                'content' => $content,
                'sender_name' => $senderName,
                'sender_normalized' => $senderNormalized,
                'message_text' => $messageText,
                'raw_lines' => [$line],
            ];
        }

        return null;
    }

    private function parseDate(string $date, string $time, ?string $ampm): ?CarbonImmutable
    {
        $normalizedTime = str_replace('.', ':', $time).($ampm ? ' '.strtoupper($ampm) : '');
        $formats = $ampm
            ? ['m/d/y h:i A', 'm/d/Y h:i A']
            : ['m/d/y H:i', 'm/d/Y H:i', 'm/d/y H:i:s', 'm/d/Y H:i:s'];

        foreach ($formats as $format) {
            try {
                return CarbonImmutable::createFromFormat($format, "{$date} {$normalizedTime}", 'Asia/Makassar');
            } catch (\Throwable) {
                //
            }
        }

        return null;
    }

    /**
     * @param  array{
     *     line_number: int,
     *     occurred_at_source: CarbonImmutable,
     *     occurred_at_display: CarbonImmutable,
     *     content: string,
     *     sender_name: string|null,
     *     sender_normalized: string|null,
     *     message_text: string|null,
     *     raw_lines: array<int, string>
     * }  $activity
     */
    private function classify(array $activity): ParsedWhatsappActivity
    {
        $messageText = $activity['message_text'];
        $systemEventType = null;
        $activityType = $messageText === null ? 'system' : 'message';
        $senderName = $activity['sender_name'];
        $senderNormalized = $activity['sender_normalized'];
        $targetName = null;
        $targetNormalized = null;

        if ($messageText !== null && $this->isDeletedMessage($messageText)) {
            $activityType = 'system';
            $systemEventType = 'deleted_message';
        }

        if ($messageText === null) {
            $event = $this->classifySystemEvent($activity['content']);
            $systemEventType = $event['type'];
            $senderName = $event['sender_name'];
            $senderNormalized = $senderName ? $this->normalizeName($senderName) : null;
            $targetName = $event['target_name'];
            $targetNormalized = $targetName ? $this->normalizeName($targetName) : null;
        }

        $textForCounts = $messageText ?? $activity['content'];
        $hasSticker = $this->hasSticker($textForCounts);
        $hasMedia = $hasSticker || $this->hasMedia($textForCounts);
        $hasLink = $this->hasLink($textForCounts);
        $hasEmoji = $this->hasEmoji($textForCounts);

        return new ParsedWhatsappActivity(
            lineNumber: $activity['line_number'],
            occurredAtSource: $activity['occurred_at_source'],
            occurredAtDisplay: $activity['occurred_at_display'],
            activityType: $activityType,
            systemEventType: $systemEventType,
            senderName: $senderName,
            senderNormalized: $senderNormalized,
            targetName: $targetName,
            targetNormalized: $targetNormalized,
            messageText: $messageText,
            hasMedia: $hasMedia,
            hasSticker: $hasSticker,
            hasLink: $hasLink,
            hasEmoji: $hasEmoji,
            isDeletedMessage: $systemEventType === 'deleted_message',
            wordCount: $activityType === 'message' ? count($this->words($messageText ?? '')) : 0,
            characterCount: $activityType === 'message' ? mb_strlen($messageText ?? '') : 0,
            rawLines: $activity['raw_lines'],
        );
    }

    /**
     * @return array{type: string|null, sender_name: string|null, target_name: string|null}
     */
    private function classifySystemEvent(string $content): array
    {
        $patterns = [
            'member_added' => '/^(?<sender>.+?) added (?<target>.+)$/u',
            'member_removed' => '/^(?<sender>.+?) removed (?<target>.+)$/u',
            'member_left' => '/^(?<target>.+?) left$/u',
            'phone_number_changed' => '/^(?<target>.+?) changed their phone number/u',
            'security_code_changed' => '/^Your security code with (?<target>.+?) changed/u',
            'group_name_changed' => '/^(?<sender>.+?) changed (?:the subject|this group)/u',
            'group_icon_changed' => '/^(?<sender>.+?) (?:changed|deleted) this group(?:\'s|’) icon/u',
            'group_description_changed' => '/^(?<sender>.+?) changed the group description/u',
            'disappearing_message_changed' => '/^(?<sender>.+?) (?:updated the message timer|turned off disappearing messages)/u',
        ];

        foreach ($patterns as $type => $pattern) {
            if (! preg_match($pattern, $content, $matches)) {
                continue;
            }

            return [
                'type' => $type,
                'sender_name' => isset($matches['sender']) ? trim($matches['sender']) : null,
                'target_name' => isset($matches['target']) ? trim($matches['target']) : null,
            ];
        }

        return ['type' => 'unknown_system_event', 'sender_name' => null, 'target_name' => null];
    }

    private function isDeletedMessage(string $messageText): bool
    {
        return Str::of($messageText)->lower()->trim()->exactly('this message was deleted');
    }

    private function hasMedia(string $text): bool
    {
        $text = Str::lower($text);

        return str_contains($text, '<media omitted>')
            || str_contains($text, 'image omitted')
            || str_contains($text, 'video omitted')
            || str_contains($text, 'document omitted');
    }

    private function hasSticker(string $text): bool
    {
        return str_contains(Str::lower($text), '<sticker omitted>')
            || str_contains(Str::lower($text), 'sticker omitted');
    }

    private function hasLink(string $text): bool
    {
        return preg_match('/https?:\/\/[^\s]+|www\.[^\s]+/iu', $text) === 1;
    }

    private function hasEmoji(string $text): bool
    {
        return preg_match('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}]/u', $text) === 1;
    }

    /**
     * @return array<int, string>
     */
    private function words(string $text): array
    {
        preg_match_all('/[\pL\pN]{2,}/u', Str::lower($text), $matches);

        return $matches[0] ?? [];
    }
}
