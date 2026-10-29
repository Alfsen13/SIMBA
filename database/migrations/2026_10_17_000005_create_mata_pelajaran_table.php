<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->unique();
            $table->string('nama');
            $table->string('kategori', 100)->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index('kategori');
        });

        // pengajar_kompetensi harus dibuat SETELAH mata_pelajaran: FK-nya merujuk
        // ke sana. Di SQLite REFERENCES ke tabel yang belum ada tidak divalidasi,
        // di MySQL/MariaDB errno 150 ("foreign key constraint is incorrectly formed").
        Schema::create('pengajar_kompetensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->nullable()->constrained('mata_pelajaran')->nullOnDelete();
            $table->string('mata_pelajaran_lain')->nullable();
            $table->foreignId('jenjang_id')->nullable()->constrained('jenjang_pendidikan')->nullOnDelete();
            $table->foreignId('tingkat_id')->nullable()->constrained('tingkat_pendidikan')->nullOnDelete();
            $table->enum('status', ['klaim', 'disetujui', 'ditolak'])->default('klaim');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('disetujui_pada')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajar_kompetensi');
        Schema::dropIfExists('mata_pelajaran');
    }
};
