<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MataPelajaranSeeder extends Seeder
{
    /**
     * Master mata pelajaran + kategori kelompoknya. Idempoten: baris lama
     * diperbarui (termasuk kategori), baris baru disisipkan — aman dijalankan
     * berulang lewat `db:seed` maupun `migrate --seed`.
     */
    public function run(): void
    {
        foreach ($this->master() as $kategori => $daftar) {
            foreach ($daftar as $mapel) {
                DB::table('mata_pelajaran')->updateOrInsert(
                    ['kode' => $mapel['kode']],
                    [
                        'nama' => $mapel['nama'],
                        'kategori' => $kategori,
                        'deskripsi' => $mapel['deskripsi'] ?? null,
                        'aktif' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    private function master(): array
    {
        return [
            'Matematika & Ilmu Alam' => [
                ['kode' => 'matematika', 'nama' => 'Matematika'],
                ['kode' => 'fisika', 'nama' => 'Fisika'],
                ['kode' => 'kimia', 'nama' => 'Kimia'],
                ['kode' => 'biologi', 'nama' => 'Biologi'],
            ],
            'Bahasa & Komunikasi' => [
                ['kode' => 'bahasa_indonesia', 'nama' => 'Bahasa Indonesia'],
                ['kode' => 'bahasa_inggris', 'nama' => 'Bahasa Inggris'],
            ],
            'Ilmu Sosial & Humaniora' => [
                ['kode' => 'ppkn', 'nama' => 'PPKn'],
                ['kode' => 'sejarah', 'nama' => 'Sejarah'],
                ['kode' => 'geografi', 'nama' => 'Geografi'],
                ['kode' => 'ekonomi', 'nama' => 'Ekonomi'],
                ['kode' => 'sosiologi', 'nama' => 'Sosiologi'],
            ],
            'Teknologi & Vokasional' => [
                ['kode' => 'informatika', 'nama' => 'Informatika'],
                ['kode' => 'tkj', 'nama' => 'Teknik Komputer dan Jaringan'],
            ],
            'Seni & Jasmani' => [
                ['kode' => 'seni_budaya', 'nama' => 'Seni Budaya'],
                ['kode' => 'pjok', 'nama' => 'PJOK'],
            ],
            'Keagamaan & Budi Pekerti' => [
                ['kode' => 'agama_budi_pekerti', 'nama' => 'Pendidikan Agama dan Budi Pekerti'],
            ],
        ];
    }
}
