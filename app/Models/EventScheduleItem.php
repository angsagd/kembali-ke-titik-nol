<?php

namespace App\Models;

use Database\Factories\EventScheduleItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['event_day', 'start_time', 'activity'])]
class EventScheduleItem extends Model
{
    /** @use HasFactory<EventScheduleItemFactory> */
    use HasFactory;

    public function displayTime(): string
    {
        return str_replace(':', '.', substr($this->start_time, 0, 5));
    }
}
