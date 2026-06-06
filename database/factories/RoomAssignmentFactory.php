<?php

namespace Database\Factories;

use App\Models\Alumni;
use App\Models\Room;
use App\Models\RoomAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomAssignment>
 */
class RoomAssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'alumni_id' => Alumni::factory(),
            'assigned_by' => User::factory(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
