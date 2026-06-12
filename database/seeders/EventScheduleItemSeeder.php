<?php

namespace Database\Seeders;

use App\Models\EventScheduleItem;
use Illuminate\Database\Seeder;

class EventScheduleItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (EventScheduleItem::query()->exists()) {
            return;
        }

        $timestamp = now();

        EventScheduleItem::query()->insert([
            ['event_day' => 'day_one', 'start_time' => '15:00', 'activity' => 'Check-in & Registrasi', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['event_day' => 'day_one', 'start_time' => '19:00', 'activity' => 'Dinner & Malam Akrab', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['event_day' => 'day_one', 'start_time' => '21:00', 'activity' => 'Angkringan Night', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['event_day' => 'day_two', 'start_time' => '09:45', 'activity' => 'Campus Walk', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['event_day' => 'day_two', 'start_time' => '13:00', 'activity' => 'Sarasehan Alumni', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['event_day' => 'day_two', 'start_time' => '18:00', 'activity' => 'Gala Dinner', 'created_at' => $timestamp, 'updated_at' => $timestamp],
        ]);
    }
}
