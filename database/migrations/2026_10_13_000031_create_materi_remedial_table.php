<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materi_remedial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('prediksi_ai_id')->nullable()->constrained('prediksi_ai')->cascadeOnDelete();
            $table->foreignId('pertemuan_id')->nullable()->constrained('pertemuan')->nullOnDelete();
            $table->string('sumber', 20)->default('prediksi');
            $table->decimal('skor_pertemuan', 5, 2)->nullable();
            $table->string('topik_lemah')->nullable();
            $table->text('ringkasan')->nullable();
            $table->text('latihan')->nullable();
            $table->json('bloom_tujuan')->nullable();
            $table->json('tugas_remedial')->nullable();
            $table->json('jawaban_remedial')->nullable();
            $table->json('hasil_remedial')->nullable();
            $table->boolean('bloom_tercapai')->nullable();
            $table->text('mastery_alasan')->nullable();
            $table->timestamp('dinilai_at')->nullable();
            $table->string('provider_llm')->nullable();
            $table->enum('status', ['belum_dibaca', 'dibaca', 'selesai'])->default('belum_dibaca');
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->index(['siswa_id', 'pertemuan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materi_remedial');
    }
};
