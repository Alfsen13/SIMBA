<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fitur_hybrid_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('periode')->nullable();
            $table->timestamps();
            $table->decimal('rata_nilai', 5, 1)->nullable();
            $table->decimal('persen_terlambat', 5, 1)->nullable();
            $table->decimal('persen_terlewat', 5, 1)->nullable();
            $table->decimal('nilai_keseluruhan', 5, 1)->nullable();
        });

        Schema::create('fitur_siswa_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pertemuan_id')->nullable()->constrained('pertemuan')->nullOnDelete();
            $table->date('tanggal');
            $table->timestamps();
            $table->decimal('rata_nilai', 5, 1)->nullable();
            $table->unsignedInteger('frekuensi_akses_pertemuan')->default(0);
            $table->decimal('rata_durasi_belajar', 5, 1)->nullable();
            $table->decimal('persen_tugas_terkumpul', 5, 1)->nullable();
            $table->decimal('tren_nilai', 5, 1)->nullable();
            $table->decimal('persen_kehadiran', 5, 1)->nullable();
            $table->unsignedInteger('jumlah_pertemuan_tidak_aktif')->default(0);

            $table->unique(['siswa_id', 'pertemuan_id']);
            $table->index('tanggal');
            $table->index('siswa_id');
        });

        Schema::create('prediksi_ai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->enum('hasil', ['aman', 'perlu_pantau', 'berisiko_tinggi'])->default('aman');
            $table->double('skor_risiko')->default(0);
            $table->json('feature_vector')->nullable();
            $table->timestamps();

            $table->index('hasil');
        });

        Schema::create('prediksi_mendatang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('pertemuan_sumber_id')->nullable()->constrained('pertemuan')->nullOnDelete();
            $table->foreignId('pertemuan_target_id')->nullable()->constrained('pertemuan')->nullOnDelete();
            $table->unsignedSmallInteger('horizon_hari')->default(14);
            $table->decimal('nilai_ideal', 5, 1)->default(100);
            $table->decimal('persentase_meleset', 5, 2)->nullable();
            $table->decimal('prediksi_nilai', 5, 1)->nullable();
            $table->decimal('nilai_aktual', 5, 1)->nullable();
            $table->decimal('mae_individual', 5, 2)->nullable();
            $table->enum('status_data', ['lengkap', 'data_tidak_lengkap'])->default('data_tidak_lengkap');
            $table->string('model_versi', 40)->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ground_truth_risiko', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal');
            $table->enum('label', ['berisiko_tinggi', 'perlu_pantau', 'aman']);
            $table->enum('sumber', ['guru', 'sistem'])->default('sistem');
            $table->text('catatan')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->unique(['siswa_id', 'tanggal']);
            $table->index('label');
            $table->index('sumber');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ground_truth_risiko');
        Schema::dropIfExists('prediksi_mendatang');
        Schema::dropIfExists('prediksi_ai');
        Schema::dropIfExists('fitur_siswa_history');
        Schema::dropIfExists('fitur_hybrid_siswa');
    }
};
