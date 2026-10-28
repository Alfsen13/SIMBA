<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prediksi_risiko_pertemuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->foreignId('pertemuan_id')->constrained('pertemuan')->cascadeOnDelete();

            $table->decimal('rata_nilai', 5, 1)->nullable();
            $table->decimal('persen_terlambat', 5, 1)->nullable();
            $table->decimal('persen_terlewat', 5, 1)->nullable();
            $table->decimal('nilai_keseluruhan', 5, 1)->nullable();

            $table->decimal('skor_penyimpangan', 5, 2)->nullable();
            $table->enum('kategori_risiko', ['aman', 'perlu_pantau', 'berisiko_tinggi'])->nullable();
            $table->enum('status_data', ['lengkap', 'data_tidak_lengkap', 'data_tidak_valid'])->default('data_tidak_lengkap');

            // Metode & versi konfigurasi yang menghasilkan baris ini.
            $table->string('metode', 40)->nullable();
            $table->string('versi_konfigurasi', 60)->nullable();

            // Hasil klasifikasi Random Forest - kolom SENDIRI, tidak menimpa
            // kategori baseline, supaya kedua metode tetap bisa dibedakan.
            $table->string('kategori_risiko_rf', 20)->nullable();

            // Ramalan SVR untuk pertemuan berikutnya.
            $table->decimal('prediksi_nilai_mendatang', 5, 1)->nullable();

            $table->timestamps();

            $table->unique(['siswa_id', 'pertemuan_id']);
            $table->index('kategori_risiko');
            $table->index('status_data');
            $table->index('siswa_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prediksi_risiko_pertemuan');
    }
};
