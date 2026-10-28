<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

/**
 * Snapshot dataset latih: "dasar" model ML yang dipisahkan dari database.
 *
 * Pelatihan (`ml:train`) normalnya menurunkan dataset langsung dari tabel
 * `prediksi_risiko_pertemuan` (RF) dan `fitur_siswa_history` (SVR). Kalau
 * database dibersihkan, turunan itu ikut hilang walaupun model terlatih di
 * `storage/app/private/ml/*.ser` masih ada.
 *
 * Snapshot ini menyimpan dataset mentah persis seperti yang dikembalikan
 * `KlasifikasiRisikoService::buildDataset()` dan
 * `PrediksiMendatangService::buildDataset()`, sehingga `ml:train --dataset=...`
 * bisa melatih ulang model tanpa perlu menghidupkan lagi data user fiktif di
 * database.
 *
 * Formatnya sengaja menyertakan daftar `fitur` supaya snapshot lama (dari
 * daftar fitur yang sudah berubah) ditolak, bukan menghasilkan model salah
 * kolom.
 */
class DatasetLatihService
{
    public const PATH = 'ml/dataset_latih.json';

    public function __construct(
        protected KlasifikasiRisikoService $klasifikasi,
        protected PrediksiMendatangService $mendatang,
    ) {}

    /**
     * Bangun snapshot dari data yang sedang ada di database.
     *
     * @return array<string, mixed>
     */
    public function bangun(string $sumber = 'db'): array
    {
        [$rfSamples, $rfLabels, $rfSiswa] = $this->klasifikasi->buildDataset();
        [$svrSamples, $svrTargets, $svrSiswa, $svrKelas] = $this->mendatang->buildDataset();

        return [
            'versi' => 'dataset-'.now()->format('Ymd\THis'),
            'dibuat_pada' => now()->toIso8601String(),
            'sumber' => $sumber,
            'rf' => [
                'target' => KlasifikasiRisikoService::TARGET,
                'fitur' => KlasifikasiRisikoService::FITUR,
                'label' => KlasifikasiRisikoService::LABELS,
                'sumber_label' => PrediksiRisikoService::METODE,
                'samples' => $rfSamples,
                'labels' => $rfLabels,
                'siswa_ids' => $rfSiswa,
            ],
            'svr' => [
                'target' => PrediksiMendatangService::TARGET,
                'fitur' => FiturPertemuanService::fiturModel(),
                'samples' => $svrSamples,
                'targets' => $svrTargets,
                'siswa_ids' => $svrSiswa,
                'kelas_ids' => $svrKelas,
            ],
            'ringkasan' => [
                'rf_samples' => count($rfSamples),
                'rf_siswa_unik' => count(array_unique($rfSiswa)),
                'svr_samples' => count($svrSamples),
                'svr_siswa_unik' => count(array_unique($svrSiswa)),
                'distribusi_label' => $this->distribusiLabel($rfLabels),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $dataset
     * @return string path snapshot yang ditulis
     */
    public function tulis(array $dataset, string $path = self::PATH): string
    {
        Storage::put($path, json_encode(
            $dataset,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR
        ));

        return $path;
    }

    /**
     * Bangun lalu tulis snapshot dalam satu langkah.
     *
     * @return array<string, mixed> snapshot yang ditulis
     */
    public function export(string $path = self::PATH, string $sumber = 'db'): array
    {
        $dataset = $this->bangun($sumber);
        $this->tulis($dataset, $path);

        return $dataset;
    }

    public function ada(string $path = self::PATH): bool
    {
        return Storage::exists($path);
    }

    /**
     * @return array<string, mixed>|null null kalau berkasnya tidak ada / rusak
     */
    public function muat(string $path = self::PATH): ?array
    {
        if (! Storage::exists($path)) {
            return null;
        }

        $isi = json_decode((string) Storage::get($path), true);

        return is_array($isi) ? $isi : null;
    }

    /**
     * Dataset RF dalam bentuk `[samples, labels, siswa_ids]` - sama persis
     * dengan kembalian `KlasifikasiRisikoService::buildDataset()`.
     *
     * @param  array<string, mixed>  $dataset
     * @return array{0: array<int, array<int, float>>, 1: array<int, string>, 2: array<int, int>}|null
     */
    public function datasetRf(array $dataset): ?array
    {
        $rf = $dataset['rf'] ?? null;

        if (! is_array($rf) || ! $this->sejajar($rf, ['samples', 'labels', 'siswa_ids'])) {
            return null;
        }

        return [$rf['samples'], $rf['labels'], $rf['siswa_ids']];
    }

    /**
     * Dataset SVR dalam bentuk `[samples, targets, siswa_ids, kelas_ids]`.
     *
     * @param  array<string, mixed>  $dataset
     * @return array{0: array<int, array<int, float|null>>, 1: array<int, float>, 2: array<int, int>, 3: array<int, int>}|null
     */
    public function datasetSvr(array $dataset): ?array
    {
        $svr = $dataset['svr'] ?? null;

        if (! is_array($svr) || ! $this->sejajar($svr, ['samples', 'targets', 'siswa_ids', 'kelas_ids'])) {
            return null;
        }

        return [$svr['samples'], $svr['targets'], $svr['siswa_ids'], $svr['kelas_ids']];
    }

    /**
     * Alasan snapshot tidak boleh dipakai untuk melatih model saat ini.
     * Model lama dengan urutan fitur berbeda akan salah kolom, jadi lebih baik
     * ditolak daripada diam-diam dilatih.
     *
     * @param  array<string, mixed>  $dataset
     */
    public function alasanFiturTidakCocok(array $dataset): ?string
    {
        $rf = $dataset['rf']['fitur'] ?? null;
        $svr = $dataset['svr']['fitur'] ?? null;

        if ($rf !== KlasifikasiRisikoService::FITUR) {
            return 'Fitur RF di snapshot ('.$this->daftarFitur($rf).') tidak sama dengan fitur saat ini ('
                .implode(', ', KlasifikasiRisikoService::FITUR).'). Ekspor ulang dataset.';
        }

        if ($svr !== FiturPertemuanService::fiturModel()) {
            return 'Fitur SVR di snapshot ('.$this->daftarFitur($svr).') tidak sama dengan fitur saat ini ('
                .implode(', ', FiturPertemuanService::fiturModel()).'). Ekspor ulang dataset.';
        }

        return null;
    }

    /**
     * @param  mixed  $fitur
     */
    protected function daftarFitur(mixed $fitur): string
    {
        if (! is_array($fitur)) {
            return is_scalar($fitur) ? (string) $fitur : 'tidak terbaca';
        }

        return implode(', ', array_map(fn ($f) => is_scalar($f) ? (string) $f : '?', $fitur));
    }

    /**
     * @param  array<string, mixed>  $bagian
     * @param  array<int, string>  $kunci
     */
    protected function sejajar(array $bagian, array $kunci): bool
    {
        $jumlah = null;

        foreach ($kunci as $nama) {
            if (! isset($bagian[$nama]) || ! is_array($bagian[$nama])) {
                return false;
            }

            $jumlah ??= count($bagian[$nama]);

            if (count($bagian[$nama]) !== $jumlah) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<int, string>  $labels
     * @return array<string, int>
     */
    protected function distribusiLabel(array $labels): array
    {
        $out = array_fill_keys(KlasifikasiRisikoService::LABELS, 0);

        foreach ($labels as $label) {
            if (array_key_exists($label, $out)) {
                $out[$label]++;
            }
        }

        return $out;
    }
}
