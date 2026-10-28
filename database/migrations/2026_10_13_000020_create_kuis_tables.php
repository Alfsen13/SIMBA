<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kuis', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('pertemuan_id')->nullable()->constrained('pertemuan')->nullOnDelete();
            $table->foreignId('materi_id')->nullable()->constrained('materi')->nullOnDelete();
            $table->json('soal');
            $table->integer('durasi_menit')->default(30);
            $table->dateTime('tanggal_mulai')->nullable();
            $table->dateTime('tanggal_selesai')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();

            $table->index('is_aktif');
        });

        Schema::create('kuis_materi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kuis_id')->constrained('kuis')->cascadeOnDelete();
            $table->foreignId('materi_id')->constrained('materi')->cascadeOnDelete();

            $table->unique(['kuis_id', 'materi_id'], 'kuis_materi_unik');
        });

        Schema::create('kuis_hasil', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kuis_id')->constrained('kuis')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
            $table->integer('skor')->default(0);
            $table->json('jawaban_siswa')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kuis_hasil');
        Schema::dropIfExists('kuis_materi');
        Schema::dropIfExists('kuis');
    }
};
