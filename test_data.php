<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('role','pelajar')->first();
echo 'User: ' . ($user ? $user->name : 'NOT FOUND') . PHP_EOL;

$kelas = App\Models\Kelas::find(10);
echo 'Kelas 10: ' . ($kelas ? $kelas->nama_kelas : 'NOT FOUND') . PHP_EOL;
if ($kelas) {
    $pertemuan = $kelas->pertemuan()->find(1);
    echo 'Pertemuan 1: ' . ($pertemuan ? $pertemuan->nama_pertemuan : 'NOT FOUND') . PHP_EOL;
    if ($pertemuan) {
        echo 'Materi count: ' . $pertemuan->materi()->count() . PHP_EOL;
        echo 'Dibuka: ' . ($pertemuan->dibuka ? 'YES' : 'NO') . PHP_EOL;
        $materis = $pertemuan->materi()->get();
        foreach ($materis as $m) {
            echo "  - Materi: {$m->judul} (ID: {$m->id})" . PHP_EOL;
        }
    }
}