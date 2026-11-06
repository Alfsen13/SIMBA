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
use Illuminate\Support\Collection;

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

    /**
     * Hitung indikator risiko untuk seluruh pasangan siswa-pertemuan dalam
     * satu kelas dengan mengambil asesmen dan nilai per batch.
     *
     * @param  array<int, int>  $siswaIds
     * @param  array<int, Pertemuan>  $pertemuans
     * @return array<int, array<int, array<string, ?float>>>
     */
    private function parameterPertemuanBatch(array $siswaIds, array $pertemuans): array
    {
        $pertemuanIds = array_map(fn (Pertemuan $pertemuan) => (int) $pertemuan->id, $pertemuans);
        $now = now();

        $kuis = Kuis::whereIn('pertemuan_id', $pertemuanIds)
            ->where(fn ($query) => $query->where('is_aktif', false)
                ->orWhere(fn ($query) => $query->whereNotNull('tanggal_selesai')
                    ->where('tanggal_selesai', '<=', $now)))
            ->get(['id', 'pertemuan_id']);
        $kuisKePertemuan = $kuis->pluck('pertemuan_id', 'id');
        $jumlahKuis = $kuis->countBy('pertemuan_id');

        $tugas = Tugas::whereIn('pertemuan_id', $pertemuanIds)
            ->whereNotNull('deadline')
            ->where('deadline', '<=', $now)
            ->get(['id', 'pertemuan_id']);
        $tugasKePertemuan = $tugas->pluck('pertemuan_id', 'id');
        $jumlahTugas = $tugas->countBy('pertemuan_id');

        $nilai = [];
        if ($kuisKePertemuan->isNotEmpty()) {
            foreach (KuisHasil::whereIn('siswa_id', $siswaIds)
                ->whereIn('kuis_id', $kuisKePertemuan->keys())
                ->get(['siswa_id', 'kuis_id', 'skor']) as $hasil) {
                $siswaId = (int) $hasil->siswa_id;
                $pertemuanId = (int) $kuisKePertemuan[$hasil->kuis_id];
                $nilai[$siswaId][$pertemuanId] ??= ['jumlah' => 0, 'total' => 0.0, 'tugas_dinilai' => 0];
                $nilai[$siswaId][$pertemuanId]['jumlah']++;
                $nilai[$siswaId][$pertemuanId]['total'] += (float) $hasil->skor;
            }
        }

        $pengumpulan = [];
        if ($tugasKePertemuan->isNotEmpty()) {
            foreach (PengumpulanTugas::whereIn('siswa_id', $siswaIds)
                ->whereIn('tugas_id', $tugasKePertemuan->keys())
                ->get(['siswa_id', 'tugas_id', 'nilai', 'terlambat']) as $baris) {
                $siswaId = (int) $baris->siswa_id;
                $pertemuanId = (int) $tugasKePertemuan[$baris->tugas_id];
                $pengumpulan[$siswaId][$pertemuanId] ??= ['jumlah' => 0, 'terlambat' => 0];
                $pengumpulan[$siswaId][$pertemuanId]['jumlah']++;
                $pengumpulan[$siswaId][$pertemuanId]['terlambat'] += $baris->terlambat ? 1 : 0;

                if ($baris->nilai !== null) {
                    $nilai[$siswaId][$pertemuanId] ??= ['jumlah' => 0, 'total' => 0.0, 'tugas_dinilai' => 0];
                    $nilai[$siswaId][$pertemuanId]['jumlah']++;
                    $nilai[$siswaId][$pertemuanId]['total'] += (float) $baris->nilai;
                    $nilai[$siswaId][$pertemuanId]['tugas_dinilai']++;
                }
            }
        }

        $parameter = [];
        foreach ($siswaIds as $siswaId) {
            $siswaId = (int) $siswaId;

            foreach ($pertemuans as $pertemuan) {
                $pertemuanId = (int) $pertemuan->id;
                $nilaiSiswa = $nilai[$siswaId][$pertemuanId] ?? null;
                $pengumpulanSiswa = $pengumpulan[$siswaId][$pertemuanId] ?? ['jumlah' => 0, 'terlambat' => 0];
                $jumlahTugasPertemuan = (int) ($jumlahTugas[$pertemuanId] ?? 0);

                $parameter[$siswaId][$pertemuanId] = [
                    'rata_nilai' => $nilaiSiswa && $nilaiSiswa['jumlah'] > 0
                        ? round($nilaiSiswa['total'] / $nilaiSiswa['jumlah'], 1)
                        : null,
                    'persen_terlambat' => $jumlahTugasPertemuan > 0
                        ? round($pengumpulanSiswa['terlambat'] / $jumlahTugasPertemuan * 100, 1)
                        : null,
                    'persen_terlewat' => $jumlahTugasPertemuan > 0
                        ? round(max(0, $jumlahTugasPertemuan - $pengumpulanSiswa['jumlah']) / $jumlahTugasPertemuan * 100, 1)
                        : null,
                    'nilai_keseluruhan' => $nilaiSiswa && ($jumlahKuis[$pertemuanId] ?? 0) + $nilaiSiswa['tugas_dinilai'] > 0
                        ? round(
                            $nilaiSiswa['total']
                                / (((int) ($jumlahKuis[$pertemuanId] ?? 0) + $nilaiSiswa['tugas_dinilai']) * 100)
                                * 100,
                            1
                        )
                        : null,
                ];
            }
        }

        return $parameter;
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
     * Perbarui snapshot banyak siswa dengan satu pembacaan riwayat risiko.
     *
     * @param  array<int, int>  $siswaIds
     */
    private function perbaruiSnapshotBatch(array $siswaIds): int
    {
        $terbaru = PrediksiRisikoPertemuan::whereIn('siswa_id', $siswaIds)
            ->with('pertemuan:id,urutan')
            ->get()
            ->groupBy('siswa_id');
        $now = now();
        $rows = [];

        foreach ($terbaru as $riwayatSiswa) {
            $baris = $riwayatSiswa->sortByDesc(fn (PrediksiRisikoPertemuan $risiko) => [
                $risiko->pertemuan?->urutan ?? 0,
                $risiko->kelas_id ?? 0,
                $risiko->pertemuan_id,
            ])->first();

            if ($baris->status_data !== self::STATUS_LENGKAP
                || $baris->skor_penyimpangan === null
                || $baris->kategori_risiko === null) {
                continue;
            }

            $rows[] = [
                'siswa_id' => $baris->siswa_id,
                'hasil' => $baris->kategori_risiko,
                'skor_risiko' => (float) $baris->skor_penyimpangan,
                'feature_vector' => json_encode([
                    (float) $baris->rata_nilai,
                    (float) $baris->persen_terlambat,
                    (float) $baris->persen_terlewat,
                    (float) $baris->nilai_keseluruhan,
                ], JSON_THROW_ON_ERROR),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 500) as $batch) {
            PrediksiAi::upsert(
                $batch,
                ['siswa_id'],
                ['hasil', 'skor_risiko', 'feature_vector', 'updated_at']
            );
        }

        return count($rows);
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

        $siswaIds = [];
        $pertemuans = Pertemuan::query()
            ->dibuka()
            ->orderBy('kelas_id')
            ->orderBy('urutan')
            ->get();

        foreach ($pertemuans->groupBy('kelas_id') as $kelasId => $daftar) {
            foreach ($this->siswaKelas((int) $kelasId)->chunk(200) as $siswaBatch) {
                $idsBatch = $siswaBatch->map(fn ($id) => (int) $id)->all();
                $siswaIds = array_merge($siswaIds, $idsBatch);
                $parameterBatch = $this->parameterPertemuanBatch($idsBatch, $daftar->all());
                $rows = [];
                $now = now();

                foreach ($idsBatch as $siswaId) {
                    foreach ($daftar as $pertemuan) {
                        $parameter = $parameterBatch[$siswaId][$pertemuan->id];
                        $hasil = $this->hitungSkor($parameter);
                        $rows[] = [
                            'siswa_id' => $siswaId,
                            'kelas_id' => $pertemuan->kelas_id,
                            'pertemuan_id' => $pertemuan->id,
                            ...$parameter,
                            'metode' => self::METODE,
                            'versi_konfigurasi' => self::VERSI_KONFIGURASI,
                            'skor_penyimpangan' => $hasil['skor_penyimpangan'],
                            'kategori_risiko' => $hasil['kategori_risiko'],
                            'status_data' => $hasil['status_data'],
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                        $laporan['pertemuan']++;

                        if ($hasil['status_data'] === self::STATUS_LENGKAP) {
                            $laporan['lengkap']++;
                        } elseif ($hasil['status_data'] === self::STATUS_TIDAK_VALID) {
                            $laporan['tidak_valid']++;
                        } else {
                            $laporan['tidak_lengkap']++;
                        }
                    }
                }

                foreach (array_chunk($rows, 500) as $batch) {
                    PrediksiRisikoPertemuan::upsert(
                        $batch,
                        ['siswa_id', 'pertemuan_id'],
                        [
                            'kelas_id',
                            'metode',
                            'versi_konfigurasi',
                            'rata_nilai',
                            'persen_terlambat',
                            'persen_terlewat',
                            'nilai_keseluruhan',
                            'skor_penyimpangan',
                            'kategori_risiko',
                            'status_data',
                            'updated_at',
                        ]
                    );
                }
            }
        }

        $siswaIds = array_values(array_unique(array_map('intval', $siswaIds)));
        $laporan['siswa'] = count($siswaIds);
        foreach (array_chunk($siswaIds, 500) as $batch) {
            $laporan['snapshot'] += $this->perbaruiSnapshotBatch($batch);
        }

        if ($backfill) {
            $laporan['fitur_pertemuan'] = app(FiturPertemuanService::class)->backfill();
        }

        return $laporan;
    }

    /**
     * @return Collection<int, int>
     */
    protected function siswaKelas(int $kelasId)
    {
        return User::where('role', 'pelajar')
            ->whereHas('kelasDiikuti', fn ($q) => $q->where('kelas_id', $kelasId))
            ->pluck('id');
    }
}
