<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aktivitas_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pertemuan_id')->nullable()->constrained('pertemuan')->nullOnDelete();
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->string('tipe');
            $table->text('deskripsi')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('tipe');
            $table->index(['siswa_id', 'pertemuan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aktivitas_log');
    }
};
