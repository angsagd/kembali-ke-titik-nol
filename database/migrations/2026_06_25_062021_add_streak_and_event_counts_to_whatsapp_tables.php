<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_imports', function (Blueprint $table) {
            // Streak grup
            $table->unsignedInteger('longest_active_streak')->default(0)->after('last_activity_at');
            $table->unsignedInteger('longest_silent_streak')->default(0)->after('longest_active_streak');

            // Event counts (sebelumnya dihitung on-the-fly via groupSystemEventCounts)
            $table->unsignedInteger('event_member_left')->default(0)->after('longest_silent_streak');
            $table->unsignedInteger('event_member_added')->default(0)->after('event_member_left');
            $table->unsignedInteger('event_member_removed')->default(0)->after('event_member_added');
            $table->unsignedInteger('event_phone_changed')->default(0)->after('event_member_removed');
            $table->unsignedInteger('event_security_code_changed')->default(0)->after('event_phone_changed');
            $table->unsignedInteger('event_group_name_changed')->default(0)->after('event_security_code_changed');
            $table->unsignedInteger('event_group_description_changed')->default(0)->after('event_group_name_changed');
            $table->unsignedInteger('event_group_icon_changed')->default(0)->after('event_group_description_changed');
        });

        Schema::table('whatsapp_member_stats', function (Blueprint $table) {
            // Streak per-anggota
            $table->unsignedInteger('longest_active_streak')->default(0)->after('active_days');
            $table->unsignedInteger('longest_silent_streak')->default(0)->after('longest_active_streak');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_imports', function (Blueprint $table) {
            $table->dropColumn([
                'longest_active_streak',
                'longest_silent_streak',
                'event_member_left',
                'event_member_added',
                'event_member_removed',
                'event_phone_changed',
                'event_security_code_changed',
                'event_group_name_changed',
                'event_group_description_changed',
                'event_group_icon_changed',
            ]);
        });

        Schema::table('whatsapp_member_stats', function (Blueprint $table) {
            $table->dropColumn(['longest_active_streak', 'longest_silent_streak']);
        });
    }
};
