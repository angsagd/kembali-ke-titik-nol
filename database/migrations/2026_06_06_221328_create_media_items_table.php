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
        Schema::create('media_items', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['photo', 'video'])->index();
            $table->foreignId('uploaded_by_alumni_id')->constrained('alumni')->cascadeOnDelete();
            $table->string('title', 150)->nullable();
            $table->text('description')->nullable();
            $table->string('file_path', 255)->nullable();
            $table->string('video_url', 500)->nullable();
            $table->tinyInteger('month')->nullable();
            $table->smallInteger('year');
            $table->enum('visibility', ['internal', 'public'])->default('internal')->index();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'year']);
            $table->index('uploaded_by_alumni_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_items');
    }
};
