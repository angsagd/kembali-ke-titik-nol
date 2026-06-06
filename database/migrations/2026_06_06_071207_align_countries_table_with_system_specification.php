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
        Schema::table('countries', function (Blueprint $table) {
            $table->renameColumn('iso_code', 'code');
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->string('code', 10)->nullable()->change();
            $table->decimal('latitude', 10, 7)->nullable()->after('code');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
            $table->renameColumn('code', 'iso_code');
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->string('iso_code', 3)->nullable()->change();
        });
    }
};
