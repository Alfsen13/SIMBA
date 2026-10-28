<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->unique(['kelas_id', 'siswa_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas_siswa');
    }
};
