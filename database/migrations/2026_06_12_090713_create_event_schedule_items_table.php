<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('event_schedule_items', function (Blueprint $table) {
            $table->id();
            $table->enum('event_day', ['day_one', 'day_two']);
            $table->time('start_time');
            $table->string('activity', 200);
            $table->timestamps();

            $table->index(['event_day', 'start_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_schedule_items');
    }
};
