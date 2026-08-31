<?php

namespace App\Services;

use App\Models\AlumniTimeline;
use Illuminate\Support\Collection;

class HistoricalLocationResolver
{
    /**
     * Resolve the latest known timeline location for every alumni at a selected year.
     *
     * @return Collection<int, array{id: string, city: string, country: ?string, latitude: float, longitude: float, alumni_count: int, alumni: Collection}>
     */
    public function resolve(int $year): Collection
    {
        return AlumniTimeline::query()
            ->with('alumni')
            ->where('year', '<=', $year)
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('alumni_id')
            ->orderByDesc('year')
            ->orderByRaw('month is null')
            ->orderByDesc('month')
            ->get()
            ->unique('alumni_id')
            ->filter(fn (AlumniTimeline $timeline): bool => $timeline->alumni !== null)
            ->groupBy(fn (AlumniTimeline $timeline): string => $this->locationKey(
                $timeline->city,
                $timeline->country,
                (float) $timeline->latitude,
                (float) $timeline->longitude,
            ))
            ->map(function (Collection $timelines, string $id): array {
                /** @var AlumniTimeline $location */
                $location = $timelines->first();

                return [
                    'id' => $id,
                    'city' => $location->city,
                    'country' => $location->country,
                    'latitude' => (float) $location->latitude,
                    'longitude' => (float) $location->longitude,
                    'alumni_count' => $timelines->count(),
                    'alumni' => $timelines->pluck('alumni')->sortBy('full_name')->values(),
                ];
            })
            ->sortByDesc('alumni_count')
            ->values();
    }

    private function locationKey(?string $city, ?string $country, float $latitude, float $longitude): string
    {
        return implode('::', [$city, $country, $latitude, $longitude]);
    }
}
