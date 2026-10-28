<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->char('sesi_id', 36)->nullable();
            $table->string('role');
            $table->text('message');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->foreignId('materi_id')->nullable()->constrained('materi')->nullOnDelete();

            $table->index('sesi_id');
        });

        Schema::create('sesi_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->char('sesi_id', 36)->nullable();
            $table->text('ringkasan');
            $table->dateTime('periode_mulai');
            $table->dateTime('periode_selesai');
            $table->timestamps();
            $table->foreignId('materi_id')->nullable()->constrained('materi')->nullOnDelete();
        });

        Schema::create('chat_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sesi_summary_id')->nullable()->constrained('sesi_summaries')->nullOnDelete();
            $table->text('ringkasan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_summaries');
        Schema::dropIfExists('sesi_summaries');
        Schema::dropIfExists('chat_logs');
    }
};
