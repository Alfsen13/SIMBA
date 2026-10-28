<?php

namespace App\Services;

use App\Models\Kuis;
use App\Models\KuisHasil;
use App\Models\PengumpulanTugas;
use App\Models\Pertemuan;
use App\Models\PrediksiAi;
use App\Models\PrediksiRisikoPertemuan;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Skor Penyimpangan dari Baseline (Baseline Deviation Scoring).
 *
 * Perhitungan deterministik (tanpa pelatihan ML): tiap pertemuan dihitung 4
 * parameter akademik siswa, lalu jaraknya terhadap profil ideal (baseline)
 * dirata-ratakan menjadi satu skor 0-100. Skor itu dipetakan ke kategori
 * risiko aman / perlu_pantau / berisiko_tinggi.
 *
 * Kategori dari sini adalah ACUAN. Model Random Forest
 * (`KlasifikasiRisikoService`) dilatih dari label ini dan menyimpan hasilnya di
 * kolom terpisah (`kategori_risiko_rf`) sebagai pembanding, bukan pengganti.
 *
 * Kalau salah satu parameter tidak tersedia (mis. siswa belum punya nilai
 * yang dinilai sama sekali), skor TIDAK dihitung dan status_data ditandai
 * `data_tidak_lengkap` - menghindari label "aman" palsu dari data kosong.
 * Parameter di luar rentang 0-100 ditandai `data_tidak_valid`, tidak
 * diam-diam di-clamp supaya seolah-olah valid.
 */
class PrediksiRisikoService
{
    /** Metode & versi konfigurasi yang ditulis ke tiap baris riwayat. */
    public const METODE = 'baseline_deviation_scoring';

    public const VERSI_KONFIGURASI = 'baseline_ideal_bobot_setara_v1';

    /** Rentang valid setiap parameter (skala 0-100). */
    public const RENTANG_MIN = 0.0;

    public const RENTANG_MAKS = 100.0;
    public const PARAMETERS = [
        'rata_nilai',
        'persen_terlambat',
        'persen_terlewat',
        'nilai_keseluruhan',
    ];

    /** Profil ideal tiap parameter (baseline). */
    public const BASELINE = [
        'rata_nilai' => 100.0,
        'persen_terlambat' => 0.0,
        'persen_terlewat' => 0.0,
        'nilai_keseluruhan' => 100.0,
    ];

    /** Bobot tiap parameter (sama rata: 0.25). */
    public const BOBOT = [
        'rata_nilai' => 0.25,
        'persen_terlambat' => 0.25,
        'persen_terlewat' => 0.25,
        'nilai_keseluruhan' => 0.25,
    ];

    public const STATUS_LENGKAP = 'lengkap';
    public const STATUS_TIDAK_LENGKAP = 'data_tidak_lengkap';
    public const STATUS_TIDAK_VALID = 'data_tidak_valid';

    public static function kategoriDariSkor(float $skor): string
    {
        $aman = (float) config('services.prediksi.ambang_aman', 20);
        $pantau = (float) config('services.prediksi.ambang_pantau', 40);

        if ($skor < $aman) {
            return 'aman';
        }

        return $skor < $pantau ? 'perlu_pantau' : 'berisiko_tinggi';
    }

    /**
     * Hasil "kosong": tidak ada skor dan tidak ada kategori.
     *
     * @return array{status_data: string, skor_penyimpangan: null, kategori_risiko: null, deviasi: array<string, float>}
     */
    protected function tanpaSkor(string $status): array
    {
        return [
            'status_data' => $status,
            'skor_penyimpangan' => null,
            'kategori_risiko' => null,
            'deviasi' => [],
        ];
    }

    /**
     * Hitung skor penyimpangan dari 4 parameter (skala 0-100).
     *
     * Data diperiksa dulu: parameter kosong -> `data_tidak_lengkap`, parameter
     * di luar rentang (atau bukan angka) -> `data_tidak_valid`. Keduanya tidak
     * menghasilkan skor mau pun kategori.
     *
     * @return array{status_data: string, skor_penyimpangan: ?float, kategori_risiko: ?string, deviasi: array<string, float>}
     */
    public function hitungSkor(array $parameter): array
    {
        foreach (self::PARAMETERS as $nama) {
            if (! array_key_exists($nama, $parameter) || $parameter[$nama] === null) {
                return $this->tanpaSkor(self::STATUS_TIDAK_LENGKAP);
            }

            if (! is_numeric($parameter[$nama])) {
                return $this->tanpaSkor(self::STATUS_TIDAK_VALID);
            }

            $nilai = (float) $parameter[$nama];
            if ($nilai < self::RENTANG_MIN || $nilai > self::RENTANG_MAKS) {
                return $this->tanpaSkor(self::STATUS_TIDAK_VALID);
            }
        }

        $skor = 0.0;
        $totalBobot = 0.0;
        $deviasi = [];

        foreach (self::PARAMETERS as $nama) {
            $nilai = (float) $parameter[$nama];
            $jarak = abs($nilai - self::BASELINE[$nama]);

            $deviasi[$nama] = round($jarak, 2);
            $skor += $jarak * self::BOBOT[$nama];
            $totalBobot += self::BOBOT[$nama];
        }

        $skor = $totalBobot > 0 ? round($skor / $totalBobot, 2) : 0.0;

        return [
            'status_data' => self::STATUS_LENGKAP,
            'skor_penyimpangan' => $skor,
            'kategori_risiko' => self::kategoriDariSkor($skor),
            'deviasi' => $deviasi,
        ];
    }

    /**
     * Hitung 4 parameter akademik siswa UNTUK SATU PERTEMUAN.
     *
     * Hanya komponen yang sudah berakhir/dinilai yang dihitung (kuis selesai,
     * tugas lewat deadline). Pengumpulan yang belum dinilai tidak dihitung 0
     * dan tidak dianggap terlewat.
     *
     * @return array<string, ?float>
     */
    public function parameterPertemuan(int $siswaId, Pertemuan $pertemuan): array
    {
        $now = now();

        $kuisIds = Kuis::where('pertemuan_id', $pertemuan->id)
            ->where(function ($q) use ($now) {
                $q->where('is_aktif', false)
                    ->orWhere(fn ($q2) => $q2->whereNotNull('tanggal_selesai')->where('tanggal_selesai', '<=', $now));
            })
            ->pluck('id');

        $tugasIds = Tugas::where('pertemuan_id', $pertemuan->id)
            ->whereNotNull('deadline')
            ->where('deadline', '<=', $now)
            ->pluck('id');

        $skorKuis = $kuisIds->isEmpty()
            ? collect()
            : KuisHasil::where('siswa_id', $siswaId)->whereIn('kuis_id', $kuisIds)->pluck('skor');

        $pengumpulan = $tugasIds->isEmpty()
            ? collect()
            : PengumpulanTugas::where('siswa_id', $siswaId)
                ->whereIn('tugas_id', $tugasIds)
                ->get(['nilai', 'terlambat']);

        $nilaiTugas = $pengumpulan->whereNotNull('nilai')->values();

        $semuaNilai = $skorKuis->concat($nilaiTugas->pluck('nilai'));
        $rataNilai = $semuaNilai->isNotEmpty() ? round((float) $semuaNilai->avg(), 1) : null;

        $jumlahTugas = $tugasIds->count();

        // Penyebut = seluruh tugas yang deadline-nya sudah lewat (sesuai
        // spesifikasi), bukan hanya yang dikumpulkan.
        $terlambat = $pengumpulan->where('terlambat', true)->count();
        $persenTerlambat = $jumlahTugas > 0 ? round($terlambat / $jumlahTugas * 100, 1) : null;

        $terlewat = max(0, $jumlahTugas - $pengumpulan->count());
        $persenTerlewat = $jumlahTugas > 0 ? round($terlewat / $jumlahTugas * 100, 1) : null;

        $perolehan = (int) $skorKuis->sum() + (int) $nilaiTugas->sum('nilai');
        $nilaiSempurna = ($kuisIds->count() * 100) + ($nilaiTugas->count() * 100);
        $nilaiKeseluruhan = $nilaiSempurna > 0
            ? round($perolehan / $nilaiSempurna * 100, 1)
            : null;

        return [
            'rata_nilai' => $rataNilai,
            'persen_terlambat' => $persenTerlambat,
            'persen_terlewat' => $persenTerlewat,
            'nilai_keseluruhan' => $nilaiKeseluruhan,
        ];
    }

    public function simpanPertemuan(int $siswaId, Pertemuan $pertemuan, ?array $parameter = null): ?PrediksiRisikoPertemuan
    {
        if (! $pertemuan->dibuka) {
            return null;
        }

        $parameter ??= $this->parameterPertemuan($siswaId, $pertemuan);
        $hasil = $this->hitungSkor($parameter);

        return PrediksiRisikoPertemuan::updateOrCreate(
            ['siswa_id' => $siswaId, 'pertemuan_id' => $pertemuan->id],
            [
                ...$parameter,
                'kelas_id' => $pertemuan->kelas_id,
                'metode' => self::METODE,
                'versi_konfigurasi' => self::VERSI_KONFIGURASI,
                'skor_penyimpangan' => $hasil['skor_penyimpangan'],
                'kategori_risiko' => $hasil['kategori_risiko'],
                'status_data' => $hasil['status_data'],
            ]
        );
    }

    /**
     * Tulis ulang snapshot `prediksi_ai` siswa dari baris risiko pertemuan
     * TERAKHIR (urutan tertinggi). Baris risiko yang datanya tidak lengkap
     * tidak menimpa snapshot agar dashboard tidak menampilkan kategori palsu.
     */
    public function perbaruiSnapshot(int $siswaId): ?PrediksiAi
    {
        $baris = PrediksiRisikoPertemuan::where('siswa_id', $siswaId)
            ->with('pertemuan')
            ->get()
            ->sortByDesc(fn (PrediksiRisikoPertemuan $r) => [
                $r->pertemuan?->urutan ?? 0,
                $r->kelas_id ?? 0,
                $r->pertemuan_id,
            ])
            ->first();

        if (! $baris
            || $baris->status_data !== self::STATUS_LENGKAP
            || $baris->skor_penyimpangan === null
            || $baris->kategori_risiko === null) {
            return null;
        }

        return PrediksiAi::updateOrCreate(
            ['siswa_id' => $siswaId],
            [
                'hasil' => $baris->kategori_risiko,
                'skor_risiko' => (float) $baris->skor_penyimpangan,
                'feature_vector' => [
                    (float) $baris->rata_nilai,
                    (float) $baris->persen_terlambat,
                    (float) $baris->persen_terlewat,
                    (float) $baris->nilai_keseluruhan,
                ],
            ]
        );
    }

    /**
     * Hitung & simpan skor risiko untuk seluruh (siswa, pertemuan yang dibuka),
     * lalu perbarui snapshot `prediksi_ai` tiap siswa. Opsional menyegarkan
     * snapshot fitur 7-indikator per-pertemuan (untuk grafik & regresi).
     *
     * @return array{siswa: int, pertemuan: int, snapshot: int, lengkap: int, tidak_lengkap: int, tidak_valid: int, catatan: array<int, string>}
     */
    public function jalankan(bool $backfill = true): array
    {
        $laporan = [
            'siswa' => 0,
            'pertemuan' => 0,
            'snapshot' => 0,
            'lengkap' => 0,
            'tidak_lengkap' => 0,
            'tidak_valid' => 0,
            'catatan' => [],
        ];

        try {
            $siswaIds = [];

            $pertemuans = Pertemuan::query()
                ->dibuka()
                ->orderBy('kelas_id')
                ->orderBy('urutan')
                ->get();

            foreach ($pertemuans->groupBy('kelas_id') as $kelasId => $daftar) {
                foreach ($this->siswaKelas((int) $kelasId) as $siswaId) {
                    $siswaIds[$siswaId] = true;

                    foreach ($daftar as $pertemuan) {
                        $baris = $this->simpanPertemuan($siswaId, $pertemuan);
                        $laporan['pertemuan']++;

                        if ($baris?->status_data === self::STATUS_LENGKAP) {
                            $laporan['lengkap']++;
                        } elseif ($baris?->status_data === self::STATUS_TIDAK_VALID) {
                            $laporan['tidak_valid']++;
                        } elseif ($baris !== null) {
                            $laporan['tidak_lengkap']++;
                        }
                    }
                }
            }

            foreach (array_keys($siswaIds) as $siswaId) {
                if ($this->perbaruiSnapshot($siswaId) !== null) {
                    $laporan['snapshot']++;
                }
            }

            $laporan['siswa'] = count($siswaIds);

            if ($backfill) {
                $laporan['fitur_pertemuan'] = app(FiturPertemuanService::class)->backfill();
            }
        } catch (\Throwable $e) {
            Log::warning('PrediksiRisikoService::jalankan gagal: {msg}', ['msg' => $e->getMessage()]);
            $laporan['catatan'][] = $e->getMessage();
        }

        return $laporan;
    }

    /**
     * @return \Illuminate\Support\Collection<int, int>
     */
    protected function siswaKelas(int $kelasId)
    {
        return User::where('role', 'pelajar')
            ->whereHas('kelasDiikuti', fn ($q) => $q->where('kelas_id', $kelasId))
            ->pluck('id');
    }
}
