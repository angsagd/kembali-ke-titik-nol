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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_statistics');
    }
};
