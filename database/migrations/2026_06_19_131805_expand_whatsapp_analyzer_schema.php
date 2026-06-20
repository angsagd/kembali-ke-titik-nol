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
        Schema::dropIfExists('whatsapp_statistics');

        Schema::table('whatsapp_imports', function (Blueprint $table) {
            $table->string('group_name')->nullable()->after('file_path');
            $table->string('timezone_source', 64)->default('Asia/Makassar')->after('group_name');
            $table->string('timezone_display', 64)->default('Asia/Jakarta')->after('timezone_source');
            $table->unsignedInteger('total_lines')->default(0)->after('timezone_display');
            $table->unsignedInteger('total_activities')->default(0)->after('total_lines');
            $table->unsignedInteger('total_system_events')->default(0)->after('total_messages');
            $table->unsignedInteger('total_words')->default(0)->after('total_participants');
            $table->unsignedInteger('total_emoji_messages')->default(0)->after('total_words');
            $table->unsignedInteger('total_media_messages')->default(0)->after('total_emoji_messages');
            $table->unsignedInteger('total_sticker_messages')->default(0)->after('total_media_messages');
            $table->unsignedInteger('total_link_messages')->default(0)->after('total_sticker_messages');
            $table->unsignedInteger('total_deleted_messages')->default(0)->after('total_link_messages');
            $table->dateTime('first_activity_at')->nullable()->after('total_deleted_messages');
            $table->dateTime('last_activity_at')->nullable()->after('first_activity_at');
        });

        Schema::create('whatsapp_member_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumni_id')->nullable()->constrained('alumni')->nullOnDelete();
            $table->string('display_name');
            $table->string('normalized_name')->unique();
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('whatsapp_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_import_id')->constrained('whatsapp_imports')->cascadeOnDelete();
            $table->foreignId('whatsapp_member_mapping_id')->nullable()->constrained('whatsapp_member_mappings')->nullOnDelete();
            $table->foreignId('alumni_id')->nullable()->constrained('alumni')->nullOnDelete();
            $table->string('display_name');
            $table->string('normalized_name');
            $table->dateTime('first_message_at')->nullable();
            $table->dateTime('last_message_at')->nullable();
            $table->unsignedInteger('total_messages')->default(0);
            $table->unsignedInteger('total_words')->default(0);
            $table->unsignedInteger('total_characters')->default(0);
            $table->timestamps();

            $table->unique(['whatsapp_import_id', 'normalized_name'], 'whatsapp_members_import_name_unique');
            $table->index(['whatsapp_import_id', 'total_messages']);
            $table->index('alumni_id');
        });

        Schema::create('whatsapp_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_import_id')->constrained('whatsapp_imports')->cascadeOnDelete();
            $table->foreignId('whatsapp_member_id')->nullable()->constrained('whatsapp_members')->nullOnDelete();
            $table->foreignId('alumni_id')->nullable()->constrained('alumni')->nullOnDelete();
            $table->unsignedInteger('line_number')->nullable();
            $table->dateTime('occurred_at_source');
            $table->dateTime('occurred_at_display');
            $table->enum('activity_type', ['message', 'system']);
            $table->string('system_event_type', 100)->nullable();
            $table->string('sender_name')->nullable();
            $table->string('sender_normalized')->nullable();
            $table->string('target_name')->nullable();
            $table->string('target_normalized')->nullable();
            $table->longText('message_text')->nullable();
            $table->boolean('has_media')->default(false);
            $table->boolean('has_sticker')->default(false);
            $table->boolean('has_link')->default(false);
            $table->boolean('has_emoji')->default(false);
            $table->boolean('is_deleted_message')->default(false);
            $table->unsignedInteger('word_count')->default(0);
            $table->unsignedInteger('character_count')->default(0);
            $table->longText('raw_text');
            $table->timestamps();

            $table->index(['whatsapp_import_id', 'occurred_at_display'], 'whatsapp_activities_import_time_index');
            $table->index(['whatsapp_import_id', 'activity_type'], 'whatsapp_activities_import_type_index');
            $table->index(['whatsapp_import_id', 'system_event_type'], 'whatsapp_activities_import_event_index');
            $table->index('whatsapp_member_id');
            $table->index('alumni_id');
        });

        Schema::create('whatsapp_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_import_id')->constrained('whatsapp_imports')->cascadeOnDelete();
            $table->date('stat_date');
            $table->unsignedInteger('total_activities')->default(0);
            $table->unsignedInteger('total_messages')->default(0);
            $table->unsignedInteger('total_system_events')->default(0);
            $table->unsignedInteger('total_media')->default(0);
            $table->unsignedInteger('total_links')->default(0);
            $table->unsignedInteger('total_emojis')->default(0);
            $table->unsignedInteger('total_deleted')->default(0);
            $table->timestamps();

            $table->unique(['whatsapp_import_id', 'stat_date'], 'whatsapp_daily_stats_import_date_unique');
        });

        Schema::create('whatsapp_member_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_import_id')->constrained('whatsapp_imports')->cascadeOnDelete();
            $table->foreignId('whatsapp_member_id')->constrained('whatsapp_members')->cascadeOnDelete();
            $table->foreignId('alumni_id')->nullable()->constrained('alumni')->nullOnDelete();
            $table->unsignedInteger('total_messages')->default(0);
            $table->unsignedInteger('pure_text_messages')->default(0);
            $table->unsignedInteger('emoji_messages')->default(0);
            $table->unsignedInteger('media_messages')->default(0);
            $table->unsignedInteger('sticker_messages')->default(0);
            $table->unsignedInteger('link_messages')->default(0);
            $table->unsignedInteger('location_messages')->default(0);
            $table->unsignedInteger('deleted_messages')->default(0);
            $table->unsignedInteger('morning_messages')->default(0);
            $table->unsignedInteger('working_hour_messages')->default(0);
            $table->unsignedInteger('after_work_messages')->default(0);
            $table->unsignedInteger('midnight_messages')->default(0);
            $table->unsignedInteger('weekend_messages')->default(0);
            $table->unsignedInteger('active_days')->default(0);
            $table->unsignedInteger('total_words')->default(0);
            $table->unsignedInteger('total_characters')->default(0);
            $table->dateTime('first_message_at')->nullable();
            $table->dateTime('last_message_at')->nullable();
            $table->timestamps();

            $table->unique(['whatsapp_import_id', 'whatsapp_member_id'], 'whatsapp_member_stats_import_member_unique');
            $table->index(['whatsapp_import_id', 'total_messages']);
            $table->index('alumni_id');
        });

        Schema::create('whatsapp_member_event_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_import_id')->constrained('whatsapp_imports')->cascadeOnDelete();
            $table->foreignId('whatsapp_member_id')->constrained('whatsapp_members')->cascadeOnDelete();
            $table->foreignId('alumni_id')->nullable()->constrained('alumni')->nullOnDelete();
            $table->unsignedInteger('member_added_as_actor')->default(0);
            $table->unsignedInteger('member_added_as_target')->default(0);
            $table->unsignedInteger('member_removed_as_actor')->default(0);
            $table->unsignedInteger('member_removed_as_target')->default(0);
            $table->unsignedInteger('member_left')->default(0);
            $table->unsignedInteger('phone_number_changed')->default(0);
            $table->unsignedInteger('security_code_changed')->default(0);
            $table->unsignedInteger('group_name_changed')->default(0);
            $table->unsignedInteger('group_description_changed')->default(0);
            $table->unsignedInteger('group_icon_changed')->default(0);
            $table->unsignedInteger('disappearing_message_changed')->default(0);
            $table->timestamps();

            $table->unique(['whatsapp_import_id', 'whatsapp_member_id'], 'whatsapp_member_event_stats_import_member_unique');
            $table->index('alumni_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_member_event_stats');
        Schema::dropIfExists('whatsapp_member_stats');
        Schema::dropIfExists('whatsapp_daily_stats');
        Schema::dropIfExists('whatsapp_activities');
        Schema::dropIfExists('whatsapp_members');
        Schema::dropIfExists('whatsapp_member_mappings');

        Schema::table('whatsapp_imports', function (Blueprint $table) {
            $table->dropColumn([
                'group_name',
                'timezone_source',
                'timezone_display',
                'total_lines',
                'total_activities',
                'total_system_events',
                'total_words',
                'total_emoji_messages',
                'total_media_messages',
                'total_sticker_messages',
                'total_link_messages',
                'total_deleted_messages',
                'first_activity_at',
                'last_activity_at',
            ]);
        });

        Schema::create('whatsapp_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_import_id')->constrained('whatsapp_imports')->cascadeOnDelete();
            $table->string('category', 100)->index();
            $table->string('label', 150)->nullable();
            $table->foreignId('alumni_id')->nullable()->constrained('alumni')->nullOnDelete();
            $table->decimal('value', 15, 2)->nullable();
            $table->unsignedInteger('rank')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['category', 'rank']);
        });
    }
};
