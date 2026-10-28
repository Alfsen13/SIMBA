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
            $siswaIds = $this->siswaKelas((int) $idKelas);
            foreach ($daftar as $pertemuan) {
                foreach ($siswaIds as $siswaId) {
                    $this->simpan($siswaId, $pertemuan);
                    $disimpan++;
                }
            }
        }

        return $disimpan;
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

