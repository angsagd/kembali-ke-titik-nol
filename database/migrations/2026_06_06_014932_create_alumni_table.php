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
        Schema::create('alumni', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();
            $table->string('student_number', 50)
                ->nullable()
                ->unique();
            $table->string('full_name', 150);
            $table->string('nickname', 100)->nullable();
            $table->string('email', 150)->nullable();
            $table->unsignedBigInteger('current_city_id')->nullable()->index();
            $table->unsignedBigInteger('current_country_id')->nullable()->index();
            $table->string('company', 150)->nullable();
            $table->string('job_title', 150)->nullable();
            $table->enum('alumni_status', ['active', 'deceased'])->default('active')->index();
            $table->enum('rsvp_status', ['pending', 'attending', 'not_attending'])->default('pending')->index();
            $table->text('special_notes')->nullable();
            $table->text('short_story')->nullable();
            $table->text('memorable_story')->nullable();
            $table->text('message_to_friends')->nullable();
            $table->string('college_photo_path')->nullable();
            $table->string('current_photo_path')->nullable();
            $table->boolean('is_profile_completed')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni');
    }
};
