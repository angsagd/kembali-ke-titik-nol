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
        Schema::create('whatsapp_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('file_name', 255)->nullable();
            $table->string('file_path', 255)->nullable();
            $table->date('import_start_date')->nullable();
            $table->date('import_end_date')->nullable();
            $table->unsignedInteger('total_messages')->default(0);
            $table->unsignedInteger('total_participants')->default(0);
            $table->enum('status', ['uploaded', 'processing', 'completed', 'failed'])->default('uploaded')->index();
            $table->timestamp('processed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_imports');
    }
};
