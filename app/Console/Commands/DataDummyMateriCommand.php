<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\KuisHasil;
use App\Models\Materi;
use App\Models\PengumpulanTugas;
use App\Models\Pertemuan;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

 
class DataDummyMateriCommand extends Command
{
     
    public const EMAIL_DUMMY = 'dummy.materi@simba.test';

     
    public const MATERI_PIVOT = 'Ikatan Kimia';

    protected $signature = 'tes:data-dummy
                            {--purge : Hapus semua data dummy, bukan membuat}
                            {--force : Boleh jalan di APP_ENV=production}
                            {--dry-run : Tampilkan rencana tanpa menyentuh database}';

    protected $description = 'Buat atau hapus data dummy untuk menguji atribusi topik lemah per materi.';

    public function handle(): int
    {
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Data dummy tidak boleh disentuh di APP_ENV=production. Tambahkan --force bila benar-benar disengaja.');

            return self::FAILURE;
        }

        return $this->option('purge') ? $this->purge() : $this->seed();
    }

     
    protected function seed(): int
    {
        if ($this->option('dry-run')) {
            $this->info('DRY RUN: tidak ada perubahan. Rencana: buat 1 guru dummy, 1 kelas, 3 pertemuan, 5 materi, 4 kuis, 3 tugas, plus materi pendukung di tabel pivot kuis_materi & tugas_materi.');

            return self::SUCCESS;
        }

        $ada = User::where('email', self::EMAIL_DUMMY)->exists();

        if ($ada) {
            $this->line('Data dummy lama ditemukan, dibersihkan dulu...');
            $this->hapusBarisDummy();
        }

        DB::transaction(function (): void {
            $guru = User::create([
                'name' => 'Guru Dummy Materi',
                'email' => self::EMAIL_DUMMY,
                'password' => Hash::make('dummy12345'),
                'role' => 'pengajar',
                'status' => 'verified',
            ]);

            $kelas = Kelas::create([
                'nama_kelas' => '[DUMMY] Kelas Uji Materi',
                'kode_kelas' => 'DMY-001',
                'deskripsi' => 'Data dummy untuk tes atribusi topik lemah. Hapus dengan tes:data-dummy --purge.',
                'guru_id' => $guru->id,
            ]);

            $paket = [
                ['nama' => 'Pertemuan 1 Sistem Pernapasan', 'materi' => ['Sistem Pernapasan Manusia', 'Sistem Digesti Hewan']],
                ['nama' => 'Pertemuan 2 Ekologi', 'materi' => ['Ekosistem Darat', 'Ekosistem Perairan']],
                ['nama' => 'Pertemuan 3 Kimia', 'materi' => [self::MATERI_PIVOT]],
            ];

            foreach ($paket as $urutan => $isi) {
                $pertemuan = Pertemuan::create([
                    'kelas_id' => $kelas->id,
                    'nama_pertemuan' => $isi['nama'],
                    'urutan' => $urutan + 1,
                    'dibuka' => true,
                ]);

                foreach ($isi['materi'] as $judul) {
                    Materi::create([
                        'judul' => $judul,
                        'pertemuan_id' => $pertemuan->id,
                        'kelas_id' => $kelas->id,
                        'guru_id' => $guru->id,
                    ]);
                }
            }

            $this->buatNilaiDummy($guru, $kelas);
        });

        $guruId = User::where('email', self::EMAIL_DUMMY)->value('id');
        $kelas = Kelas::where('guru_id', $guruId)->first();

        $this->newLine();
        $this->info('Data dummy siap:');
        $this->table(
            ['Entitas', 'Jumlah'],
            [
                ['Guru dummy', 1],
                ['Kelas', 1],
                ['Pertemuan', Pertemuan::where('kelas_id', $kelas->id)->count()],
                ['Materi', Materi::where('kelas_id', $kelas->id)->count()],
                ['Kuis', Kuis::where('kelas_id', $kelas->id)->count()],
                ['Tugas', Tugas::where('kelas_id', $kelas->id)->count()],
                ['Pivot kuis_materi', DB::table('kuis_materi')->whereIn('kuis_id', Kuis::where('kelas_id', $kelas->id)->pluck('id'))->count()],
                ['Pivot tugas_materi', DB::table('tugas_materi')->whereIn('tugas_id', Tugas::where('kelas_id', $kelas->id)->pluck('id'))->count()],
            ]
        );

        $this->newLine();
        $this->comment('Topik lemahnya harus (dari terendah):');
        $this->line('  Sistem Pernapasan Manusia (rata-rata 33), Ekosistem Darat (rata-rata 46),');
        $this->line('  '.self::MATERI_PIVOT.' (rata-rata 67.5) <- hanya muncul lewat pivot');
        $this->newLine();
        $this->comment('Akun guru dummy: '.self::EMAIL_DUMMY);
        $this->warn('Ingat: data ini hanya untuk tes. Hapus dengan:');
        $this->line('  php artisan tes:data-dummy --purge');

        return self::SUCCESS;
    }

     
    protected function buatNilaiDummy(User $guru, $kelas): void
    {
        $skor = [
            'Sistem Pernapasan Manusia' => 32,
            'Sistem Digesti Hewan' => 88,
            'Ekosistem Darat' => 45,
            'Ekosistem Perairan' => 91,
        ];

        $materiPivot = Materi::where('kelas_id', $kelas->id)->where('judul', self::MATERI_PIVOT)->first();

        foreach ($skor as $judul => $nilai) {
            $materi = Materi::where('kelas_id', $kelas->id)->where('judul', $judul)->first();

            if (! $materi) {
                continue;
            }

            $pertemuanId = $materi->pertemuan_id;


            $kuis = Kuis::create([
                'judul' => 'Kuis Dummy '.$judul,
                'kelas_id' => $kelas->id,
                'pertemuan_id' => $pertemuanId,
                'materi_id' => $materi->id,
                'soal' => [[
                    'pertanyaan' => 'Soal dummy untuk '.$judul.'.',
                    'pilihan' => ['Benar', 'Salah'],
                    'jawaban' => 0,
                ]],
                'durasi_menit' => 30,
                'is_aktif' => true,
            ]);

            KuisHasil::create([
                'kuis_id' => $kuis->id,
                'siswa_id' => $guru->id,
                'skor' => $nilai,
                'jawaban_siswa' => [],
            ]);


            if ($judul === 'Sistem Digesti Hewan') {
                $kuis->materiPendukung()->sync([$materiPivot->id]);
            }

            if ($nilai < 90) {
                $tugas = Tugas::create([
                    'judul' => 'Tugas Dummy '.$judul,
                    'kelas_id' => $kelas->id,
                    'guru_id' => $guru->id,
                    'pertemuan_id' => $pertemuanId,
                    'materi_id' => $materi->id,
                    'tipe' => 'upload',
                    'soal' => null,
                ]);

                PengumpulanTugas::create([
                    'tugas_id' => $tugas->id,
                    'siswa_id' => $guru->id,
                    'nilai' => $nilai + 2,
                    'terlambat' => false,
                ]);

                if ($judul === 'Ekosistem Darat') {
                    $tugas->materiPendukung()->sync([$materiPivot->id]);
                }
            }
        }
    }

     
    protected function purge(): int
    {
        $ids = $this->kumpulkanIdDummy();

        if ($ids['guru']->isEmpty()) {
            $this->info('Tidak ada data dummy. Database sudah bersih.');

            return self::SUCCESS;
        }

        $ringkasan = [
            'kuis_hasil' => $ids['kuis']->isEmpty() ? 0 : KuisHasil::whereIn('kuis_id', $ids['kuis'])->count(),
            'pengumpulan_tugas' => $ids['tugas']->isEmpty() ? 0 : PengumpulanTugas::whereIn('tugas_id', $ids['tugas'])->count(),
            'kuis_materi (pivot)' => $ids['kuis']->isEmpty() ? 0 : DB::table('kuis_materi')->whereIn('kuis_id', $ids['kuis'])->count(),
            'tugas_materi (pivot)' => $ids['tugas']->isEmpty() ? 0 : DB::table('tugas_materi')->whereIn('tugas_id', $ids['tugas'])->count(),
            'kuis' => $ids['kuis']->count(),
            'tugas' => $ids['tugas']->count(),
            'materi' => $ids['materi']->count(),
            'pertemuan' => $ids['pertemuan']->count(),
            'kelas' => $ids['kelas']->count(),
            'users' => $ids['guru']->count(),
        ];

        if ($this->option('dry-run')) {
            $this->info('DRY RUN: tidak ada perubahan.');

            return $this->tampilkanRingkasan($ringkasan, 'AKAN DIHAPUS');
        }

        $this->hapusBarisDummy();

        $this->newLine();
        $this->info('Data dummy berhasil dihapus.');

        return $this->tampilkanRingkasan($ringkasan, 'DIHAPUS');
    }

     
    protected function tampilkanRingkasan(array $ringkasan, string $judul): int
    {
        $baris = [];
        foreach ($ringkasan as $tabel => $jumlah) {
            $baris[] = [$tabel, $jumlah];
        }

        $this->table([$judul, 'Jumlah'], $baris);

        return self::SUCCESS;
    }

     
    protected function hapusBarisDummy(): void
    {
        $ids = $this->kumpulkanIdDummy();

        DB::transaction(function () use ($ids): void {
            if ($ids['kuis']->isNotEmpty()) {
                KuisHasil::whereIn('kuis_id', $ids['kuis'])->delete();
                DB::table('kuis_materi')->whereIn('kuis_id', $ids['kuis'])->delete();
            }

            if ($ids['tugas']->isNotEmpty()) {
                PengumpulanTugas::whereIn('tugas_id', $ids['tugas'])->delete();
                DB::table('tugas_materi')->whereIn('tugas_id', $ids['tugas'])->delete();
            }

            Kuis::whereIn('id', $ids['kuis'])->delete();
            Tugas::whereIn('id', $ids['tugas'])->delete();
            Materi::whereIn('id', $ids['materi'])->delete();
            Pertemuan::whereIn('id', $ids['pertemuan'])->delete();
            Kelas::whereIn('id', $ids['kelas'])->delete();
            User::whereIn('id', $ids['guru'])->delete();
        });
    }

     
    protected function kumpulkanIdDummy(): array
    {
        $guru = User::where('email', self::EMAIL_DUMMY)->pluck('id');
        $kelas = Kelas::whereIn('guru_id', $guru)->pluck('id');

        return [
            'guru' => $guru,
            'kelas' => $kelas,
            'pertemuan' => Pertemuan::whereIn('kelas_id', $kelas)->pluck('id'),
            'materi' => Materi::whereIn('kelas_id', $kelas)->pluck('id'),
            'kuis' => Kuis::whereIn('kelas_id', $kelas)->pluck('id'),
            'tugas' => Tugas::whereIn('kelas_id', $kelas)->pluck('id'),
        ];
    }
}

