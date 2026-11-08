<?php

namespace App\Services;

use Random\Engine\Mt19937;
use Random\Randomizer;

class DatasetSintetisNilaiService
{
    public const PATH = 'ml/dataset_sintetis_nilai.json';

    public const PROFIL = [
        'tinggi_stabil' => [83, 97, 0],
        'tinggi_menurun' => [83, 97, -1],
        'menengah_meningkat' => [65, 74, 1],
        'menengah_stabil' => [65, 74, 0],
        'menengah_menurun' => [65, 74, -1],
        'rendah_meningkat' => [40, 57, 1],
        'rendah_stabil' => [40, 57, 0],
    ];

    public function bangun(): array
    {
        $acak = new Randomizer(new Mt19937(20261101));
        $samples = $targets = $siswaIds = $kelasIds = $profilSiswa = [];
        $profil = array_keys(self::PROFIL);

        for ($siswa = 1; $siswa <= 140; $siswa++) {
            $nama = $profil[($siswa - 1) % count($profil)];
            [$min, $max, $arah] = self::PROFIL[$nama];
            $awal = $acak->getInt($min * 10, $max * 10) / 10;
            $laju = $arah * $acak->getInt(31, 40) / 10;
            $nilai = [];

            for ($pertemuan = 0; $pertemuan < 8; $pertemuan++) {
                $nilai[] = round(max(0, min(100, $awal + $laju * $pertemuan
                    + $acak->getInt(-5, 5) / 10)), 1);
            }

            $profilSiswa[$siswa] = $nama;
            $hadir = $tidakAktif = 0;
            for ($pertemuan = 0; $pertemuan < 7; $pertemuan++) {
                $hadir += $acak->getInt(1, 100) <= 90 ? 1 : 0;
                $aktif = $acak->getInt(1, 100) <= 90;
                $tidakAktif += $aktif ? 0 : 1;
                $fitur = [
                    'rata_durasi_belajar' => $aktif ? (float) $acak->getInt(15, 75) : null,
                    'persen_tugas_terkumpul' => (float) ($acak->getInt(0, 2) * 50),
                    'tren_nilai' => $pertemuan === 0 ? null : round($nilai[$pertemuan] - $nilai[$pertemuan - 1], 1),
                    'persen_kehadiran' => round($hadir / ($pertemuan + 1) * 100, 1),
                    'rata_nilai' => $nilai[$pertemuan],
                    'jumlah_pertemuan_tidak_aktif' => (float) $tidakAktif,
                ];

                $samples[] = array_map(fn (string $namaFitur) => $fitur[$namaFitur], FiturPertemuanService::fiturModel());
                $targets[] = $nilai[$pertemuan + 1];
                $siswaIds[] = $siswa;
                $kelasIds[] = 1;
            }
        }

        return [
            'versi' => 'sintetis-nilai-1',
            'sumber' => 'simulasi, bukan data siswa nyata',
            'rf' => [
                'fitur' => KlasifikasiRisikoService::FITUR,
                'samples' => [],
                'labels' => [],
                'siswa_ids' => [],
            ],
            'svr' => [
                'target' => PrediksiMendatangService::TARGET,
                'fitur' => FiturPertemuanService::fiturModel(),
                'samples' => $samples,
                'targets' => $targets,
                'siswa_ids' => $siswaIds,
                'kelas_ids' => $kelasIds,
                'profil_siswa' => $profilSiswa,
            ],
            'ringkasan' => [
                'rf_samples' => 0,
                'rf_siswa_unik' => 0,
                'svr_samples' => count($samples),
                'svr_siswa_unik' => count($profilSiswa),
            ],
        ];
    }
}
