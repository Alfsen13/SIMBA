<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kehadiran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pertemuan_id')->constrained('pertemuan')->cascadeOnDelete();
            $table->string('status')->default('hadir');
            $table->text('catatan')->nullable();
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['siswa_id', 'pertemuan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kehadiran');
    }
};
