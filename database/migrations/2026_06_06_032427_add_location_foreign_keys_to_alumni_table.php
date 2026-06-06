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
        Schema::table('alumni', function (Blueprint $table) {
            $table->foreign('current_country_id')
                ->references('id')
                ->on('countries')
                ->nullOnDelete();

            $table->foreign('current_city_id')
                ->references('id')
                ->on('cities')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumni', function (Blueprint $table) {
            $table->dropForeign(['current_country_id']);
            $table->dropForeign(['current_city_id']);
        });
    }
};
