<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aplikasi_pengajar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('pendidikan_tertinggi')->nullable();
            $table->text('pengalaman_mengajar')->nullable();
            $table->string('cv_path')->nullable();
            $table->string('ijazah_path')->nullable();
            $table->string('kategori_mapel')->nullable();
            $table->text('mata_pelajaran')->nullable();
            $table->string('jenjang_dan_tingkat')->nullable();
            $table->enum('status_aplikasi', ['draft', 'pending', 'under_review', 'revision_required', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('catatan_operator')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aplikasi_pengajar');
    }
};
