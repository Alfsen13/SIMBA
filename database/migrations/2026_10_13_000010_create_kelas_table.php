<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kelas');
            $table->string('kode_kelas')->unique();
            $table->text('deskripsi')->nullable();
            $table->string('kategori', 100)->nullable();
            $table->foreignId('guru_id')->nullable()->constrained('users');
            $table->timestamps();

            $table->index('guru_id');
            $table->index('kategori');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
