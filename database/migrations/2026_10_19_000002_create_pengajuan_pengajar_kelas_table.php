<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pengajuan pengajar mengisi kelas. Pengajar tidak lagi langsung menjadi
     * guru_id saat mengambil kelas — dia mengajukan bahan rencana ajar, lalu
     * operator menyetujui (kelas.guru_id terisi) atau menolak.
     */
    public function up(): void
    {
        Schema::create('pengajuan_pengajar_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('rencana_ajar');
            $table->string('berkas_path')->nullable();
            $table->string('status', 20)->default('pending'); // pending|disetujui|ditolak
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('catatan_operator')->nullable();
            $table->timestamps();

            $table->unique(['kelas_id', 'user_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_pengajar_kelas');
    }
};
