<?php

namespace Database\Factories;

use App\Models\Alumni;
use App\Models\MediaItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaItem>
 */
class MediaItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['photo', 'video']);

        return [
            'type' => $type,
            'uploaded_by_alumni_id' => Alumni::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'file_path' => $type === 'photo' ? 'documentation/photos/'.fake()->uuid().'.jpg' : null,
            'thumbnail_path' => null,
            'video_url' => $type === 'video' ? 'https://www.youtube.com/watch?v='.fake()->regexify('[A-Za-z0-9_-]{11}') : null,
            'provider' => $type === 'video' ? 'youtube' : null,
            'month' => fake()->optional()->numberBetween(1, 12),
            'year' => fake()->numberBetween(1996, 2026),
            'visibility' => fake()->randomElement(['internal', 'public']),
            'file_size' => $type === 'photo' ? fake()->numberBetween(100000, 1000000) : null,
            'width' => $type === 'photo' ? fake()->numberBetween(800, 1920) : null,
            'height' => $type === 'photo' ? fake()->numberBetween(600, 1080) : null,
        ];
    }

    /**
     * Indicate that the media item is a photo.
     */
    public function photo(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'photo',
            'file_path' => 'documentation/photos/'.fake()->uuid().'.jpg',
            'thumbnail_path' => null,
            'video_url' => null,
            'provider' => null,
            'file_size' => fake()->numberBetween(100000, 1000000),
        ]);
    }

    /**
     * Indicate that the media item is a video.
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'video',
            'file_path' => null,
            'thumbnail_path' => null,
            'video_url' => 'https://www.youtube.com/watch?v='.fake()->regexify('[A-Za-z0-9_-]{11}'),
            'provider' => 'youtube',
            'file_size' => null,
            'width' => null,
            'height' => null,
        ]);
    }
}
