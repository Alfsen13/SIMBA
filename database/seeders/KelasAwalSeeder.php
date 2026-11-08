<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KelasAwalSeeder extends Seeder
{
    public function run(): void
    {
        $cari = fn (string $tabel, string $kode) => DB::table($tabel)->where('kode', $kode)->value('id');
        $mapel = fn (string $kode) => DB::table('mata_pelajaran')->where('kode', $kode)->first(['id', 'kategori']);

        foreach ($this->kelas() as $baris) {
            if (DB::table('kelas')->where('kode_kelas', $baris['kode'])->exists()) {
                continue;
            }

            $m = $mapel($baris['mapel']);

            DB::table('kelas')->insert(
                [
                    'kode_kelas' => $baris['kode'],
                    'nama_kelas' => $baris['nama'],
                    'deskripsi' => $baris['deskripsi'] ?? null,
                    'kategori' => $m->kategori ?? null,
                    'guru_id' => null,
                    'jalur_id' => $cari('jalur_pendidikan', $baris['jalur']),
                    'jenjang_id' => isset($baris['jenjang']) ? $cari('jenjang_pendidikan', $baris['jenjang']) : null,
                    'tingkat_id' => isset($baris['tingkat']) ? $cari('tingkat_pendidikan', $baris['tingkat']) : null,
                    'mata_pelajaran_id' => $m->id,
                    'kapasitas' => $baris['kapasitas'] ?? null,
                    'status' => $baris['status'] ?? 'dibuka',
                    'aturan_kelayakan' => $baris['aturan'] ?? 'sesuai_profil',
                    'periode' => '2026/2027 Ganjil',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function kelas(): array
    {
        return [
            [
                'kode' => 'MTKSD4XQ',
                'nama' => 'Matematika Kelas 4 SD',
                'mapel' => 'matematika',
                'jalur' => 'formal', 'jenjang' => 'sd', 'tingkat' => 'kelas_4',
                'kapasitas' => 30,
                'deskripsi' => 'Pembelajaran matematika untuk siswa SD kelas 4.',
            ],
            [
                'kode' => 'BIOSTD8R',
                'nama' => 'Sains Biologi Kelas 8 SMP',
                'mapel' => 'biologi',
                'jalur' => 'formal', 'jenjang' => 'smp', 'tingkat' => 'kelas_8',
                'kapasitas' => 32,
                'deskripsi' => 'Eksplorasi biologi untuk siswa SMP kelas 8.',
            ],
            [
                'kode' => 'BIND7KLP',
                'nama' => 'Bahasa Indonesia Kelas 7',
                'mapel' => 'bahasa_indonesia',
                'jalur' => 'formal', 'jenjang' => 'smp', 'tingkat' => 'kelas_7',
                'kapasitas' => 32,
                'deskripsi' => 'Literasi dan keterampilan berbahasa Indonesia.',
            ],
            [
                'kode' => 'BING10VP',
                'nama' => 'Bahasa Inggris Kelas 10',
                'mapel' => 'bahasa_inggris',
                'jalur' => 'formal', 'jenjang' => 'sma', 'tingkat' => 'kelas_10',
                'kapasitas' => 36,
                'deskripsi' => 'English for senior high school students.',
            ],
            [
                'kode' => 'INF11TRB',
                'nama' => 'Informatika Kelas 11',
                'mapel' => 'informatika',
                'jalur' => 'formal', 'jenjang' => 'sma', 'tingkat' => 'kelas_11',
                'kapasitas' => 36,
                'aturan' => 'terbuka',
                'deskripsi' => 'Kelas pengayaan: terbuka untuk pelajar jalur apa pun.',
            ],
            [
                'kode' => 'PKTBSRSJ',
                'nama' => 'Persiapan Ujian Paket B',
                'mapel' => 'matematika',
                'jalur' => 'nonformal', 'jenjang' => 'kesetaraan', 'tingkat' => 'paket_b',
                'kapasitas' => 25,
                'aturan' => 'sesuai_jenjang',
                'deskripsi' => 'Lintas tingkat: semua tingkat Paket B boleh bergabung.',
            ],
            [
                'kode' => 'OLM11KHK',
                'nama' => 'Kelas Khusus Olimpiade Sains',
                'mapel' => 'kimia',
                'jalur' => 'formal', 'jenjang' => 'sma', 'tingkat' => 'kelas_11',
                'kapasitas' => 15,
                'aturan' => 'khusus',
                'status' => 'draft',
                'deskripsi' => 'Kelas khusus: pengajuan peserta disetujui operator.',
            ],
            [
                'kode' => 'LTRBSMDR',
                'nama' => 'Literasi Umum Belajar Mandiri',
                'mapel' => 'bahasa_indonesia',
                'jalur' => 'self_learning',
                'aturan' => 'terbuka',
                'kapasitas' => null,
                'deskripsi' => 'Tanpa jenjang/tingkat: terbuka untuk jalur belajar mandiri.',
            ],
            [
                'kode' => 'BING10SMK',
                'nama' => 'Bahasa Inggris Kelas 10 SMA',
                'mapel' => 'bahasa_inggris',
                'jalur' => 'formal', 'jenjang' => 'sma', 'tingkat' => 'kelas_10',
                'kapasitas' => 36,
                'deskripsi' => 'English for senior high school students grade 10.',
            ],
            [
                'kode' => 'FIS10SMA',
                'nama' => 'Fisika Kelas 10 SMA',
                'mapel' => 'fisika',
                'jalur' => 'formal', 'jenjang' => 'sma', 'tingkat' => 'kelas_10',
                'kapasitas' => 32,
                'deskripsi' => 'Dasar-dasar fisika untuk siswa SMA kelas 10.',
            ],
            [
                'kode' => 'PPKN7SMP',
                'nama' => 'PPKn Kelas 7 SMP',
                'mapel' => 'ppkn',
                'jalur' => 'formal', 'jenjang' => 'smp', 'tingkat' => 'kelas_7',
                'kapasitas' => 32,
                'deskripsi' => 'Pendidikan Pancasila dan Kewarganegaraan untuk SMP kelas 7.',
            ],
            [
                'kode' => 'SEJ10SMA',
                'nama' => 'Sejarah Kelas 10 SMA',
                'mapel' => 'sejarah',
                'jalur' => 'formal', 'jenjang' => 'sma', 'tingkat' => 'kelas_10',
                'kapasitas' => 36,
                'deskripsi' => 'Sejarah Indonesia dan dunia untuk SMA kelas 10.',
            ],
            [
                'kode' => 'GEO10SMA',
                'nama' => 'Geografi Kelas 10 SMA',
                'mapel' => 'geografi',
                'jalur' => 'formal', 'jenjang' => 'sma', 'tingkat' => 'kelas_10',
                'kapasitas' => 36,
                'deskripsi' => 'Geografi fisik dan wilayah untuk SMA kelas 10.',
            ],
            [
                'kode' => 'EKON10SMA',
                'nama' => 'Ekonomi Kelas 10 SMA',
                'mapel' => 'ekonomi',
                'jalur' => 'formal', 'jenjang' => 'sma', 'tingkat' => 'kelas_10',
                'kapasitas' => 36,
                'deskripsi' => 'Dasar-dasar ekonomi untuk SMA kelas 10.',
            ],
            [
                'kode' => 'SOS10SMA',
                'nama' => 'Sosiologi Kelas 10 SMA',
                'mapel' => 'sosiologi',
                'jalur' => 'formal', 'jenjang' => 'sma', 'tingkat' => 'kelas_10',
                'kapasitas' => 36,
                'deskripsi' => 'Sosiologi dan perubahan sosial untuk SMA kelas 10.',
            ],
            [
                'kode' => 'TKJ10SMK',
                'nama' => 'TKJ Kelas 10 SMK',
                'mapel' => 'tkj',
                'jalur' => 'formal', 'jenjang' => 'smk', 'tingkat' => 'kelas_10',
                'kapasitas' => 24,
                'deskripsi' => 'Dasar-dasar jaringan komputer dan telekomunikasi untuk SMK kelas 10.',
            ],
            [
                'kode' => 'SBUD7SMP',
                'nama' => 'Seni Budaya Kelas 7 SMP',
                'mapel' => 'seni_budaya',
                'jalur' => 'formal', 'jenjang' => 'smp', 'tingkat' => 'kelas_7',
                'kapasitas' => 32,
                'deskripsi' => 'Apresiasi, berkarya, dan apresiasi seni budaya Indonesia.',
            ],
            [
                'kode' => 'PJOK10SMA',
                'nama' => 'PJOK Kelas 10 SMA',
                'mapel' => 'pjok',
                'jalur' => 'formal', 'jenjang' => 'sma', 'tingkat' => 'kelas_10',
                'kapasitas' => 36,
                'deskripsi' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan untuk SMA kelas 10.',
            ],
            [
                'kode' => 'AGM10SMA',
                'nama' => 'Pendidikan Agama dan Budi Pekerti Kelas 10 SMA',
                'mapel' => 'agama_budi_pekerti',
                'jalur' => 'formal', 'jenjang' => 'sma', 'tingkat' => 'kelas_10',
                'kapasitas' => 36,
                'deskripsi' => 'Pembentukan karakter, akhlak, dan nilai keagamaan.',
            ],
            [
                'kode' => 'INF8SMPX',
                'nama' => 'Informatika Kelas 8 SMP',
                'mapel' => 'informatika',
                'jalur' => 'formal', 'jenjang' => 'smp', 'tingkat' => 'kelas_8',
                'kapasitas' => 32,
                'aturan' => 'terbuka',
                'deskripsi' => 'Berpikir komputasional, algoritma, dan pemrograman dasar.',
            ],
        ];
    }
}
