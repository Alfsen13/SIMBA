<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->foreignId('jalur_id')->nullable()->constrained('jalur_pendidikan')->nullOnDelete();
            $table->foreignId('jenjang_id')->nullable()->constrained('jenjang_pendidikan')->nullOnDelete();
            $table->foreignId('tingkat_id')->nullable()->constrained('tingkat_pendidikan')->nullOnDelete();
            $table->foreignId('mata_pelajaran_id')->nullable()->constrained('mata_pelajaran')->nullOnDelete();
            $table->integer('kapasitas')->nullable();
            $table->enum('status', ['draft', 'dibuka', 'ditutup', 'arsip'])->default('dibuka');
            $table->string('aturan_kelayakan', 50)->default('sesuai_profil');
            $table->string('periode')->nullable();
            $table->index('status');
            $table->index('aturan_kelayakan');
        });
    }

    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropIndex(['aturan_kelayakan']);
            $table->dropIndex(['status']);
            $table->dropColumn('periode');
            $table->dropColumn('aturan_kelayakan');
            $table->dropColumn('status');
            $table->dropColumn('kapasitas');
            $table->dropConstrainedForeignId('mata_pelajaran_id');
            $table->dropConstrainedForeignId('tingkat_id');
            $table->dropConstrainedForeignId('jenjang_id');
            $table->dropConstrainedForeignId('jalur_id');
        });
    }
};
