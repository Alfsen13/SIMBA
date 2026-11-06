<?php

namespace App\Services;

use App\Models\AktivitasLog;
use App\Models\FiturSiswaHistory;
use App\Models\Kehadiran;
use App\Models\Kuis;
use App\Models\KuisHasil;
use App\Models\PengumpulanTugas;
use App\Models\Pertemuan;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Support\Collection;

class FiturPertemuanService
{
    public const FEATURES = [
        'frekuensi_akses_pertemuan',
        'rata_durasi_belajar',
        'persen_tugas_terkumpul',
        'tren_nilai',
        'persen_kehadiran',
        'rata_nilai',
        'jumlah_pertemuan_tidak_aktif',
    ];

    public const FEATURE_LABELS = [
        'frekuensi_akses_pertemuan' => 'Frekuensi Akses Pertemuan',
        'rata_durasi_belajar' => 'Rata-rata Durasi Belajar',
        'persen_tugas_terkumpul' => 'Persen Tugas Terkumpul',
        'tren_nilai' => 'Tren Nilai',
        'persen_kehadiran' => 'Persen Kehadiran',
        'rata_nilai' => 'Rata Nilai',
        'jumlah_pertemuan_tidak_aktif' => 'Pertemuan Tidak Aktif',
    ];

    /**
     * Fitur yang sudah TIDAK dipakai lagi oleh model regresi "nilai pertemuan
     * berikutnya" maupun tampilan prediksi (grafik indikator + kartu ringkasan).
     * Frekuensi akses hanya mencacah jumlah klik sehingga satuannya tidak
     * sebanding indikator lain. Kolomnya tetap ada di `FEATURES` /
     * `fitur_siswa_history` supaya data historis tidak hilang.
     */
    public const FEATURE_DIABAIKAN = [
        'frekuensi_akses_pertemuan',
    ];

    /**
     * Urutan fitur vektor yang benar-benar dipakai model & ditampilkan di UI
     * prediksi. Turunan dari `FEATURES` supaya urutan tetap satu sumber.
     *
     * @return array<int, string>
     */
    public static function fiturModel(): array
    {
        return array_values(array_diff(self::FEATURES, self::FEATURE_DIABAIKAN));
    }

    public function hitung(int $siswaId, Pertemuan $pertemuan): array
    {
        $tugasIds = Tugas::where('pertemuan_id', $pertemuan->id)->pluck('id');

        $logs = AktivitasLog::where('siswa_id', $siswaId)
            ->where('pertemuan_id', $pertemuan->id)
            ->get(['metadata']);

        $frekuensi = $logs->count();

        $durasi = $logs
            ->map(fn (AktivitasLog $l) => (float) ($l->metadata['durasi_menit'] ?? 0))
            ->filter(fn (float $d) => $d > 0);
        $rataDurasi = $durasi->isNotEmpty() ? round((float) $durasi->avg(), 1) : null;

        $persenTugas = null;
        if ($tugasIds->isNotEmpty()) {
            $terkumpul = PengumpulanTugas::where('siswa_id', $siswaId)
                ->whereIn('tugas_id', $tugasIds)
                ->count();
            $persenTugas = round($terkumpul / $tugasIds->count() * 100, 1);
        }

        $rataNilai = $this->nilaiPertemuan($siswaId, $pertemuan);

        $tren = null;
        $sebelumnya = $this->pertemuanSebelumnya($pertemuan);
        if ($sebelumnya !== null) {
            $nilaiSebelumnya = $this->nilaiPertemuan($siswaId, $sebelumnya);
            if ($rataNilai !== null && $nilaiSebelumnya !== null) {
                $tren = round($rataNilai - $nilaiSebelumnya, 1);
            }
        }

        $pertemuanSampai = $this->pertemuanSampai($pertemuan)->pluck('id');
        $statusKehadiran = Kehadiran::where('siswa_id', $siswaId)
            ->whereIn('pertemuan_id', $pertemuanSampai)
            ->pluck('status');
        $totalHadir = $statusKehadiran->count();
        $persenKehadiran = $totalHadir > 0
            ? round($statusKehadiran->filter(
                fn (string $s) => in_array($s, Kehadiran::STATUS_HADIR, true)
            )->count() / $totalHadir * 100, 1)
            : null;

        $diakses = AktivitasLog::where('siswa_id', $siswaId)
            ->whereIn('pertemuan_id', $pertemuanSampai)
            ->distinct()
            ->count('pertemuan_id');
        $tidakAktif = max(0, $pertemuanSampai->count() - $diakses);

        return [
            'frekuensi_akses_pertemuan' => $frekuensi,
            'rata_durasi_belajar' => $rataDurasi,
            'persen_tugas_terkumpul' => $persenTugas,
            'tren_nilai' => $tren,
            'persen_kehadiran' => $persenKehadiran,
            'rata_nilai' => $rataNilai,
            'jumlah_pertemuan_tidak_aktif' => $tidakAktif,
        ];
    }

    public function simpan(int $siswaId, Pertemuan $pertemuan): ?FiturSiswaHistory
    {
        if (! $pertemuan->dibuka) {
            return null;
        }

        return FiturSiswaHistory::updateOrCreate(
            ['siswa_id' => $siswaId, 'pertemuan_id' => $pertemuan->id],
            [
                ...$this->hitung($siswaId, $pertemuan),

                'tanggal' => now()->startOfDay(),
            ]
        );
    }

    public function simpanUntukPertemuan(Pertemuan $pertemuan): int
    {
        $siswaIds = $this->siswaKelas($pertemuan->kelas_id);
        foreach ($siswaIds as $siswaId) {
            $this->simpan($siswaId, $pertemuan);
        }

        return $siswaIds->count();
    }

    public function backfill(?int $kelasId = null): int
    {
        $pertemuans = Pertemuan::query()
            ->dibuka()
            ->when($kelasId !== null, fn ($q) => $q->where('kelas_id', $kelasId))
            ->orderBy('kelas_id')
            ->orderBy('urutan')
            ->get();

        $disimpan = 0;
        foreach ($pertemuans->groupBy('kelas_id') as $idKelas => $daftar) {
            foreach ($this->siswaKelas((int) $idKelas)->chunk(200) as $siswaIds) {
                $disimpan += $this->simpanBatch($siswaIds->all(), $daftar);
            }
        }

        return $disimpan;
    }

    /**
     * Hitung semua fitur untuk satu batch siswa dengan pembacaan data per
     * kelas, bukan query terpisah untuk setiap pasangan siswa-pertemuan.
     *
     * @param  array<int, int>  $siswaIds
     */
    private function simpanBatch(array $siswaIds, Collection $pertemuans): int
    {
        $pertemuanIds = $pertemuans->pluck('id')->all();
        $tugas = Tugas::whereIn('pertemuan_id', $pertemuanIds)->get(['id', 'pertemuan_id']);
        $kuis = Kuis::whereIn('pertemuan_id', $pertemuanIds)->get(['id', 'pertemuan_id']);
        $tugasPerPertemuan = $tugas->groupBy('pertemuan_id');
        $tugasKePertemuan = $tugas->pluck('pertemuan_id', 'id');
        $kuisKePertemuan = $kuis->pluck('pertemuan_id', 'id');
        $tugasIds = $tugas->pluck('id')->all();
        $kuisIds = $kuis->pluck('id')->all();

        $aktivitas = [];
        foreach (AktivitasLog::whereIn('siswa_id', $siswaIds)
            ->whereIn('pertemuan_id', $pertemuanIds)
            ->get(['siswa_id', 'pertemuan_id', 'metadata']) as $log) {
            $siswaId = (int) $log->siswa_id;
            $pertemuanId = (int) $log->pertemuan_id;
            $aktivitas[$siswaId][$pertemuanId] ??= ['jumlah' => 0, 'durasi' => []];
            $aktivitas[$siswaId][$pertemuanId]['jumlah']++;

            $durasi = (float) ($log->metadata['durasi_menit'] ?? 0);
            if ($durasi > 0) {
                $aktivitas[$siswaId][$pertemuanId]['durasi'][] = $durasi;
            }
        }

        $nilai = [];
        if ($kuisIds !== []) {
            foreach (KuisHasil::whereIn('siswa_id', $siswaIds)
                ->whereIn('kuis_id', $kuisIds)
                ->get(['siswa_id', 'kuis_id', 'skor']) as $hasil) {
                $siswaId = (int) $hasil->siswa_id;
                $pertemuanId = (int) $kuisKePertemuan[$hasil->kuis_id];
                $nilai[$siswaId][$pertemuanId] ??= ['jumlah' => 0, 'total' => 0.0];
                $nilai[$siswaId][$pertemuanId]['jumlah']++;
                $nilai[$siswaId][$pertemuanId]['total'] += (float) $hasil->skor;
            }
        }

        $pengumpulan = [];
        if ($tugasIds !== []) {
            foreach (PengumpulanTugas::whereIn('siswa_id', $siswaIds)
                ->whereIn('tugas_id', $tugasIds)
                ->get(['siswa_id', 'tugas_id', 'nilai']) as $baris) {
                $siswaId = (int) $baris->siswa_id;
                $pertemuanId = (int) $tugasKePertemuan[$baris->tugas_id];
                $pengumpulan[$siswaId][$pertemuanId] = ($pengumpulan[$siswaId][$pertemuanId] ?? 0) + 1;

                if ($baris->nilai !== null) {
                    $nilai[$siswaId][$pertemuanId] ??= ['jumlah' => 0, 'total' => 0.0];
                    $nilai[$siswaId][$pertemuanId]['jumlah']++;
                    $nilai[$siswaId][$pertemuanId]['total'] += (float) $baris->nilai;
                }
            }
        }

        $kehadiran = [];
        foreach (Kehadiran::whereIn('siswa_id', $siswaIds)
            ->whereIn('pertemuan_id', $pertemuanIds)
            ->get(['siswa_id', 'pertemuan_id', 'status']) as $baris) {
            $kehadiran[(int) $baris->siswa_id][(int) $baris->pertemuan_id] = $baris->status;
        }

        $now = now();
        $rows = [];
        foreach ($siswaIds as $siswaId) {
            $siswaId = (int) $siswaId;
            $jumlahAksesSampai = 0;
            $jumlahPertemuanSampai = 0;
            $jumlahKehadiran = 0;
            $jumlahHadir = 0;
            $nilaiSebelumnya = null;

            foreach ($pertemuans as $pertemuan) {
                $pertemuanId = (int) $pertemuan->id;
                $jumlahPertemuanSampai++;
                $aktivitasPertemuan = $aktivitas[$siswaId][$pertemuanId] ?? null;
                $jumlahAktivitas = $aktivitasPertemuan['jumlah'] ?? 0;
                $jumlahAksesSampai += $jumlahAktivitas > 0 ? 1 : 0;

                $statusKehadiran = $kehadiran[$siswaId][$pertemuanId] ?? null;
                if ($statusKehadiran !== null) {
                    $jumlahKehadiran++;
                    $jumlahHadir += in_array($statusKehadiran, Kehadiran::STATUS_HADIR, true) ? 1 : 0;
                }

                $nilaiPertemuan = $nilai[$siswaId][$pertemuanId] ?? null;
                $rataNilai = $nilaiPertemuan && $nilaiPertemuan['jumlah'] > 0
                    ? round($nilaiPertemuan['total'] / $nilaiPertemuan['jumlah'], 1)
                    : null;
                $tren = $rataNilai !== null && $nilaiSebelumnya !== null
                    ? round($rataNilai - $nilaiSebelumnya, 1)
                    : null;

                $jumlahTugas = $tugasPerPertemuan->get($pertemuanId, collect())->count();
                $terkumpul = $pengumpulan[$siswaId][$pertemuanId] ?? 0;
                $durasi = $aktivitasPertemuan['durasi'] ?? [];
                $rows[] = [
                    'siswa_id' => $siswaId,
                    'pertemuan_id' => $pertemuanId,
                    'tanggal' => $now->toDateString(),
                    'frekuensi_akses_pertemuan' => $jumlahAktivitas,
                    'rata_durasi_belajar' => $durasi === [] ? null : round(array_sum($durasi) / count($durasi), 1),
                    'persen_tugas_terkumpul' => $jumlahTugas > 0 ? round($terkumpul / $jumlahTugas * 100, 1) : null,
                    'tren_nilai' => $tren,
                    'persen_kehadiran' => $jumlahKehadiran > 0 ? round($jumlahHadir / $jumlahKehadiran * 100, 1) : null,
                    'rata_nilai' => $rataNilai,
                    'jumlah_pertemuan_tidak_aktif' => $jumlahPertemuanSampai - $jumlahAksesSampai,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $nilaiSebelumnya = $rataNilai;
            }
        }

        foreach (array_chunk($rows, 500) as $batch) {
            FiturSiswaHistory::upsert(
                $batch,
                ['siswa_id', 'pertemuan_id'],
                [
                    'tanggal',
                    'frekuensi_akses_pertemuan',
                    'rata_durasi_belajar',
                    'persen_tugas_terkumpul',
                    'tren_nilai',
                    'persen_kehadiran',
                    'rata_nilai',
                    'jumlah_pertemuan_tidak_aktif',
                    'updated_at',
                ]
            );
        }

        return count($rows);
    }

    public function nilaiPertemuan(int $siswaId, Pertemuan $pertemuan): ?float
    {

        if (! $pertemuan->dibuka) {
            return null;
        }

        $kuisIds = Kuis::where('pertemuan_id', $pertemuan->id)->pluck('id');
        $tugasIds = Tugas::where('pertemuan_id', $pertemuan->id)->pluck('id');

        $skorKuis = KuisHasil::where('siswa_id', $siswaId)
            ->whereIn('kuis_id', $kuisIds)
            ->pluck('skor');

        $nilaiTugas = PengumpulanTugas::where('siswa_id', $siswaId)
            ->whereIn('tugas_id', $tugasIds)
            ->whereNotNull('nilai')
            ->pluck('nilai');

        $semua = $skorKuis->concat($nilaiTugas);

        return $semua->isNotEmpty() ? round((float) $semua->avg(), 1) : null;
    }

    public function targetBerikutnya(int $siswaId, Pertemuan $pertemuan): ?float
    {
        $berikutnya = $this->pertemuanBerikutnya($pertemuan);

        return $berikutnya === null ? null : $this->nilaiPertemuan($siswaId, $berikutnya);
    }

    public function pertemuanBerikutnya(Pertemuan $pertemuan): ?Pertemuan
    {
        return Pertemuan::where('kelas_id', $pertemuan->kelas_id)
            ->dibuka()
            ->where('urutan', '>', $pertemuan->urutan)
            ->orderBy('urutan')
            ->first();
    }

    public function pertemuanSebelumnya(Pertemuan $pertemuan): ?Pertemuan
    {
        return Pertemuan::where('kelas_id', $pertemuan->kelas_id)
            ->dibuka()
            ->where('urutan', '<', $pertemuan->urutan)
            ->orderByDesc('urutan')
            ->first();
    }

    public function pertemuanSampai(Pertemuan $pertemuan): Collection
    {
        return Pertemuan::where('kelas_id', $pertemuan->kelas_id)
            ->dibuka()
            ->where('urutan', '<=', $pertemuan->urutan)
            ->orderBy('urutan')
            ->get();
    }

    protected function siswaKelas(int $kelasId): Collection
    {
        return User::where('role', 'pelajar')
            ->whereHas('kelasDiikuti', fn ($q) => $q->where('kelas_id', $kelasId))
            ->pluck('id');
    }
}
