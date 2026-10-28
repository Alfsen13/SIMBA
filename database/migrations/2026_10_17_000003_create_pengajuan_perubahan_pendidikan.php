<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_perubahan_pendidikan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('jalur_id')->constrained('jalur_pendidikan');
            $table->foreignId('jenjang_id')->nullable()->constrained('jenjang_pendidikan')->nullOnDelete();
            $table->foreignId('tingkat_id')->nullable()->constrained('tingkat_pendidikan')->nullOnDelete();
            $table->string('institusi')->nullable();
            $table->string('program')->nullable();
            $table->text('minat')->nullable();
            $table->enum('jenis', ['kenaikan_kelas', 'perpindahan_jenjang', 'perubahan_tingkat_program', 'lainnya'])->default('kenaikan_kelas');
            $table->enum('status', ['draft', 'pending', 'under_review', 'revision_required', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('catatan_operator')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_perubahan_pendidikan');
    }
};
