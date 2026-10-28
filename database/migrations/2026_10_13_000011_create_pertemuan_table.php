<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pertemuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->string('nama_pertemuan');
            $table->integer('urutan')->default(0);
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('dibuka')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pertemuan');
    }
};
