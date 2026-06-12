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
            $table->enum('rsvp_party_type', ['self', 'family'])
                ->default('self')
                ->after('rsvp_status')
                ->index();
            $table->unsignedTinyInteger('family_members_count')
                ->default(0)
                ->after('rsvp_party_type');
            $table->enum('shirt_size', ['S', 'M', 'L', 'XL', 'XXL'])
                ->nullable()
                ->after('family_members_count');
            $table->enum('shirt_type', ['child', 'male', 'female'])
                ->nullable()
                ->after('shirt_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumni', function (Blueprint $table) {
            $table->dropColumn([
                'rsvp_party_type',
                'family_members_count',
                'shirt_size',
                'shirt_type',
            ]);
        });
    }
};
