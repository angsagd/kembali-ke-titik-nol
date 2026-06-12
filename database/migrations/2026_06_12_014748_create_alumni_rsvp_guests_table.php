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
        Schema::create('alumni_rsvp_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumni_id')
                ->constrained('alumni')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('sequence');
            $table->enum('shirt_size', ['S', 'M', 'L', 'XL', 'XXL']);
            $table->enum('shirt_type', ['child', 'male', 'female']);
            $table->timestamps();

            $table->unique(['alumni_id', 'sequence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni_rsvp_guests');
    }
};
