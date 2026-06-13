<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumni', function (Blueprint $table) {
            $table->dropForeign(['current_city_id']);
            $table->dropForeign(['current_country_id']);
            $table->dropIndex(['current_city_id']);
            $table->dropIndex(['current_country_id']);
            $table->dropColumn(['current_city_id', 'current_country_id']);
        });

        Schema::table('alumni', function (Blueprint $table) {
            $table->string('city', 120)->nullable()->index()->after('email');
            $table->string('country', 100)->nullable()->index()->after('city');
            $table->decimal('latitude', 10, 7)->nullable()->after('country');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });

        Schema::table('alumni_timelines', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropForeign(['country_id']);
            $table->dropColumn(['city_id', 'country_id']);
        });

        Schema::table('alumni_timelines', function (Blueprint $table) {
            $table->string('city', 120)->nullable()->index()->after('year');
            $table->string('country', 100)->nullable()->index()->after('city');
        });

        Schema::drop('cities');
        Schema::drop('countries');
    }

    public function down(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('code', 10)->nullable()->unique();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
            $table->unique(['country_id', 'name']);
        });

        Schema::table('alumni', function (Blueprint $table) {
            $table->dropColumn(['city', 'country', 'latitude', 'longitude']);
            $table->unsignedBigInteger('current_city_id')->nullable()->index()->after('email');
            $table->unsignedBigInteger('current_country_id')->nullable()->index()->after('current_city_id');
            $table->foreign('current_city_id')->references('id')->on('cities')->nullOnDelete();
            $table->foreign('current_country_id')->references('id')->on('countries')->nullOnDelete();
        });

        Schema::table('alumni_timelines', function (Blueprint $table) {
            $table->dropColumn(['city', 'country']);
            $table->foreignId('city_id')->nullable()->after('year')->constrained()->nullOnDelete();
            $table->foreignId('country_id')->nullable()->after('city_id')->constrained()->nullOnDelete();
        });
    }
};
