<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilai_harian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->integer('nilai')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_harian');
    }
};
