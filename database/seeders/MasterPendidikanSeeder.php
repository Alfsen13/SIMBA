<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MasterPendidikanSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->master() as $urutanJalur => $jalur) {
            $jalurId = $this->pertahankan('jalur_pendidikan', ['kode' => $jalur['kode']], [
                'nama' => $jalur['nama'],
                'deskripsi' => $jalur['deskripsi'] ?? null,
                'urutan' => $urutanJalur + 1,
                'aktif' => true,
            ]);

            foreach ($jalur['jenjang'] as $urutanJenjang => $jenjang) {
                $jenjangId = $this->pertahankan('jenjang_pendidikan', [
                    'jalur_id' => $jalurId,
                    'kode' => $jenjang['kode'],
                ], [
                    'nama' => $jenjang['nama'],
                    'urutan' => $urutanJenjang + 1,
                    'aktif' => true,
                ]);

                foreach ($jenjang['tingkat'] as $urutanTingkat => $namaTingkat) {
                    $this->pertahankan('tingkat_pendidikan', [
                        'jenjang_id' => $jenjangId,
                        'kode' => Str::slug($namaTingkat, '_'),
                    ], [
                        'nama' => $namaTingkat,
                        'urutan' => $urutanTingkat + 1,
                        'aktif' => true,
                    ]);
                }
            }
        }
    }

    private function pertahankan(string $tabel, array $kunci, array $nilai): int
    {
        $id = DB::table($tabel)->where($kunci)->value('id');

        if ($id !== null) {
            return (int) $id;
        }

        return (int) DB::table($tabel)->insertGetId($kunci + $nilai + [
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function master(): array
    {
        return [
            [
                'kode' => 'formal',
                'nama' => 'Formal',
                'deskripsi' => 'Pendidikan formal berjenjang: SD, SMP, SMA/SMK, dan perguruan tinggi.',
                'jenjang' => [
                    [
                        'kode' => 'sd',
                        'nama' => 'SD',
                        'tingkat' => ['Kelas 1', 'Kelas 2', 'Kelas 3', 'Kelas 4', 'Kelas 5', 'Kelas 6'],
                    ],
                    [
                        'kode' => 'smp',
                        'nama' => 'SMP',
                        'tingkat' => ['Kelas 7', 'Kelas 8', 'Kelas 9'],
                    ],
                    [
                        'kode' => 'sma',
                        'nama' => 'SMA',
                        'tingkat' => ['Kelas 10', 'Kelas 11', 'Kelas 12'],
                    ],
                    [
                        'kode' => 'smk',
                        'nama' => 'SMK',
                        'tingkat' => ['Kelas 10', 'Kelas 11', 'Kelas 12'],
                    ],
                    [
                        'kode' => 'perguruan_tinggi',
                        'nama' => 'Perguruan Tinggi',
                        'tingkat' => ['D3', 'D4', 'S1', 'S2', 'S3'],
                    ],
                ],
            ],
            [
                'kode' => 'nonformal',
                'nama' => 'Nonformal',
                'deskripsi' => 'Pendidikan nonformal: pendidikan kesetaraan.',
                'jenjang' => [
                    [
                        'kode' => 'kesetaraan',
                        'nama' => 'Kesetaraan',
                        'tingkat' => ['Paket A', 'Paket B', 'Paket C'],
                    ],
                ],
            ],
            [
                'kode' => 'self_learning',
                'nama' => 'Belajar Mandiri',
                'deskripsi' => 'Belajar mandiri di luar jalur formal dan nonformal; tanpa jenjang dan tingkat sekolah.',
                'jenjang' => [],
            ],
        ];
    }
}
