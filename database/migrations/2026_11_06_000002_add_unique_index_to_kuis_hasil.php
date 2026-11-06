<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasDuplicateResults = DB::table('kuis_hasil')
            ->select('kuis_id', 'siswa_id')
            ->groupBy('kuis_id', 'siswa_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicateResults) {
            throw new RuntimeException(
                'Tidak dapat menambahkan indeks unik kuis_hasil: ada lebih dari satu hasil untuk kuis dan siswa yang sama. Tinjau data duplikat sebelum menjalankan migrasi.'
            );
        }

        Schema::table('kuis_hasil', function (Blueprint $table) {
            $table->unique(['kuis_id', 'siswa_id'], 'kuis_hasil_satu_per_siswa_unik');
        });
    }

    public function down(): void
    {
        Schema::table('kuis_hasil', function (Blueprint $table) {
            $table->dropUnique('kuis_hasil_satu_per_siswa_unik');
        });
    }
};
