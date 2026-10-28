<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tugas', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pertemuan_id')->nullable()->constrained('pertemuan')->nullOnDelete();
            $table->foreignId('materi_id')->nullable()->constrained('materi')->nullOnDelete();
            $table->enum('tipe', ['upload', 'pilihan_ganda', 'essai'])->default('upload');
            $table->json('soal')->nullable();
            $table->dateTime('deadline')->nullable();
            $table->timestamps();
        });

        Schema::create('tugas_materi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_id')->constrained('tugas')->cascadeOnDelete();
            $table->foreignId('materi_id')->constrained('materi')->cascadeOnDelete();

            $table->unique(['tugas_id', 'materi_id'], 'tugas_materi_unik');
        });

        Schema::create('pengumpulan_tugas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tugas_id')->constrained('tugas')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
            $table->string('file')->nullable();
            $table->json('jawaban')->nullable();
            $table->integer('nilai')->nullable();
            $table->integer('nilai_ai')->nullable();
            $table->json('hasil_ai')->nullable();
            $table->timestamp('koreksi_ai_at')->nullable();
            $table->boolean('terlambat')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumpulan_tugas');
        Schema::dropIfExists('tugas_materi');
        Schema::dropIfExists('tugas');
    }
};
