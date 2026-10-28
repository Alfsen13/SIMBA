<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('original_name');
            $table->string('path');
            $table->string('type')->nullable();
            $table->integer('size')->default(0);
            $table->string('kategori', 100)->nullable();
            $table->string('status')->default('pending');
            $table->text('error_message')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unsignedBigInteger('materi_id')->nullable();
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();

            $table->index('kategori');
        });

        Schema::create('materi', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('bab')->nullable();
            $table->foreignId('pertemuan_id')->nullable()->constrained('pertemuan')->nullOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('users')->cascadeOnDelete();
            $table->string('file_path')->nullable();
            $table->string('file_type')->nullable();
            $table->timestamps();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->foreign('materi_id')->references('id')->on('materi')->nullOnDelete();
        });

        Schema::create('materi_bab_summary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materi_id')->unique()->constrained('materi')->cascadeOnDelete();
            $table->text('ringkasan');
            $table->timestamps();
        });

        Schema::create('document_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->text('content');
            $table->json('embedding')->nullable();
            $table->integer('chunk_index');
            $table->unsignedSmallInteger('page_start')->nullable();
            $table->unsignedSmallInteger('page_end')->nullable();
            $table->string('kategori', 100)->nullable();
            $table->timestamps();

            $table->index('kategori');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_chunks');
        Schema::dropIfExists('materi_bab_summary');

        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['materi_id']);
        });

        Schema::dropIfExists('materi');
        Schema::dropIfExists('documents');
    }
};
