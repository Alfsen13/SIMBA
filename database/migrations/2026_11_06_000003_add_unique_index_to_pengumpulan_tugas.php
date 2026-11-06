<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasDuplicateSubmissions = DB::table('pengumpulan_tugas')
            ->select('tugas_id', 'siswa_id')
            ->groupBy('tugas_id', 'siswa_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicateSubmissions) {
            throw new RuntimeException(
                'Tidak dapat menambahkan indeks unik pengumpulan_tugas: ada lebih dari satu pengumpulan untuk tugas dan siswa yang sama. Tinjau data duplikat sebelum menjalankan migrasi.'
            );
        }

        Schema::table('pengumpulan_tugas', function (Blueprint $table) {
            $table->unique(['tugas_id', 'siswa_id'], 'pengumpulan_tugas_satu_per_siswa_unik');
        });
    }

    public function down(): void
    {
        Schema::table('pengumpulan_tugas', function (Blueprint $table) {
            $table->dropUnique('pengumpulan_tugas_satu_per_siswa_unik');
        });
    }
};
