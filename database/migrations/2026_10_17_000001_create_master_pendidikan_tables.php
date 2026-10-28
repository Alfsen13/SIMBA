<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jalur_pendidikan', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->unique();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->index('aktif');
        });

        Schema::create('jenjang_pendidikan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jalur_id')->constrained('jalur_pendidikan')->cascadeOnDelete();
            $table->string('kode', 50);
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->unique(['jalur_id', 'kode']);
            $table->index('aktif');
        });

        Schema::create('tingkat_pendidikan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenjang_id')->constrained('jenjang_pendidikan')->cascadeOnDelete();
            $table->string('kode', 50);
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->unique(['jenjang_id', 'kode']);
            $table->index('aktif');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tingkat_pendidikan');
        Schema::dropIfExists('jenjang_pendidikan');
        Schema::dropIfExists('jalur_pendidikan');
    }
};
