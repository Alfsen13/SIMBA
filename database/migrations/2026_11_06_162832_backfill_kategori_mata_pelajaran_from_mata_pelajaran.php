<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ([
            ['mata_pelajaran', 'kategori'],
            ['kelas', 'kategori'],
            ['users', 'kategori_mapel'],
            ['aplikasi_pengajar', 'kategori_mapel'],
        ] as [$table, $column]) {
            $kategoriList = DB::table($table)
                ->whereNotNull($column)
                ->where($column, '<>', '')
                ->distinct()
                ->pluck($column);

            foreach ($kategoriList as $kategori) {
                DB::table('kategori_mata_pelajaran')->insertOrIgnore([
                    'nama' => $kategori,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The catalog may have been used or extended after the backfill.
    }
};
