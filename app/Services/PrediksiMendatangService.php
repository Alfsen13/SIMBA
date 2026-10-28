<?php

namespace App\Services;

use App\Models\FiturSiswaHistory;
use App\Models\Pertemuan;
use App\Models\PrediksiMendatang;
use App\Models\PrediksiRisikoPertemuan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Phpml\Regression\LeastSquares;
use Phpml\Regression\SVR;
use Phpml\SupportVectorMachine\Kernel;
use RuntimeException;

 
class PrediksiMendatangService
{
     
    public const TARGET = 'nilai_pertemuan_berikutnya';

    /**
     * Nilai acuan untuk `persentase_meleset`. Ini BUKAN ukuran kesalahan
     * prediksi (itu MAE/RMSE) - hanya jarak relatif prediksi dari nilai ideal.
     */
    public const NILAI_IDEAL = 100.0;

     
    protected int $minSamples = 30;

    protected int $minSiswaUnik = 3;

     
    public const KANDIDAT = [
        'svr_linear' => 'SVR kernel linear',
    ];

    protected string $modelDir = 'ml';

    /** Cache peta pertemuan per kelas; direset tiap kali predictRows() jalan. */
    protected ?Collection $petaPertemuan = null;

    /** Cache json benchmark supaya tidak dibaca ulang per siswa. */
    protected ?array $benchmarkCache = null;

    protected bool $benchmarkDibaca = false;

    /**
     * Jarak relatif prediksi dari nilai ideal (skala 0-100).
     *
     * Rumus konsep: abs(nilai_ideal - prediksi) / nilai_ideal * 100.
     * Untuk nilai ideal 100 rumus ini setara dengan abs(100 - prediksi).
     * Hasilnya menunjukkan seberapa jauh prediksi dari nilai sempurna, BUKAN
     * seberapa salah prediksinya. Jangan dipakai menggantikan MAE/RMSE.
     */
    public static function persentaseMeleset(float $prediksi, float $nilaiIdeal = self::NILAI_IDEAL): float
    {
        if ($nilaiIdeal <= 0) {
            throw new \InvalidArgumentException('Nilai ideal harus lebih besar dari 0.');
        }

        return round(abs($nilaiIdeal - $prediksi) / $nilaiIdeal * 100, 2);
    }




     
    public function buildDataset(): array
    {
        $riwayat = FiturSiswaHistory::with('pertemuan')
            ->whereNotNull('pertemuan_id')

            ->whereHas('pertemuan', fn ($q) => $q->dibuka())
            ->get()
            ->filter(fn (FiturSiswaHistory $r) => $r->pertemuan !== null);


        $perSiswaKelas = $riwayat->groupBy(
            fn (FiturSiswaHistory $r) => $r->siswa_id.'-'.$r->pertemuan->kelas_id
        );

        $samples = [];
        $targets = [];
        $siswaIds = [];
        $kelasIds = [];

        foreach ($perSiswaKelas as $baris) {
            $baris = $baris->sortBy(fn (FiturSiswaHistory $r) => $r->pertemuan->urutan)->values();
            $n = $baris->count();

            for ($i = 0; $i < $n - 1; $i++) {
                $asal = $baris[$i];
                $tujuan = $baris[$i + 1];

                if ($tujuan->rata_nilai === null) {
                    continue;
                }

                $samples[] = $this->rawFromHistory($asal);
                $targets[] = (float) $tujuan->rata_nilai;
                $siswaIds[] = (int) $asal->siswa_id;
                $kelasIds[] = (int) $asal->pertemuan->kelas_id;
            }
        }

        return [$samples, $targets, $siswaIds, $kelasIds];
    }

     
    protected function rawFromHistory(FiturSiswaHistory $row): array
    {
        $out = [];
        foreach (FiturPertemuanService::fiturModel() as $nama) {
            $nilai = $row->{$nama};
            $out[] = $nilai === null ? null : (float) $nilai;
        }

        return $out;
    }




     
    /**
     * @param  array{0: array<int, array<int, float|null>>, 1: array<int, float>, 2: array<int, int>, 3: array<int, int>}|null  $dataset
     *         Dataset siap pakai (mis. dari snapshot `ml:train --dataset`). `null` =
     *         turunkan dari `fitur_siswa_history`.
     * @return array<string, mixed>
     */
    public function train(?array $dataset = null): array
    {
        $laporan = [
            'message' => 'Model prediksi pertemuan berikutnya berhasil dilatih.',
            'models' => [],
        ];

        try {
            $laporan['models'][self::TARGET] = $this->trainOne($dataset);
        } catch (RuntimeException $e) {
            $laporan['models'][self::TARGET] = ['error' => $e->getMessage()];
        }

        $berhasil = array_filter($laporan['models'], fn ($m) => ! isset($m['error']));

        if ($berhasil === []) {
            throw new RuntimeException(
                'Tidak ada target yang bisa dilatih. '
                .'Butuh riwayat minimal '.$this->minSamples.' pasangan pertemuan.'
            );
        }

        $model = reset($berhasil);
        $laporan['total_samples'] = $model['total_samples'] ?? 0;
        $laporan['train_samples'] = $model['train_samples'] ?? 0;
        $laporan['test_samples'] = $model['test_samples'] ?? 0;

        [, , $siswaIds] = $dataset ?? $this->buildDataset();
        $laporan['siswa_unik'] = count(array_unique($siswaIds));

        return $laporan;
    }

     
    protected function trainOne(?array $dataset = null): array
    {
        [$samples, $targets, $siswaIds] = $dataset ?? $this->buildDataset();

        if (count($samples) < $this->minSamples) {
            throw new RuntimeException(sprintf(
                'Data tidak cukup: butuh minimal %d pasangan riwayat pertemuan, tersedia %d. '
                .'Jalankan prediksi rutin supaya fitur_siswa_history per-pertemuan terisi.',
                $this->minSamples,
                count($samples)
            ));
        }

        if (count(array_unique($siswaIds)) < $this->minSiswaUnik) {
            throw new RuntimeException(
                'Data tidak cukup: butuh minimal '.$this->minSiswaUnik
                .' siswa berbeda agar split uji bermakna. Tersedia '
                .count(array_unique($siswaIds)).'.'
            );
        }

        [$imputed, $medians] = $this->impute($samples);


        $X = array_map(fn ($r) => array_map(fn ($v) => $v / 100, $r), $imputed);
        $y = array_map(fn ($v) => $v / 100, $targets);

        [$XTrain, $yTrain, $XTest, $yTest] = $this->splitGrouped($X, $y, $siswaIds);

        $adaUji = count($XTest) > 0;

        $benchmark = [];
        $winner = null;
        $winnerModel = null;
        $winnerMetrik = null;
        $maeTerbaik = INF;
        $cadangan = null;
        $cadanganModel = null;

        foreach (self::KANDIDAT as $kunci => $nama) {
            $model = $this->buildCandidate($kunci);

            if ($model === null) {
                $benchmark[$kunci] = [
                    'nama' => $nama,
                    'error' => 'Kandidat tidak tersedia di library regresi yang terpasang.',
                ];

                continue;
            }

            try {
                $model->train($XTrain, $yTrain);
                $prediksiTest = $this->predictBatch($model, $XTest);

                if ($this->adaPrediksiTidakValid($prediksiTest)) {
                    $benchmark[$kunci] = [
                        'nama' => $nama,
                        'error' => 'Prediksi menghasilkan nilai tidak hingga '
                            .'(kemungkinan matriks singular) - kandidat dilewati.',
                    ];

                    continue;
                }

                $mae = $this->mae($prediksiTest, $yTest) * 100;
                $rmse = $this->rmse($prediksiTest, $yTest) * 100;
                $r2 = $this->r2($prediksiTest, $yTest);

                $benchmark[$kunci] = [
                    'nama' => $nama,
                    'mae' => round($mae, 2),
                    'rmse' => round($rmse, 2),
                    'r2' => $r2 === null ? null : round($r2, 4),
                ];

                if ($cadangan === null) {
                    $cadangan = $kunci;
                    $cadanganModel = $model;
                }

                if ($adaUji && $mae < $maeTerbaik) {
                    $maeTerbaik = $mae;
                    $winner = $kunci;
                    $winnerModel = $model;
                    $winnerMetrik = $benchmark[$kunci];
                }
            } catch (\Throwable $e) {
                $benchmark[$kunci] = [
                    'nama' => $nama,
                    'error' => 'Gagal dilatih: '.$e->getMessage(),
                ];
            }
        }

        if ($winner === null) {
            if ($cadangan === null) {
                throw new RuntimeException(
                    'Semua kandidat regresi gagal dilatih untuk target '.self::TARGET.'.'
                );
            }

            $winner = $cadangan;
            $winnerModel = $cadanganModel;
            $winnerMetrik = $benchmark[$cadangan];
        }

        $versi = 'svr-'.now()->format('Ymd\THis');

        Storage::put($this->modelPath(), serialize($winnerModel));
        Storage::put($this->medianPath(), json_encode($medians, JSON_THROW_ON_ERROR));

        $hasil = [
            'total_samples' => count($X),
            'train_samples' => count($XTrain),
            'test_samples' => count($XTest),
        ];

        if ($adaUji && $winnerMetrik !== null) {
            $hasil['mae'] = $winnerMetrik['mae'];
            $hasil['rmse'] = $winnerMetrik['rmse'];
            $hasil['r2'] = $winnerMetrik['r2'];
        } else {
            $hasil['catatan'] = 'Data terlalu sedikit untuk split uji — '
                .'metrik regresi tidak dilaporkan karena bisa menyesatkan.';
        }

        $hasil['pemenang'] = $winner;
        $hasil['pemenang_nama'] = self::KANDIDAT[$winner];
        $hasil['benchmark'] = $benchmark;

        Storage::put(
            $this->benchmarkPath(),
            json_encode([
                'target' => self::TARGET,
                'fitur' => FiturPertemuanService::fiturModel(),
                'total_samples' => count($X),
                'train_samples' => count($XTrain),
                'test_samples' => count($XTest),
                'pemenang' => $winner,
                'pemenang_nama' => self::KANDIDAT[$winner],
                'versi_model' => $versi,
                'metode_pemilihan' => $adaUji
                    ? 'MAE terendah di data uji; seri jatuh ke kandidat paling sederhana.'
                    : 'Tanpa split uji - dipilih kandidat paling sederhana, bukan berbasis performa.',
                'benchmark' => $benchmark,
                'dilatih_pada' => now()->toIso8601String(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
        );

        // Benchmark baru saja ditulis ulang; buang cache versi lama.
        $this->benchmarkCache = null;
        $this->benchmarkDibaca = false;

        return $hasil;
    }

    protected function buildCandidate(string $kunci): ?object
    {
        return match ($kunci) {
            'linear' => new LeastSquares,
            'svr_linear' => new SVR(
                kernel: Kernel::LINEAR,
                epsilon: 0.01,
                cost: 1.0,
                tolerance: 0.001,
            ),
            'svr_rbf' => new SVR(
                kernel: Kernel::RBF,
                epsilon: 0.01,
                cost: 1.0,
                tolerance: 0.001,
            ),
            default => null,
        };
    }

     
    protected function adaPrediksiTidakValid(array $prediksi): bool
    {
        foreach ($prediksi as $v) {
            if (! is_finite((float) $v)) {
                return true;
            }
        }

        return false;
    }

     
    protected function predictBatch(object $model, array $X): array
    {
        if ($X === []) {
            return [];
        }

        return array_map('floatval', (array) $model->predict($X));
    }

     
    protected function splitGrouped(array $X, array $y, array $siswaIds): array
    {
        $uniqueSiswa = array_values(array_unique($siswaIds));
        sort($uniqueSiswa);

        $jumlahTest = (int) floor(count($uniqueSiswa) * 0.3);
        $jumlahTest = max(1, min($jumlahTest, count($uniqueSiswa) - 1));

        $testSiswa = array_flip(array_slice($uniqueSiswa, -$jumlahTest));

        $XTrain = $yTrain = $XTest = $yTest = [];
        foreach ($X as $i => $sample) {
            if (isset($testSiswa[$siswaIds[$i]])) {
                $XTest[] = $sample;
                $yTest[] = $y[$i];
            } else {
                $XTrain[] = $sample;
                $yTrain[] = $y[$i];
            }
        }

        return [$XTrain, $yTrain, $XTest, $yTest];
    }




     
    public function predict(int $siswaId): array
    {
        $hasil = $this->predictRows($this->barisTerbaruPerSiswa([$siswaId]));

        if ($hasil === [] || ($hasil[$siswaId][self::TARGET] ?? null) === null) {
            return [
                'error' => 'Model prediksi belum dilatih atau data per-pertemuan belum cukup. '
                    .'Jalankan php artisan ml:train.',
            ];
        }

        return $hasil[$siswaId];
    }

     
    public function predictAll(): array
    {
        $baris = FiturSiswaHistory::with('pertemuan')
            ->whereNotNull('pertemuan_id')
            ->whereHas('pertemuan', fn ($q) => $q->dibuka())
            ->get();

        return $this->predictRows($this->barisTerbaruDari($baris));
    }

     
    protected function barisTerbaruPerSiswa(?array $siswaIds = null): array
    {
        $query = FiturSiswaHistory::with('pertemuan')
            ->whereNotNull('pertemuan_id')
            ->whereHas('pertemuan', fn ($q) => $q->dibuka());
        if ($siswaIds !== null) {
            $query->whereIn('siswa_id', $siswaIds);
        }

        return $this->barisTerbaruDari($query->get());
    }

     
    protected function barisTerbaruDari($baris): array
    {
        $out = [];
        foreach ($baris->groupBy('siswa_id') as $siswaId => $rows) {
            $terbaru = $rows
                ->filter(fn (FiturSiswaHistory $r) => $r->pertemuan !== null)
                ->sortByDesc(fn (FiturSiswaHistory $r) => $r->pertemuan->urutan)
                ->first();

            if ($terbaru !== null) {
                $out[(int) $siswaId] = $terbaru;
            }
        }

        return $out;
    }

     
    protected function predictRows(array $baris): array
    {
        if ($baris === []) {
            return [];
        }

        // Peta pertemuan & benchmark di-cache selama satu kali predictRows saja.
        $this->petaPertemuan = null;

        $model = $this->loadModel();
        if ($model === null) {
            return [];
        }

        // Model di disk bisa saja dilatih untuk daftar fitur lama (mis. saat
        // frekuensi akses masih dipakai). Vektor baru akan bergeser kolomnya,
        // jadi lebih baik dianggap belum siap daripada salah prediksi.
        if (! $this->modelSesuaiFitur()) {
            Log::warning(
                'Model '.self::TARGET.' dilatih untuk daftar fitur lama; jalankan ml:train ulang.'
            );

            return [];
        }

        $rawRows = [];
        foreach ($baris as $row) {
            $rawRows[] = $this->rawFromHistory($row);
        }

        [$imputed] = $this->impute($rawRows, $this->loadMedians());
        $matrix = array_map(fn ($r) => array_map(fn ($v) => $v / 100, $r), $imputed);

        $hasilPerSiswa = [];
        try {
            $prediksi = $this->predictBatch($model, $matrix);
        } catch (\Throwable $e) {
            Log::warning('Prediksi batch gagal untuk '.self::TARGET.': '.$e->getMessage());

            return [];
        }

        foreach (array_keys($baris) as $i => $siswaId) {
            $nilai = round(max(0, min(100, $prediksi[$i] * 100)), 1);

            // `simpan()` mengembalikan metadata ramalan (pertemuan sumber &
            // target, persentase meleset, status data, versi model) supaya
            // hasil yang dikembalikan ke pemanggil identik dengan yang
            // tersimpan - tidak dihitung dua kali dengan cara berbeda.
            $meta = $this->simpan((int) $siswaId, $nilai, $baris[$siswaId]);

            $hasilPerSiswa[$siswaId] = [self::TARGET => $nilai, ...$meta];
        }

        return $hasilPerSiswa;
    }




    protected function modelPath(): string
    {
        return $this->modelDir.'/svr_'.self::TARGET.'.ser';
    }

    protected function medianPath(): string
    {
        return $this->modelDir.'/svr_'.self::TARGET.'_medians.json';
    }

    protected function benchmarkPath(): string
    {
        return $this->modelDir.'/benchmark_'.self::TARGET.'.json';
    }

     
    public function loadBenchmark(): ?array
    {
        if ($this->benchmarkDibaca) {
            return $this->benchmarkCache;
        }

        $this->benchmarkDibaca = true;
        $path = $this->benchmarkPath();

        if (! Storage::exists($path)) {
            return $this->benchmarkCache = null;
        }

        return $this->benchmarkCache = json_decode(Storage::get($path), true);
    }

    protected function loadModel(): ?object
    {
        $path = $this->modelPath();
        if (! Storage::exists($path)) {
            return null;
        }

        $payload = Storage::get($path);

        preg_match_all('/O:\d+:"([^"]+)"/', $payload, $m);
        $allowed = array_values(array_filter(
            $m[1] ?? [],
            fn ($class) => str_starts_with($class, 'Phpml\\')
        ));
        $allowed[] = SVR::class;
        $allowed[] = LeastSquares::class;

        try {
            return unserialize($payload, [
                'allowed_classes' => array_values(array_unique($allowed)),
            ]);
        } catch (\Throwable $e) {
            Log::error('Gagal memuat model '.self::TARGET.': '.$e->getMessage());

            return null;
        }
    }

    protected function loadMedians(): ?array
    {
        $path = $this->medianPath();
        if (! Storage::exists($path)) {
            return null;
        }

        return json_decode(Storage::get($path), true);
    }

    /**
     * Artifact model menyimpan daftar fitur yang dipakai saat dilatih. Kalau
     * daftar itu tidak lagi sama dengan `fiturModel()`, vektor prediksi akan
     * bergeser kolom dan hasilnya ngawur - model dianggap belum siap sampai
     * `ml:train` dijalankan ulang. Artifact tanpa benchmark (versi lama) tetap
     * dicoba supaya tidak memblokir apa pun.
     */
    protected function modelSesuaiFitur(): bool
    {
        $benchmark = $this->loadBenchmark();

        if ($benchmark === null) {
            return true;
        }

        return ($benchmark['fitur'] ?? null) === FiturPertemuanService::fiturModel();
    }

     
    /**
     * Simpan ramalan beserta metadatanya, lalu tempelkan ramalan itu ke baris
     * riwayat risiko pertemuan sumber (kolom `prediksi_nilai_mendatang`).
     *
     * @return array<string, mixed> metadata yang ikut dikembalikan ke pemanggil
     */
    protected function simpan(int $siswaId, float $nilai, ?FiturSiswaHistory $sumber = null): array
    {
        $targetId = $sumber?->pertemuan_id === null
            ? null
            : $this->pertemuanBerikutnyaId($sumber);

        $aktual = $targetId === null
            ? null
            : $this->nilaiAktualUntuk($siswaId, $targetId);

        $meta = [
            'nilai_ideal' => self::NILAI_IDEAL,
            'persentase_meleset' => self::persentaseMeleset($nilai, self::NILAI_IDEAL),
            'status_data' => $sumber === null ? 'data_tidak_lengkap' : $this->statusData($sumber),
            'model_versi' => $this->versiModel(),
            'pertemuan_sumber_id' => $sumber?->pertemuan_id,
            'pertemuan_target_id' => $targetId,
            'nilai_aktual' => $aktual,
            'mae_individual' => $aktual === null ? null : round(abs($nilai - $aktual), 2),
        ];

        try {
            PrediksiMendatang::updateOrCreate(
                ['siswa_id' => $siswaId],
                [
                    ...$meta,
                    'horizon_hari' => 1,
                    'prediksi_nilai' => $nilai,
                    'calculated_at' => now(),
                ]
            );

            if ($sumber?->pertemuan_id !== null) {
                PrediksiRisikoPertemuan::where('siswa_id', $siswaId)
                    ->where('pertemuan_id', $sumber->pertemuan_id)
                    ->update(['prediksi_nilai_mendatang' => $nilai]);
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal menyimpan PrediksiMendatang siswa '.$siswaId.': '.$e->getMessage());
        }

        return $meta;
    }

    /**
     * Isi `nilai_aktual` + `mae_individual` untuk ramalan yang pertemuan
     * targetnya sudah punya nilai. Dipanggil setelah backfill fitur, karena
     * nilai aktual baru muncul belakangan - saat ramalan dibuat biasanya belum
     * ada. Ramalan yang targetnya belum dinilai dibiarkan apa adanya.
     *
     * @return int jumlah baris yang dilengkapi
     */
    public function sinkronkanNilaiAktual(): int
    {
        $jumlah = 0;

        try {
            $baris = PrediksiMendatang::whereNotNull('pertemuan_target_id')->get();

            foreach ($baris as $p) {
                $aktual = $this->nilaiAktualUntuk((int) $p->siswa_id, (int) $p->pertemuan_target_id);

                if ($aktual === null || $p->nilai_aktual === $aktual) {
                    continue;
                }

                $p->update([
                    'nilai_aktual' => $aktual,
                    'mae_individual' => round(abs((float) $p->prediksi_nilai - $aktual), 2),
                ]);

                $jumlah++;
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal menyinkronkan nilai aktual ramalan: '.$e->getMessage());
        }

        return $jumlah;
    }

    /**
     * Nilai `rata_nilai` pada pertemuan target, kalau baris fiturnya sudah ada
     * dan sudah punya nilai. Sumbernya sama dengan sumber target pelatihan
     * (fitur_siswa_history), jadi nilai aktual dan nilai latih konsisten.
     */
    protected function nilaiAktualUntuk(int $siswaId, int $pertemuanId): ?float
    {
        $baris = FiturSiswaHistory::where('siswa_id', $siswaId)
            ->where('pertemuan_id', $pertemuanId)
            ->first();

        return $baris?->rata_nilai === null ? null : (float) $baris->rata_nilai;
    }

    /**
     * Status kelengkapan fitur input. Fitur null tetap diisi median saat
     * pelatihan/prediksi (lihat `impute()`), tapi status ini menandai bahwa
     * ramalan berdiri di atas data yang tidak utuh - jangan disamarkan.
     */
    protected function statusData(FiturSiswaHistory $row): string
    {
        foreach (FiturPertemuanService::fiturModel() as $nama) {
            if ($row->{$nama} === null) {
                return 'data_tidak_lengkap';
            }
        }

        return 'lengkap';
    }

    /**
     * ID pertemuan setelah pertemuan sumber, dalam kelas yang sama, menurut
     * urutan. `null` kalau pertemuan sumber adalah yang terakhir sejauh ini
     * (target belum ada) - konsep tetap mengizinkan ramalan tanpa pertemuan
     * target, hanya saja nilai aktualnya belum bisa dihitung.
     */
    protected function pertemuanBerikutnyaId(FiturSiswaHistory $row): ?int
    {
        $kelasId = $row->pertemuan?->kelas_id;

        if ($kelasId === null) {
            return null;
        }

        $daftar = $this->petaPertemuan()->get($kelasId, collect());

        foreach ($daftar as $i => $pertemuan) {
            if ((int) $pertemuan->id === (int) $row->pertemuan_id) {
                return isset($daftar[$i + 1]) ? (int) $daftar[$i + 1]->id : null;
            }
        }

        return null;
    }

    /**
     * Peta pertemuan yang dibuka, dikelompokkan per kelas dan diurutkan.
     * Satu query untuk seluruh prediksi, bukan satu query per siswa.
     *
     * @return Collection<int|string, Collection<int, Pertemuan>>
     */
    protected function petaPertemuan(): Collection
    {
        return $this->petaPertemuan ??= Pertemuan::query()
            ->dibuka()
            ->orderBy('kelas_id')
            ->orderBy('urutan')
            ->get()
            ->groupBy('kelas_id')
            ->map(fn (Collection $grup) => $grup->values());
    }

    /** Versi model SVR terlatih, untuk ditulis ke hasil prediksi. */
    public function versiModel(): ?string
    {
        return $this->loadBenchmark()['versi_model'] ?? null;
    }




     
    protected function mae(array $prediksi, array $y): float
    {
        if ($prediksi === []) {
            return 0.0;
        }

        $total = 0.0;
        foreach ($prediksi as $i => $p) {
            $total += abs($p - $y[$i]);
        }

        return $total / count($prediksi);
    }

     
    protected function rmse(array $prediksi, array $y): float
    {
        if ($prediksi === []) {
            return 0.0;
        }

        $total = 0.0;
        foreach ($prediksi as $i => $p) {
            $selisih = $p - $y[$i];
            $total += $selisih * $selisih;
        }

        return sqrt($total / count($prediksi));
    }

     
    protected function r2(array $prediksi, array $y): ?float
    {
        if ($prediksi === []) {
            return null;
        }

        $rataTarget = array_sum($y) / count($y);
        $ssTot = 0.0;
        $ssRes = 0.0;

        foreach ($prediksi as $i => $p) {
            $ssRes += ($y[$i] - $p) ** 2;
            $ssTot += ($y[$i] - $rataTarget) ** 2;
        }

        if ($ssTot <= 1e-12) {
            return null;
        }

        return 1 - ($ssRes / $ssTot);
    }




     
    protected function impute(array $rows, ?array $medians = null): array
    {
        $jumlahKolom = count(FiturPertemuanService::fiturModel());

        if ($medians === null) {
            $medians = [];
            for ($c = 0; $c < $jumlahKolom; $c++) {
                $nilai = array_values(array_filter(
                    array_column($rows, $c),
                    fn ($v) => $v !== null
                ));
                $medians[$c] = $nilai === [] ? 0.0 : $this->median($nilai);
            }
        }

        $out = [];
        foreach ($rows as $row) {
            $sample = [];
            for ($c = 0; $c < $jumlahKolom; $c++) {
                $sample[] = (float) ($row[$c] ?? $medians[$c] ?? 0.0);
            }
            $out[] = $sample;
        }

        return [$out, $medians];
    }

    protected function median(array $nilai): float
    {
        sort($nilai);
        $jumlah = count($nilai);
        $tengah = intdiv($jumlah, 2);

        return $jumlah % 2 === 1
            ? (float) $nilai[$tengah]
            : (($nilai[$tengah - 1] + $nilai[$tengah]) / 2);
    }
}

