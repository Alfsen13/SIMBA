<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$tabel = ['jalur_pendidikan','jenjang_pendidikan','tingkat_pendidikan','profil_pendidikan','pengajuan_perubahan_pendidikan','aplikasi_pengajar','pengajar_kompetensi','mata_pelajaran','users','kelas'];

echo "== TABEL ==\n";
foreach ($tabel as $t) {
    echo str_pad($t, 35) . (Schema::hasTable($t) ? 'ADA' : 'TIDAK') . "\n";
}

echo "\n== BARIS MIGRASI 2026_10_17 ==\n";
$rows = DB::table('migrations')->where('migration','like','2026_10_17%')->get();
if ($rows->isEmpty()) echo "(tidak ada baris sama sekali)\n";
foreach ($rows as $r) echo $r->migration . ' batch=' . $r->batch . "\n";

echo "\n== FOREIGN KEYS pengajar_kompetensi ==\n";
$fks = DB::select("SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='pengajar_kompetensi' AND REFERENCED_TABLE_NAME IS NOT NULL");
foreach ($fks as $f) echo "{$f->COLUMN_NAME} -> {$f->REFERENCED_TABLE_NAME}.{$f->REFERENCED_COLUMN_NAME}\n";
if (!$fks) echo "(tidak ada FK)\n";

echo "\n== TIPE users.id vs mata_pelajaran.id ==\n";
$u = DB::select("SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='id'");
$m = DB::select("SELECT COLUMN_NAME, DATA_TYPE, COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='mata_pelajaran' AND COLUMN_NAME='id'");
echo 'users.id: ' . ($u[0]->COLUMN_TYPE ?? 'N/A') . "\n";
echo 'mata_pelajaran.id: ' . ($m[0]->COLUMN_TYPE ?? '(tabel tidak ada)') . "\n";

echo "\n== ROWS ==\n";
foreach (['jalur_pendidikan','jenjang_pendidikan','tingkat_pendidikan','mata_pelajaran'] as $t) {
    if (Schema::hasTable($t)) echo $t . ': ' . DB::table($t)->count() . " baris\n";
}
