<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AktivitasLog;
use App\Models\Kehadiran;
use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\KuisHasil;
use App\Models\PengumpulanTugas;
use App\Models\Pertemuan;
use App\Models\PrediksiMendatang;
use App\Models\PrediksiRisikoPertemuan;
use App\Models\Tugas;
use App\Models\User;
use App\Services\AktivitasService;
use App\Services\DatasetLatihService;
use App\Services\PrediksiMendatangService;
use App\Services\PrediksiRisikoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Isi data latih model lewat alur yang bisa dilakukan user sungguhan: guru
 * membuat kelas, pertemuan, kuis, dan tugas; siswa mengerjakan kuis dan
 * mengumpulkan tugas (sebagian telat atau tidak dikumpulkan); guru menilai dan
 * mencatat kehadiran; siswa membuka pertemuan (timer durasi belajar).
 *
 * Baris `prediksi_risiko_pertemuan` dan `fitur_siswa_history` TIDAK ditulis
 * langsung - diturunkan `PrediksiRisikoService::jalankan()`, persis seperti
 * tombol "Jalankan Prediksi". Nilai kuis/tugas juga tidak lewat controller atau
 * service pengumpulan, karena jalur itu memicu AI inline (QUEUE_CONNECTION=sync).
 *
 * Ini data demo, bukan data uji otomatis. Hapus setelah selesai dengan
 * `php artisan latih:seed-data --purge`.
 */
class SeedDataLatihCommand extends Command
{
    public const KODE_KELAS = 'LATIH-ML-01';

    public const PREFIX_EMAIL = 'latih.ml.';

    public const JUMLAH_PERTEMUAN = 8;

    /**
     * Nilai dasar per siswa, sengaja berselang-seling supaya 30% siswa terakhir
     * (kelompok uji `splitGrouped`) memuat ketiga kategori risiko.
     */
    public const NILAI_DASAR = [85, 62, 46, 74, 56, 42, 90, 66, 80, 50, 94, 70];

    public const NAMA_SISWA = [
        'Ayu Lestari', 'Bagas Pratama', 'Citra Handayani',
        'Dimas Nugroho', 'Eka Putri', 'Fajar Ramadhan',
        'Gita Sari', 'Hendra Wijaya', 'Intan Permata',
        'Joko Santoso', 'Kartika Dewi', 'Lukman Hakim',
    ];

    public const TOPIK = [
        'Struktur Atom dan Sistem Periodik',
        'Ikatan Kimia dan Gaya Antarmolekul',
        'Stoikiometri dan Konsep Mol',
        'Larutan dan Konsentrasi',
        'Reaksi Asam Basa',
        'Termokimia',
        'Laju Reaksi',
        'Kesetimbangan Kimia',
    ];

    /** Selisih kecil per pertemuan supaya nilai tidak rata sempurna. */
    public const WOBBLE = [0, 2, -2, 1, -1, 3, -1, 0];

    protected $signature = 'latih:seed-data
                            {--purge : Hapus data latih, bukan membuat}
                            {--force : Boleh jalan di APP_ENV=production}
                            {--dry-run : Tampilkan rencana tanpa menyentuh database}
                            {--train : Sekalian jalankan ml:train, isi ramalan tiap siswa, dan tulis snapshot dataset latih}';

    protected $description = 'Buat (atau hapus) data latih model dari alur guru & siswa, lalu turunkan parameter risiko per pertemuan.';

    public function handle(): int
    {
        set_time_limit(0);

        if ($this->option('purge')) {
            return $this->purge();
        }

        $this->seed();

        return self::SUCCESS;
    }

    protected function seed(): void
    {
        if ($this->option('dry-run')) {
            $this->info('DRY-RUN: tidak ada perubahan.');
            $this->line('Akan dibuat: 1 kelas '.self::KODE_KELAS.', '.count(self::NAMA_SISWA).' siswa, '
                .self::JUMLAH_PERTEMUAN.' pertemuan, '.self::JUMLAH_PERTEMUAN.' kuis, '
                .(self::JUMLAH_PERTEMUAN * 2).' tugas, lalu parameter risiko dan fitur per pertemuan.');

            return;
        }

        if (! $this->bolehDiubah()) {
            return;
        }

        if ($this->adaDataLama()) {
            $this->line('Data latih lama ditemukan, dibersihkan dulu...');
            $this->hapusData();
        }

        $this->buatData();

        // Parameter risiko dan fitur per pertemuan diturunkan dari data di atas,
        // sama seperti tombol "Jalankan Prediksi" (tidak ditulis tangan).
        $laporan = app(PrediksiRisikoService::class)->jalankan(true);

        $this->newLine();
        $this->line('Ringkasan data latih:');
        $this->tampilkanRingkasan($this->ringkasan(), 'Tabel');

        $this->newLine();
        $this->line('Hasil turunan risiko di seluruh sekolah: lengkap='.$laporan['lengkap']
            .' tidak_lengkap='.$laporan['tidak_lengkap']
            .' tidak_valid='.$laporan['tidak_valid']);
        $this->line('Distribusi label risiko kelas latih:');
        $this->distribusiRisiko();

        if ($this->option('train')) {
            $this->latihModel();

            return;
        }

        $this->newLine();
        $this->info('Selesai. Langkah berikutnya: php artisan ml:train');
    }

    protected function buatData(): void
    {
        $guru = $this->guruLatih();
        $aktivitas = app(AktivitasService::class);
        $awal = now()->subWeeks(self::JUMLAH_PERTEMUAN + 1)->startOfWeek();

        DB::transaction(function () use ($guru, $aktivitas, $awal): void {
            $kelas = Kelas::create([
                'nama_kelas' => 'Kimia Dasar (Data Latih Model)',
                'kode_kelas' => self::KODE_KELAS,
                'deskripsi' => 'Kelas berisi aktivitas guru & siswa apa adanya, dipakai melatih model prediksi.',
                'kategori' => 'Kimia',
                'guru_id' => $guru->id,
            ]);

            $siswa = [];
            foreach (self::NAMA_SISWA as $i => $nama) {
                $u = User::create([
                    'name' => $nama,
                    'email' => self::PREFIX_EMAIL.($i + 1).'@simba.test',
                    'password' => Hash::make('password'),
                    'role' => 'pelajar',
                    'status' => 'verified',
                    'nisn' => '9990000'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                ]);

                $kelas->siswa()->attach($u->id, [
                    'status' => 'active',
                    'approved_by' => $guru->id,
                    'approved_at' => now(),
                ]);

                $siswa[] = $u;
            }

            $pertemuans = [];
            $kuisPer = [];
            $tugasPer = [];

            for ($k = 1; $k <= self::JUMLAH_PERTEMUAN; $k++) {
                $topik = self::TOPIK[$k - 1];
                $mulai = (clone $awal)->addWeeks($k - 1);
                $selesai = (clone $mulai)->addDays(4);
                $batas = $selesai->copy()->setTime(23, 59);

                $pertemuans[$k] = Pertemuan::create([
                    'kelas_id' => $kelas->id,
                    'nama_pertemuan' => $topik,
                    'urutan' => $k,
                    'deskripsi' => 'Pertemuan '.$k.' membahas '.$topik.'.',
                    'tanggal_mulai' => $mulai->toDateString(),
                    'tanggal_selesai' => $selesai->toDateString(),
                    'dibuka' => true,
                ]);

                $kuisPer[$k] = Kuis::create([
                    'judul' => 'Kuis '.$k.' - '.$topik,
                    'kelas_id' => $kelas->id,
                    'pertemuan_id' => $pertemuans[$k]->id,
                    'soal' => $this->soalPilihanGanda($topik, 3),
                    'durasi_menit' => 20,
                    'tanggal_mulai' => $mulai->copy()->setTime(7, 0),
                    'tanggal_selesai' => $batas,
                    'is_aktif' => false,
                ]);

                $tugasPer[$k] = [];
                foreach (['A', 'B'] as $huruf) {
                    $tugasPer[$k][] = Tugas::create([
                        'judul' => 'Tugas '.$k.$huruf.' - '.$topik,
                        'deskripsi' => 'Latihan soal '.$topik.'.',
                        'kelas_id' => $kelas->id,
                        'guru_id' => $guru->id,
                        'pertemuan_id' => $pertemuans[$k]->id,
                        'tipe' => 'pilihan_ganda',
                        'soal' => $this->soalPilihanGanda($topik, 3),
                        'deadline' => $batas,
                    ]);
                }
            }

            foreach ($siswa as $i => $u) {
                $base = self::NILAI_DASAR[$i];
                $profil = $this->profilUntuk($base);
                $tren = $this->trenUntuk($base);

                foreach ($pertemuans as $k => $p) {
                    $tengah = $base + $tren * ($k - 1) + self::WOBBLE[$k - 1];

                    KuisHasil::create([
                        'kuis_id' => $kuisPer[$k]->id,
                        'siswa_id' => $u->id,
                        'skor' => $this->batasNilai($tengah),
                        'jawaban_siswa' => [0, 1, 2],
                    ]);

                    [$tugasA, $tugasB] = $tugasPer[$k];

                    PengumpulanTugas::create([
                        'tugas_id' => $tugasA->id,
                        'siswa_id' => $u->id,
                        'jawaban' => [0, 1],
                        'nilai' => $this->batasNilai($tengah),
                        'terlambat' => $base <= 71,
                    ]);

                    // Siswa berisiko tinggi melewatkan tugas B pada pertemuan ganjil.
                    if ($base >= 53 || $k % 2 === 0) {
                        PengumpulanTugas::create([
                            'tugas_id' => $tugasB->id,
                            'siswa_id' => $u->id,
                            'jawaban' => [1, 2],
                            'nilai' => $this->batasNilai($tengah - 1),
                            'terlambat' => $base <= 52,
                        ]);
                    }

                    Kehadiran::create([
                        'siswa_id' => $u->id,
                        'pertemuan_id' => $p->id,
                        'status' => $this->statusHadir($profil, $k),
                        'dicatat_oleh' => $guru->id,
                    ]);

                    // Siswa berisiko tinggi kadang tidak membuka pertemuannya.
                    $buka = $profil !== 'berisiko_tinggi'
                        || $k % 3 !== 0
                        || $k === self::JUMLAH_PERTEMUAN;

                    if ($buka) {
                        $log = $aktivitas->catat($u, 'lihat_pertemuan', $p, $kelas, []);
                        $aktivitas->catatDurasi($log, $this->durasiDetik($profil, $k));
                    }
                }
            }
        });
    }

    protected function guruLatih(): User
    {
        $guru = User::where('role', 'pengajar')->orderBy('id')->first();

        if ($guru !== null) {
            return $guru;
        }

        $this->warn('Belum ada pengajar, dibuat satu akun guru khusus.');

        return User::create([
            'name' => 'Guru Latih Model',
            'email' => 'guru.latih.ml@simba.test',
            'password' => Hash::make('password'),
            'role' => 'pengajar',
            'status' => 'verified',
            'nip' => '9990000001',
        ]);
    }

    protected function profilUntuk(int $base): string
    {
        return match (true) {
            $base >= 72 => 'aman',
            $base >= 53 => 'perlu_pantau',
            default => 'berisiko_tinggi',
        };
    }

    /** Siswa yang aman cenderung membaik, yang berisiko menurun. */
    protected function trenUntuk(int $base): float
    {
        return match (true) {
            $base >= 72 => 0.75,
            $base >= 53 => -0.5,
            default => -0.75,
        };
    }

    protected function statusHadir(string $profil, int $k): string
    {
        return match ($profil) {
            'aman' => 'hadir',
            'perlu_pantau' => $k === 3 ? 'izin' : 'hadir',
            default => $k % 3 === 0 ? 'alpha' : ($k % 3 === 1 ? 'hadir' : 'izin'),
        };
    }

    protected function durasiDetik(string $profil, int $k): int
    {
        return match ($profil) {
            'aman' => 1800 + $k * 120,
            'perlu_pantau' => 900 + $k * 60,
            default => 300 + $k * 45,
        };
    }

    protected function batasNilai(float $nilai): int
    {
        return (int) max(0, min(100, round($nilai)));
    }

    /**
     * @return array<int, array{pertanyaan: string, pilihan: array<int, string>, jawaban: int, bloom: string}>
     */
    protected function soalPilihanGanda(string $topik, int $jumlah): array
    {
        $out = [];
        for ($n = 1; $n <= $jumlah; $n++) {
            $out[] = [
                'pertanyaan' => 'Soal '.$n.' tentang '.$topik.': pilih jawaban yang paling tepat.',
                'pilihan' => [
                    'Pernyataan A untuk soal '.$n,
                    'Pernyataan B untuk soal '.$n,
                    'Pernyataan C untuk soal '.$n,
                    'Pernyataan D untuk soal '.$n,
                ],
                'jawaban' => ($n - 1) % 4,
                'bloom' => ['C1', 'C2', 'C3'][($n - 1) % 3],
            ];
        }

        return $out;
    }

    protected function latihModel(): void
    {
        $this->newLine();
        $this->info('Menjalankan ml:train...');
        $this->call('ml:train');

        $this->newLine();
        $this->info('Mengisi ramalan tiap siswa (setara guru membuka halaman prediksi)...');

        $mendatang = app(PrediksiMendatangService::class);
        $berhasil = 0;
        foreach ($this->siswaIds() as $id) {
            $hasil = $mendatang->predict($id);
            if (! isset($hasil['error'])) {
                $berhasil++;
            }
        }

        $dilengkapi = $mendatang->sinkronkanNilaiAktual();

        $this->table(['Hasil', 'Jumlah'], [
            ['Ramalan per siswa', $berhasil],
            ['Nilai aktual disinkronkan', $dilengkapi],
            ['prediksi_mendatang', PrediksiMendatang::count()],
        ]);

        // Snapshot dataset tidak hidup di database: `--purge` tidak menghapusnya,
        // jadi model masih bisa dilatih ulang setelah database dibersihkan.
        $dataset = app(DatasetLatihService::class)->export(DatasetLatihService::PATH, 'latih:seed-data');
        $ringkasan = $dataset['ringkasan'] ?? [];

        $this->info('Snapshot dataset latih ditulis: '.DatasetLatihService::PATH);
        $this->line(sprintf(
            '  RF %d baris (%d siswa), SVR %d pasangan (%d siswa). Label: %s.',
            $ringkasan['rf_samples'] ?? 0,
            $ringkasan['rf_siswa_unik'] ?? 0,
            $ringkasan['svr_samples'] ?? 0,
            $ringkasan['svr_siswa_unik'] ?? 0,
            json_encode($ringkasan['distribusi_label'] ?? []),
        ));
        $this->line('  Latih ulang tanpa data user: php artisan ml:train --dataset='.DatasetLatihService::PATH);
    }

    protected function purge(): int
    {
        if (! $this->adaDataLama()) {
            $this->info('Tidak ada data latih. Tidak ada yang perlu dihapus.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info('DRY-RUN: tidak ada perubahan.');
            $this->line('Akan dihapus:');
            $this->tampilkanRingkasan($this->ringkasan(), 'Tabel');

            return self::SUCCESS;
        }

        if (! $this->bolehDiubah()) {
            return self::SUCCESS;
        }

        $this->hapusData();

        $this->newLine();
        $this->info('Data latih berhasil dihapus.');

        return self::SUCCESS;
    }

    protected function adaDataLama(): bool
    {
        return Kelas::where('kode_kelas', self::KODE_KELAS)->exists()
            || User::where('email', 'like', self::PREFIX_EMAIL.'%')->exists();
    }

    /** Pengaman: command ini menulis banyak baris. */
    protected function bolehDiubah(): bool
    {
        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Tolak dijalankan di APP_ENV=production. Pakai --force kalau memang disengaja.');

            return false;
        }

        return true;
    }

    protected function hapusData(): void
    {
        $siswaIds = $this->siswaIds();
        $kelas = Kelas::where('kode_kelas', self::KODE_KELAS)->first();

        DB::transaction(function () use ($siswaIds, $kelas): void {
            if ($siswaIds !== []) {
                foreach ([
                    'prediksi_risiko_pertemuan',
                    'prediksi_mendatang',
                    'prediksi_ai',
                    'fitur_siswa_history',
                    'aktivitas_log',
                    'kehadiran',
                    'kuis_hasil',
                    'pengumpulan_tugas',
                ] as $tabel) {
                    DB::table($tabel)->whereIn('siswa_id', $siswaIds)->delete();
                }
            }

            // Menghapus kelas ikut menghapus pertemuan, kuis, dan tugas beserta
            // turunannya lewat foreign key cascade.
            if ($kelas !== null) {
                Kelas::whereKey($kelas->id)->delete();
            }

            if ($siswaIds !== []) {
                User::whereIn('id', $siswaIds)->delete();
            }
        });
    }

    /**
     * Siswa milik kelas latih: dari email berprefix, ditambah isi pivot kelas
     * supaya baris sisa tetap terbersihkan walau emailnya sudah berubah.
     *
     * @return array<int, int>
     */
    protected function siswaIds(): array
    {
        $ids = User::where('email', 'like', self::PREFIX_EMAIL.'%')->pluck('id')->all();

        $kelas = Kelas::where('kode_kelas', self::KODE_KELAS)->first();
        if ($kelas !== null) {
            $ids = array_merge($ids, DB::table('kelas_siswa')->where('kelas_id', $kelas->id)->pluck('siswa_id')->all());
        }

        return array_values(array_unique($ids));
    }

    protected function distribusiRisiko(): void
    {
        $kelas = Kelas::where('kode_kelas', self::KODE_KELAS)->first();
        if ($kelas === null) {
            return;
        }

        $baris = PrediksiRisikoPertemuan::where('kelas_id', $kelas->id)
            ->selectRaw('kategori_risiko, status_data, count(*) as jumlah')
            ->groupBy('kategori_risiko', 'status_data')
            ->orderBy('kategori_risiko')
            ->get()
            ->map(fn ($r) => [$r->kategori_risiko ?? '(null)', $r->status_data, $r->jumlah])
            ->all();

        $this->table(['Kategori risiko', 'Status data', 'Jumlah'], $baris);
    }

    /**
     * @return array<string, int>
     */
    protected function ringkasan(): array
    {
        $siswaIds = $this->siswaIds();
        $kelas = Kelas::where('kode_kelas', self::KODE_KELAS)->first();
        $kelasIds = $kelas === null ? [] : [$kelas->id];
        $pertemuanIds = $kelasIds === [] ? [] : Pertemuan::whereIn('kelas_id', $kelasIds)->pluck('id')->all();
        $kuisIds = $kelasIds === [] ? [] : Kuis::whereIn('kelas_id', $kelasIds)->pluck('id')->all();
        $tugasIds = $kelasIds === [] ? [] : Tugas::whereIn('kelas_id', $kelasIds)->pluck('id')->all();

        return [
            'Kelas' => count($kelasIds),
            'Siswa' => count($siswaIds),
            'Pertemuan' => count($pertemuanIds),
            'Kuis' => count($kuisIds),
            'Tugas' => count($tugasIds),
            'Kuis hasil' => $kuisIds === [] ? 0 : KuisHasil::whereIn('kuis_id', $kuisIds)->count(),
            'Pengumpulan tugas' => $tugasIds === [] ? 0 : PengumpulanTugas::whereIn('tugas_id', $tugasIds)->count(),
            'Kehadiran' => $siswaIds === [] ? 0 : Kehadiran::whereIn('siswa_id', $siswaIds)->count(),
            'Aktivitas log' => $siswaIds === [] ? 0 : AktivitasLog::whereIn('siswa_id', $siswaIds)->count(),
            'Fitur siswa history' => $pertemuanIds === [] ? 0 : DB::table('fitur_siswa_history')->whereIn('pertemuan_id', $pertemuanIds)->count(),
            'Prediksi risiko pertemuan' => $kelasIds === [] ? 0 : PrediksiRisikoPertemuan::whereIn('kelas_id', $kelasIds)->count(),
            'Prediksi mendatang' => $siswaIds === [] ? 0 : PrediksiMendatang::whereIn('siswa_id', $siswaIds)->count(),
        ];
    }

    /**
     * @param  array<string, int>  $ringkasan
     */
    protected function tampilkanRingkasan(array $ringkasan, string $judul): void
    {
        $baris = [];
        foreach ($ringkasan as $tabel => $jumlah) {
            $baris[] = [$tabel, $jumlah];
        }

        $this->table([$judul, 'Jumlah'], $baris);
    }
}
