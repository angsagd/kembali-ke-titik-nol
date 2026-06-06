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
        Schema::table('media_items', function (Blueprint $table) {
            $table->string('thumbnail_path', 255)->nullable()->after('file_path');
            $table->unsignedInteger('file_size')->nullable()->after('visibility');
            $table->enum('provider', ['youtube', 'google_drive', 'other'])->nullable()->after('video_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media_items', function (Blueprint $table) {
            $table->dropColumn(['thumbnail_path', 'file_size', 'provider']);
        });
    }
};
