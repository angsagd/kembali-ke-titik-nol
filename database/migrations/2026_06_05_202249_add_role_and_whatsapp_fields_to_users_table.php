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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')
                ->after('id')
                ->constrained()
                ->restrictOnDelete();
            $table->string('whatsapp_number', 30)
                ->after('email')
                ->unique();
            $table->boolean('is_active')
                ->after('password')
                ->default(true)
                ->index();
            $table->timestamp('last_login_at')
                ->after('is_active')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn([
                'role_id',
                'whatsapp_number',
                'is_active',
                'last_login_at',
            ]);
        });
    }
};
