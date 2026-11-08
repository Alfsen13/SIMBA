<?php

namespace App\Services;

use Random\Engine\Mt19937;
use Random\Randomizer;

class DatasetSintetisRisikoService
{
    public const PATH = 'ml/dataset_sintetis_risiko.json';

    public function __construct(private PrediksiRisikoService $risiko) {}

    public function bangun(): array
    {
        $acak = new Randomizer(new Mt19937(20261108));
        $samples = $labels = $siswaIds = [];

        for ($siswa = 1; $siswa <= 120; $siswa++) {
            $profil = ($siswa - 1) % 3;
            [$minNilai, $maxNilai, $minTerlewat, $maxTerlewat] = match ($profil) {
                0 => [80, 98, 0, 1],
                1 => [55, 85, 0, 3],
                2 => [30, 65, 1, 4],
            };
            $nilaiDasar = $acak->getInt($minNilai, $maxNilai);

            for ($pertemuan = 0; $pertemuan < 4; $pertemuan++) {
                $terlewat = $acak->getInt($minTerlewat, $maxTerlewat);
                $terkumpul = 4 - $terlewat;
                $terlambat = $acak->getInt(0, $terkumpul);
                $nilai = [];

                for ($i = 0; $i < $terkumpul + 1; $i++) {
                    $nilai[] = max(0, min(100, $nilaiDasar + $acak->getInt(-12, 12)));
                }

                $rataNilai = round(array_sum($nilai) / count($nilai), 1);
                $parameter = [
                    'rata_nilai' => $rataNilai,
                    'persen_terlambat' => $terlambat * 25.0,
                    'persen_terlewat' => $terlewat * 25.0,
                    'nilai_keseluruhan' => $rataNilai,
                ];
                $hasil = $this->risiko->hitungSkor($parameter);
                $samples[] = array_map(fn (string $fitur) => $parameter[$fitur] / 100, KlasifikasiRisikoService::FITUR);
                $labels[] = $hasil['kategori_risiko'];
                $siswaIds[] = $siswa;
            }
        }

        return [
            'versi' => 'sintetis-risiko-1',
            'sumber' => 'simulasi, bukan data siswa nyata',
            'rf' => [
                'target' => KlasifikasiRisikoService::TARGET,
                'fitur' => KlasifikasiRisikoService::FITUR,
                'label' => KlasifikasiRisikoService::LABELS,
                'sumber_label' => PrediksiRisikoService::METODE,
                'samples' => $samples,
                'labels' => $labels,
                'siswa_ids' => $siswaIds,
            ],
            'ringkasan' => [
                'rf_samples' => count($samples),
                'rf_siswa_unik' => count(array_unique($siswaIds)),
                'distribusi_label' => array_count_values($labels),
            ],
        ];
    }
}
