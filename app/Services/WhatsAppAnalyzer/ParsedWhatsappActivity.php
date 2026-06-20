<?php

namespace App\Services\WhatsAppAnalyzer;

use Carbon\CarbonImmutable;

class ParsedWhatsappActivity
{
    /**
     * @param  array<int, string>  $rawLines
     */
    public function __construct(
        public int $lineNumber,
        public CarbonImmutable $occurredAtSource,
        public CarbonImmutable $occurredAtDisplay,
        public string $activityType,
        public ?string $systemEventType,
        public ?string $senderName,
        public ?string $senderNormalized,
        public ?string $targetName,
        public ?string $targetNormalized,
        public ?string $messageText,
        public bool $hasMedia,
        public bool $hasSticker,
        public bool $hasLink,
        public bool $hasEmoji,
        public bool $isDeletedMessage,
        public int $wordCount,
        public int $characterCount,
        public array $rawLines,
    ) {}

    public function rawText(): string
    {
        return implode("\n", $this->rawLines);
    }
}
