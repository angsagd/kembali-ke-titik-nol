<?php

namespace App\Services\WhatsAppAnalyzer;

use App\Models\Alumni;
use App\Models\WhatsappActivity;
use App\Models\WhatsappImport;
use App\Models\WhatsappMember;
use App\Models\WhatsappMemberMapping;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WhatsappImportProcessor
{
    public function __construct(
        private readonly WhatsappParser $parser,
    ) {}

    public function process(WhatsappImport $whatsappImport, string $contents): void
    {
        $activities = collect($this->parser->parse($contents));
        $lineCount = $contents === '' ? 0 : substr_count($contents, "\n") + 1;

        DB::transaction(function () use ($whatsappImport, $activities, $lineCount): void {
            $this->clearPreviousAnalysis($whatsappImport);

            $members = $this->persistMembers($whatsappImport, $activities);
            $this->persistActivities($whatsappImport, $activities, $members);
            $this->persistDailyStats($whatsappImport, $activities);
            $this->persistMemberStats($whatsappImport, $activities, $members);
            $this->persistMemberEventStats($whatsappImport, $activities, $members);
            $this->updateImportSummary($whatsappImport, $activities, $members, $lineCount);
        });
    }

    private function clearPreviousAnalysis(WhatsappImport $whatsappImport): void
    {
        $whatsappImport->memberEventStats()->delete();
        $whatsappImport->memberStats()->delete();
        $whatsappImport->dailyStats()->delete();
        $whatsappImport->activities()->delete();
        $whatsappImport->members()->delete();
    }

    /**
     * @param  Collection<int, ParsedWhatsappActivity>  $activities
     * @return Collection<string, WhatsappMember>
     */
    private function persistMembers(WhatsappImport $whatsappImport, Collection $activities): Collection
    {
        $alumniByName = $this->alumniByNormalizedName();
        $mappings = WhatsappMemberMapping::query()
            ->get(['id', 'alumni_id', 'normalized_name'])
            ->keyBy('normalized_name');

        return $activities
            ->filter(fn (ParsedWhatsappActivity $activity): bool => $activity->activityType === 'message'
                && filled($activity->senderName)
                && $activity->senderNormalized !== 'metaai')
            ->groupBy(fn (ParsedWhatsappActivity $activity): string => (string) $activity->senderNormalized)
            ->map(function (Collection $memberActivities, string $normalizedName) use ($whatsappImport, $alumniByName, $mappings): WhatsappMember {
                /** @var ParsedWhatsappActivity $first */
                $first = $memberActivities->first();
                $mapping = $mappings->get($normalizedName);

                return WhatsappMember::query()->create([
                    'whatsapp_import_id' => $whatsappImport->id,
                    'whatsapp_member_mapping_id' => $mapping?->id,
                    'alumni_id' => $mapping?->alumni_id ?? $alumniByName->get($normalizedName)?->id,
                    'display_name' => (string) $first->senderName,
                    'normalized_name' => $normalizedName,
                    'first_message_at' => $memberActivities->min(fn (ParsedWhatsappActivity $activity) => $activity->occurredAtDisplay),
                    'last_message_at' => $memberActivities->max(fn (ParsedWhatsappActivity $activity) => $activity->occurredAtDisplay),
                    'total_messages' => $memberActivities->count(),
                    'total_words' => $memberActivities->sum('wordCount'),
                    'total_characters' => $memberActivities->sum('characterCount'),
                ]);
            });
    }

    /**
     * @return Collection<string, Alumni>
     */
    private function alumniByNormalizedName(): Collection
    {
        return Alumni::query()
            ->where('alumni_status', 'active')
            ->get(['id', 'full_name', 'nickname'])
            ->flatMap(function (Alumni $alumni): array {
                $names = [$this->parser->normalizeName($alumni->full_name) => $alumni];

                if ($alumni->nickname) {
                    $names[$this->parser->normalizeName($alumni->nickname)] = $alumni;
                }

                return $names;
            });
    }

    /**
     * @param  Collection<int, ParsedWhatsappActivity>  $activities
     * @param  Collection<string, WhatsappMember>  $members
     */
    private function persistActivities(WhatsappImport $whatsappImport, Collection $activities, Collection $members): void
    {
        $activities->chunk(500)->each(function (Collection $chunk) use ($whatsappImport, $members): void {
            WhatsappActivity::query()->insert($chunk
                ->map(function (ParsedWhatsappActivity $activity) use ($whatsappImport, $members): array {
                    $member = $activity->senderNormalized ? $members->get($activity->senderNormalized) : null;

                    return [
                        'whatsapp_import_id' => $whatsappImport->id,
                        'whatsapp_member_id' => $member?->id,
                        'alumni_id' => $member?->alumni_id,
                        'line_number' => $activity->lineNumber,
                        'occurred_at_source' => $activity->occurredAtSource->toDateTimeString(),
                        'occurred_at_display' => $activity->occurredAtDisplay->toDateTimeString(),
                        'activity_type' => $activity->activityType,
                        'system_event_type' => $activity->systemEventType,
                        'sender_name' => $activity->senderName,
                        'sender_normalized' => $activity->senderNormalized,
                        'target_name' => $activity->targetName,
                        'target_normalized' => $activity->targetNormalized,
                        'message_text' => $activity->messageText,
                        'has_media' => $activity->hasMedia,
                        'has_sticker' => $activity->hasSticker,
                        'has_link' => $activity->hasLink,
                        'has_emoji' => $activity->hasEmoji,
                        'is_deleted_message' => $activity->isDeletedMessage,
                        'word_count' => $activity->wordCount,
                        'character_count' => $activity->characterCount,
                        'raw_text' => $activity->rawText(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })
                ->all());
        });
    }

    /**
     * @param  Collection<int, ParsedWhatsappActivity>  $activities
     */
    private function persistDailyStats(WhatsappImport $whatsappImport, Collection $activities): void
    {
        $activities
            ->groupBy(fn (ParsedWhatsappActivity $activity): string => $activity->occurredAtDisplay->toDateString())
            ->each(function (Collection $dailyActivities, string $date) use ($whatsappImport): void {
                $whatsappImport->dailyStats()->create([
                    'stat_date' => $date,
                    'total_activities' => $dailyActivities->count(),
                    'total_messages' => $dailyActivities->where('activityType', 'message')->count(),
                    'total_system_events' => $dailyActivities->where('activityType', 'system')->count(),
                    'total_media' => $dailyActivities->where('hasMedia', true)->count(),
                    'total_links' => $dailyActivities->where('hasLink', true)->count(),
                    'total_emojis' => $dailyActivities->where('hasEmoji', true)->count(),
                    'total_deleted' => $dailyActivities->where('isDeletedMessage', true)->count(),
                ]);
            });
    }

    /**
     * @param  Collection<int, ParsedWhatsappActivity>  $activities
     * @param  Collection<string, WhatsappMember>  $members
     */
    private function persistMemberStats(WhatsappImport $whatsappImport, Collection $activities, Collection $members): void
    {
        $members->each(function (WhatsappMember $member, string $normalizedName) use ($whatsappImport, $activities): void {
            $memberActivities = $activities->filter(fn (ParsedWhatsappActivity $activity): bool => $activity->senderNormalized === $normalizedName);
            $messageActivities = $memberActivities->where('activityType', 'message');

            $whatsappImport->memberStats()->create([
                'whatsapp_member_id' => $member->id,
                'alumni_id' => $member->alumni_id,
                'total_messages' => $messageActivities->count(),
                'pure_text_messages' => $messageActivities->filter(fn (ParsedWhatsappActivity $activity): bool => ! $activity->hasSticker && ! $activity->hasMedia && ! $activity->hasLink && ! $activity->hasEmoji)->count(),
                'emoji_messages' => $messageActivities->filter(fn (ParsedWhatsappActivity $activity): bool => ! $activity->hasSticker && ! $activity->hasMedia && ! $activity->hasLink && $activity->hasEmoji)->count(),
                'media_messages' => $messageActivities->filter(fn (ParsedWhatsappActivity $activity): bool => ! $activity->hasSticker && $activity->hasMedia)->count(),
                'sticker_messages' => $messageActivities->where('hasSticker', true)->count(),
                'link_messages' => $messageActivities->filter(fn (ParsedWhatsappActivity $activity): bool => ! $activity->hasSticker && ! $activity->hasMedia && $activity->hasLink)->count(),
                'location_messages' => $messageActivities->filter(fn (ParsedWhatsappActivity $activity): bool => $this->isLocationMessage($activity))->count(),
                'deleted_messages' => $memberActivities->where('isDeletedMessage', true)->count(),
                'morning_messages' => $messageActivities->filter(fn (ParsedWhatsappActivity $activity): bool => $this->isMorning($activity))->count(),
                'working_hour_messages' => $messageActivities->filter(fn (ParsedWhatsappActivity $activity): bool => $this->isWorkingHour($activity))->count(),
                'after_work_messages' => $messageActivities->filter(fn (ParsedWhatsappActivity $activity): bool => $this->isAfterWork($activity))->count(),
                'midnight_messages' => $messageActivities->filter(fn (ParsedWhatsappActivity $activity): bool => $this->isMidnight($activity))->count(),
                'weekend_messages' => $messageActivities->filter(fn (ParsedWhatsappActivity $activity): bool => $activity->occurredAtDisplay->isWeekend())->count(),
                'active_days' => $messageActivities->map(fn (ParsedWhatsappActivity $activity): string => $activity->occurredAtDisplay->toDateString())->unique()->count(),
                'total_words' => $messageActivities->sum('wordCount'),
                'total_characters' => $messageActivities->sum('characterCount'),
                'first_message_at' => $member->first_message_at,
                'last_message_at' => $member->last_message_at,
            ]);
        });
    }

    /**
     * @param  Collection<int, ParsedWhatsappActivity>  $activities
     * @param  Collection<string, WhatsappMember>  $members
     */
    private function persistMemberEventStats(WhatsappImport $whatsappImport, Collection $activities, Collection $members): void
    {
        $members->each(function (WhatsappMember $member, string $normalizedName) use ($whatsappImport, $activities): void {
            $actorEvents = $activities->filter(fn (ParsedWhatsappActivity $activity): bool => $activity->senderNormalized === $normalizedName);
            $targetEvents = $activities->filter(fn (ParsedWhatsappActivity $activity): bool => $activity->targetNormalized === $normalizedName);

            $whatsappImport->memberEventStats()->create([
                'whatsapp_member_id' => $member->id,
                'alumni_id' => $member->alumni_id,
                'member_added_as_actor' => $actorEvents->where('systemEventType', 'member_added')->count(),
                'member_added_as_target' => $targetEvents->where('systemEventType', 'member_added')->count(),
                'member_removed_as_actor' => $actorEvents->where('systemEventType', 'member_removed')->count(),
                'member_removed_as_target' => $targetEvents->where('systemEventType', 'member_removed')->count(),
                'member_left' => $targetEvents->where('systemEventType', 'member_left')->count(),
                'phone_number_changed' => $targetEvents->where('systemEventType', 'phone_number_changed')->count(),
                'security_code_changed' => $targetEvents->where('systemEventType', 'security_code_changed')->count(),
                'group_name_changed' => $actorEvents->where('systemEventType', 'group_name_changed')->count(),
                'group_description_changed' => $actorEvents->where('systemEventType', 'group_description_changed')->count(),
                'group_icon_changed' => $actorEvents->where('systemEventType', 'group_icon_changed')->count(),
                'disappearing_message_changed' => $actorEvents->where('systemEventType', 'disappearing_message_changed')->count(),
            ]);
        });
    }

    /**
     * @param  Collection<int, ParsedWhatsappActivity>  $activities
     * @param  Collection<string, WhatsappMember>  $members
     */
    private function updateImportSummary(WhatsappImport $whatsappImport, Collection $activities, Collection $members, int $lineCount): void
    {
        $firstActivity = $activities->min(fn (ParsedWhatsappActivity $activity) => $activity->occurredAtDisplay);
        $lastActivity = $activities->max(fn (ParsedWhatsappActivity $activity) => $activity->occurredAtDisplay);

        $whatsappImport->forceFill([
            'timezone_source' => 'Asia/Makassar',
            'timezone_display' => 'Asia/Jakarta',
            'total_lines' => $lineCount,
            'total_activities' => $activities->count(),
            'total_messages' => $activities->where('activityType', 'message')->count(),
            'total_system_events' => $activities->where('activityType', 'system')->count(),
            'total_participants' => $members->count(),
            'total_words' => $activities->where('activityType', 'message')->sum('wordCount'),
            'total_emoji_messages' => $activities->where('hasEmoji', true)->count(),
            'total_media_messages' => $activities->where('hasMedia', true)->count(),
            'total_sticker_messages' => $activities->where('hasSticker', true)->count(),
            'total_link_messages' => $activities->where('hasLink', true)->count(),
            'total_deleted_messages' => $activities->where('isDeletedMessage', true)->count(),
            'first_activity_at' => $firstActivity,
            'last_activity_at' => $lastActivity,
            'import_start_date' => $firstActivity?->toDateString(),
            'import_end_date' => $lastActivity?->toDateString(),
            'status' => 'completed',
            'processed_at' => now(),
        ])->save();
    }

    private function isLocationMessage(ParsedWhatsappActivity $activity): bool
    {
        $message = str($activity->messageText ?? '')->lower();

        return $message->contains('location')
            || $message->contains('live location')
            || $message->contains('maps.google.com')
            || $message->contains('goo.gl/maps');
    }

    private function isMorning(ParsedWhatsappActivity $activity): bool
    {
        $hour = (int) $activity->occurredAtDisplay->format('G');

        return $hour >= 4 && $hour < 8;
    }

    private function isWorkingHour(ParsedWhatsappActivity $activity): bool
    {
        $hour = (int) $activity->occurredAtDisplay->format('G');

        return $activity->occurredAtDisplay->dayOfWeekIso <= 5 && $hour >= 8 && $hour < 16;
    }

    private function isAfterWork(ParsedWhatsappActivity $activity): bool
    {
        $hour = (int) $activity->occurredAtDisplay->format('G');

        return $hour >= 16 && $hour < 23;
    }

    private function isMidnight(ParsedWhatsappActivity $activity): bool
    {
        $hour = (int) $activity->occurredAtDisplay->format('G');

        return $hour >= 23 || $hour < 4;
    }
}
