<?php

namespace App\Services;

use App\Models\PrediksiRisikoPertemuan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Phpml\Classification\DecisionTree;
use Phpml\Classification\Ensemble\RandomForest;
use Phpml\Metric\Accuracy;
use Phpml\Metric\ClassificationReport;
use Phpml\Metric\ConfusionMatrix;
use RuntimeException;

/**
 * Klasifikasi risiko dengan Random Forest.
 *
 * Ini BUKAN penentu kategori risiko. Acuan kategori tetap
 * `PrediksiRisikoService` (Baseline Deviation Scoring). RF dilatih dari 4
 * parameter baseline dengan label hasil aturan baseline, lalu hasilnya
 * disimpan di kolom SENDIRI (`kategori_risiko_rf`) sebagai pembanding:
 * seberapa jauh model terlatih meniru aturan baseline pada data uji.
 *
 * Konsekuensi yang harus jujur disampaikan ke pengguna: karena label latih
 * berasal dari aturan baseline, akurasi tinggi berarti RF bisa meniru aturan
 * itu, bukan bukti RF lebih baik dalam memprediksi risiko akademik nyata.
 * Hasil RF tidak pernah dipakai sebagai label untuk melatih ulang dirinya
 * sendiri (lihat konsep v3: `larangan_label`).
 */
class KlasifikasiRisikoService
{
    public const TARGET = 'kategori_risiko';

    /**
     * Urutan kelas ditetapkan supaya matriks kebingungan tidak bergeser
     * antar-pelatihan dan tetap terbaca saat ditampilkan.
     */
    public const LABELS = ['aman', 'perlu_pantau', 'berisiko_tinggi'];

    /** Fitur input = 4 parameter baseline, bukan 6 fitur SVR. */
    public const FITUR = MLService::FEATURES;

    public const POHON = 50;

    public const KEDALAMAN_MAKS = 12;

    protected int $minSamples = 30;

    protected int $minSiswaUnik = 3;

    protected string $modelDir = 'ml';

    /**
     * Susun dataset dari baris risiko yang datanya lengkap. Hanya baris
     * berstatus `lengkap` yang punya keempat parameter terisi, jadi baris
     * `data_tidak_lengkap`/`data_tidak_valid` tidak dipaksa masuk.
     *
     * @return array{0: array<int, array<int, float>>, 1: array<int, string>, 2: array<int, int>}
     */
    public function buildDataset(): array
    {
        $baris = PrediksiRisikoPertemuan::query()
            ->where('status_data', PrediksiRisikoService::STATUS_LENGKAP)
            ->whereNotNull('kategori_risiko')
            ->orderBy('id')
            ->get();

        $samples = [];
        $labels = [];
        $siswaIds = [];

        foreach ($baris as $r) {
            $sample = [];
            foreach (self::FITUR as $nama) {
                $sample[] = ((float) $r->{$nama}) / 100;
            }

            $samples[] = $sample;
            $labels[] = (string) $r->kategori_risiko;
            $siswaIds[] = (int) $r->siswa_id;
        }

        return [$samples, $labels, $siswaIds];
    }

    /**
     * Latih model, evaluasi di data uji, lalu simpan artifact.
     *
     * Split dikelompokkan per siswa supaya baris milik satu siswa tidak muncul
     * di data latih dan data uji sekaligus (kebocoran yang membuat skor
     * evaluasi terlihat bagus padahal tidak).
     *
     * @param  array{0: array<int, array<int, float>>, 1: array<int, string>, 2: array<int, int>}|null  $dataset
     *         Dataset siap pakai (mis. dari snapshot `ml:train --dataset`). `null` =
     *         turunkan dari `prediksi_risiko_pertemuan`.
     * @return array<string, mixed>
     */
    public function train(?array $dataset = null): array
    {
        [$samples, $labels, $siswaIds] = $dataset ?? $this->buildDataset();

        if (count($samples) < $this->minSamples) {
            throw new RuntimeException(sprintf(
                'Data tidak cukup: butuh minimal %d baris risiko berstatus lengkap, tersedia %d. '
                .'Jalankan prediksi risiko lebih dulu supaya prediksi_risiko_pertemuan terisi.',
                $this->minSamples,
                count($samples)
            ));
        }

        $siswaUnik = count(array_unique($siswaIds));
        if ($siswaUnik < $this->minSiswaUnik) {
            throw new RuntimeException(
                'Data tidak cukup: butuh minimal '.$this->minSiswaUnik
                .' siswa berbeda agar split uji bermakna. Tersedia '.$siswaUnik.'.'
            );
        }

        [$XTrain, $yTrain, $XTest, $yTest] = $this->splitGrouped($samples, $labels, $siswaIds);

        $model = $this->buildModel();
        $model->train($XTrain, $yTrain);

        $prediksiUji = $XTest === [] ? [] : array_values((array) $model->predict($XTest));

        $hasil = [
            'total_samples' => count($samples),
            'train_samples' => count($XTrain),
            'test_samples' => count($XTest),
            'siswa_unik' => $siswaUnik,
        ];

        $versi = 'rf-'.now()->format('Ymd\THis');

        if ($prediksiUji !== []) {
            $laporan = new ClassificationReport($yTest, $prediksiUji, ClassificationReport::MACRO_AVERAGE);

            $hasil['akurasi'] = round(Accuracy::score($yTest, $prediksiUji), 4);
            $hasil['precision_macro'] = round((float) ($laporan->getAverage()['precision'] ?? 0), 4);
            $hasil['recall_macro'] = round((float) ($laporan->getAverage()['recall'] ?? 0), 4);
            $hasil['f1_macro'] = round((float) ($laporan->getAverage()['f1score'] ?? 0), 4);
            $hasil['per_kelas'] = $laporan->getF1score();
            $hasil['confusion_matrix'] = ConfusionMatrix::compute($yTest, $prediksiUji, self::LABELS);
            $hasil['catatan'] = 'Kelas ditiru dari aturan baseline; akurasi mengukur kemampuan meniru, '
                .'bukan bukti prediksi risiko akademik nyata.';
        } else {
            $hasil['catatan'] = 'Tidak ada data uji - metrik klasifikasi tidak dilaporkan karena bisa menyesatkan.';
        }

        Storage::put($this->modelPath(), serialize($model));

        Storage::put(
            $this->benchmarkPath(),
            json_encode([
                'target' => self::TARGET,
                'fitur' => self::FITUR,
                'label' => self::LABELS,
                'sumber_label' => PrediksiRisikoService::METODE,
                'pohon' => self::POHON,
                'kedalaman_maks' => self::KEDALAMAN_MAKS,
                'versi_model' => $versi,
                'total_samples' => count($samples),
                'train_samples' => count($XTrain),
                'test_samples' => count($XTest),
                'siswa_unik' => $siswaUnik,
                'akurasi' => $hasil['akurasi'] ?? null,
                'precision_macro' => $hasil['precision_macro'] ?? null,
                'recall_macro' => $hasil['recall_macro'] ?? null,
                'f1_macro' => $hasil['f1_macro'] ?? null,
                'per_kelas' => $hasil['per_kelas'] ?? null,
                'confusion_matrix' => $hasil['confusion_matrix'] ?? null,
                'dilatih_pada' => now()->toIso8601String(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
        );

        $hasil['versi_model'] = $versi;

        return $hasil;
    }

    /**
     * Klasifikasi ulang SELURUH baris risiko berstatus lengkap dengan model
     * yang sudah ada, lalu tulis hasilnya ke `kategori_risiko_rf`.
     *
     * Dipisah dari `train()` karena prediksi jauh lebih murah daripada
     * pelatihan: tombol "Jalankan Prediksi" cukup memakai model yang ada.
     *
     * @return array{diklasifikasi: int, model: bool, catatan?: string}
     */
    public function klasifikasiSemua(): array
    {
        $model = $this->loadModel();

        if ($model === null) {
            return [
                'diklasifikasi' => 0,
                'model' => false,
                'catatan' => 'Model Random Forest belum dilatih (jalankan php artisan ml:train).',
            ];
        }

        if (! $this->modelSesuaiFitur()) {
            Log::warning('Model '.self::TARGET.' dilatih untuk daftar fitur lama; jalankan ml:train ulang.');

            return [
                'diklasifikasi' => 0,
                'model' => false,
                'catatan' => 'Model Random Forest dilatih untuk daftar fitur lama; jalankan ml:train ulang.',
            ];
        }

        $baris = PrediksiRisikoPertemuan::query()
            ->where('status_data', PrediksiRisikoService::STATUS_LENGKAP)
            ->orderBy('id')
            ->get();

        if ($baris->isEmpty()) {
            return ['diklasifikasi' => 0, 'model' => true];
        }

        $matriks = [];
        foreach ($baris as $r) {
            $sample = [];
            foreach (self::FITUR as $nama) {
                $sample[] = ((float) $r->{$nama}) / 100;
            }
            $matriks[] = $sample;
        }

        try {
            $prediksi = array_values((array) $model->predict($matriks));
        } catch (\Throwable $e) {
            Log::warning('Klasifikasi batch gagal untuk '.self::TARGET.': '.$e->getMessage());

            return [
                'diklasifikasi' => 0,
                'model' => true,
                'catatan' => 'Klasifikasi gagal: '.$e->getMessage(),
            ];
        }

        // Dikelompokkan per label supaya cukup 1 UPDATE per kelas, bukan
        // 1 UPDATE per baris (ribuan siswa x pertemuan akan sangat lambat).
        $perLabel = [];
        foreach ($prediksi as $i => $label) {
            if (! in_array($label, self::LABELS, true)) {
                continue;
            }

            $perLabel[$label][] = $baris[$i]->id;
        }

        $jumlah = 0;
        foreach ($perLabel as $label => $ids) {
            PrediksiRisikoPertemuan::whereIn('id', $ids)->update(['kategori_risiko_rf' => $label]);
            $jumlah += count($ids);
        }

        return ['diklasifikasi' => $jumlah, 'model' => true];
    }

    /**
     * @return array{0: array<int, array<int, float>>, 1: array<int, string>, 2: array<int, array<int, float>>, 3: array<int, string>}
     */
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

    protected function buildModel(): RandomForest
    {
        $model = new RandomForest(self::POHON);
        $model->setClassifer(DecisionTree::class, ['maxDepth' => self::KEDALAMAN_MAKS]);

        return $model;
    }

    /**
     * Daftar fitur saat model dilatih. Kalau tidak sama dengan `FITUR` yang
     * berlaku sekarang, vektor input akan bergeser kolom - lebih baik model
     * dianggap belum siap daripada salah klasifikasi.
     */
    protected function modelSesuaiFitur(): bool
    {
        $benchmark = $this->loadBenchmark();

        if ($benchmark === null) {
            return true;
        }

        return ($benchmark['fitur'] ?? null) === self::FITUR;
    }

    public function modelPath(): string
    {
        return $this->modelDir.'/rf_'.self::TARGET.'.ser';
    }

    public function benchmarkPath(): string
    {
        return $this->modelDir.'/benchmark_rf_'.self::TARGET.'.json';
    }

    public function loadBenchmark(): ?array
    {
        $path = $this->benchmarkPath();

        if (! Storage::exists($path)) {
            return null;
        }

        return json_decode(Storage::get($path), true);
    }

    /** Versi model terlatih yang tersimpan, untuk ditulis ke hasil prediksi. */
    public function versiModel(): ?string
    {
        return $this->loadBenchmark()['versi_model'] ?? null;
    }

    public function loadModel(): ?object
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
        $allowed[] = RandomForest::class;
        $allowed[] = DecisionTree::class;

        try {
            return unserialize($payload, [
                'allowed_classes' => array_values(array_unique($allowed)),
            ]);
        } catch (\Throwable $e) {
            Log::error('Gagal memuat model '.self::TARGET.': '.$e->getMessage());

            return null;
        }
    }
}
