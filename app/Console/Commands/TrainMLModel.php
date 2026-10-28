<?php

namespace App\Console\Commands;

use App\Services\DatasetLatihService;
use App\Services\KlasifikasiRisikoService;
use App\Services\MLService;
use App\Services\PrediksiMendatangService;
use Illuminate\Console\Command;
use Throwable;

class TrainMLModel extends Command
{
    protected $signature = 'ml:train
                            {--dataset= : Latih dari snapshot JSON (mis. ml/dataset_latih.json) alih-alih dari database}
                            {--skip-regression : Jangan latih SVR (regresi nilai pertemuan berikutnya)}
                            {--skip-klasifikasi : Jangan latih Random Forest (klasifikasi risiko)}';

    protected $description = 'Hitung 4 parameter akademik + snapshot fitur per-pertemuan, lengkapi nilai aktual ramalan, lalu latih SVR kernel linear (MAE/RMSE/R2) dan Random Forest (akurasi/precision/recall/F1 + confusion matrix)';

    public function handle(
        MLService $mlService,
        PrediksiMendatangService $mendatang,
        KlasifikasiRisikoService $klasifikasi,
    ): int {
        set_time_limit(0);

        $dataset = $this->datasetLatih();

        if ($this->option('dataset') !== null && $dataset === null) {
            return Command::FAILURE;
        }

        $this->info('Menghitung 4 parameter akademik...');
        $hasil = $mlService->computeAllAcademicFeatures();
        $this->info('Parameter dihitung untuk '.count($hasil).' siswa.');

        $snapshot = $mlService->backfillFiturPertemuan();
        $this->info('Snapshot fitur per-pertemuan: '.$snapshot.' baris.');

        // Nilai aktual pertemuan target baru muncul setelah pertemuan berjalan.
        $dilengkapi = $mendatang->sinkronkanNilaiAktual();
        $this->info('Nilai aktual ramalan dilengkapi: '.$dilengkapi.' baris.');

        if (! $this->option('skip-regression')) {
            $this->latihRegresi($mendatang, $dataset['svr'] ?? null);
        }

        if (! $this->option('skip-klasifikasi')) {
            $this->latihKlasifikasi($klasifikasi, $dataset['rf'] ?? null);
        }

        return Command::SUCCESS;
    }

    /**
     * Snapshot dataset latih kalau `--dataset=...` diberikan, sudah dalam bentuk
     * yang diterima kedua service (tuple posisional seperti `buildDataset()`).
     * `null` berarti perilaku biasa: dataset diturunkan dari database.
     *
     * @return array{rf: array<int, mixed>, svr: array<int, mixed>}|null null juga saat snapshot tidak valid (sudah dilaporkan)
     */
    protected function datasetLatih(): ?array
    {
        $path = $this->option('dataset');

        if ($path === null) {
            return null;
        }

        $service = app(DatasetLatihService::class);
        $dataset = $service->muat($path);

        if ($dataset === null) {
            $this->error('Snapshot dataset tidak ditemukan atau tidak bisa dibaca: '.$path);

            return null;
        }

        $alasan = $service->alasanFiturTidakCocok($dataset);
        if ($alasan !== null) {
            $this->error($alasan.' Ekspor ulang dataset dulu.');

            return null;
        }

        $rf = $service->datasetRf($dataset);
        $svr = $service->datasetSvr($dataset);

        if ($rf === null || $svr === null) {
            $this->error('Snapshot '.$path.' tidak memuat dataset RF/SVR yang lengkap.');

            return null;
        }

        $ringkasan = $dataset['ringkasan'] ?? [];
        $this->info('Melatih dari snapshot dataset: '.$path);
        $this->line(sprintf(
            '  Snapshot %s - RF %d baris (%d siswa), SVR %d pasangan (%d siswa).',
            $dataset['versi'] ?? '?',
            $ringkasan['rf_samples'] ?? count($rf[0]),
            $ringkasan['rf_siswa_unik'] ?? count(array_unique($rf[2])),
            $ringkasan['svr_samples'] ?? count($svr[0]),
            $ringkasan['svr_siswa_unik'] ?? count(array_unique($svr[2])),
        ));

        return ['rf' => $rf, 'svr' => $svr];
    }

    protected function latihRegresi(PrediksiMendatangService $mendatang, ?array $dataset = null): void
    {
        $this->line('');
        $this->info($dataset === null
            ? 'Melatih SVR (regresi nilai pertemuan berikutnya)...'
            : 'Melatih SVR dari snapshot dataset...');

        try {
            $laporan = $mendatang->train($dataset);
        } catch (Throwable $e) {
            $this->warn('Regresi dilewati: '.$e->getMessage());

            return;
        }

        $this->info($laporan['message'] ?? 'Model regresi dilatih.');
        $this->line(sprintf(
            '  Sampel: %d total, %d latih, %d uji, %d siswa unik.',
            $laporan['total_samples'] ?? 0,
            $laporan['train_samples'] ?? 0,
            $laporan['test_samples'] ?? 0,
            $laporan['siswa_unik'] ?? 0,
        ));

        foreach ($laporan['models'] ?? [] as $target => $model) {
            if (isset($model['error'])) {
                $this->warn('  '.$target.': '.$model['error']);

                continue;
            }

            $this->line(sprintf(
                '  %s: MAE %s, RMSE %s, R2 %s.',
                $target,
                isset($model['mae']) ? number_format((float) $model['mae'], 2) : 'n/a',
                isset($model['rmse']) ? number_format((float) $model['rmse'], 2) : 'n/a',
                isset($model['r2']) && $model['r2'] !== null ? number_format((float) $model['r2'], 4) : 'n/a',
            ));

            $this->line('  '.($model['catatan'] ?? ''));

            if (! empty($model['benchmark'])) {
                $this->table(
                    ['Kandidat', 'MAE', 'RMSE', 'R2', 'Status'],
                    $this->barisBenchmark($target, $model)
                );
            }
        }
    }

    protected function latihKlasifikasi(KlasifikasiRisikoService $klasifikasi, ?array $dataset = null): void
    {
        $this->line('');
        $this->info($dataset === null
            ? 'Melatih Random Forest (klasifikasi risiko)...'
            : 'Melatih Random Forest dari snapshot dataset...');

        try {
            $laporan = $klasifikasi->train($dataset);
        } catch (Throwable $e) {
            $this->warn('Klasifikasi dilewati: '.$e->getMessage());

            return;
        }

        $this->line(sprintf(
            '  Sampel: %d total, %d latih, %d uji, %d siswa unik.',
            $laporan['total_samples'] ?? 0,
            $laporan['train_samples'] ?? 0,
            $laporan['test_samples'] ?? 0,
            $laporan['siswa_unik'] ?? 0,
        ));

        if (($laporan['test_samples'] ?? 0) > 0) {
            $this->line(sprintf(
                '  Akurasi %.2f%%, precision %.2f%%, recall %.2f%%, F1 %.2f%%.',
                (float) $laporan['akurasi'] * 100,
                (float) $laporan['precision_macro'] * 100,
                (float) $laporan['recall_macro'] * 100,
                (float) $laporan['f1_macro'] * 100,
            ));
            $this->line('  Confusion matrix (baris = aktual, kolom = prediksi, urutan: '
                .implode(' | ', KlasifikasiRisikoService::LABELS).'):');

            foreach ($laporan['confusion_matrix'] ?? [] as $aktual => $kolom) {
                $this->line('    '.str_pad((string) $aktual, 16).implode('  ', array_map(
                    fn ($v) => str_pad((string) $v, 4),
                    array_values($kolom)
                )));
            }
        }

        if (isset($laporan['catatan'])) {
            $this->warn('  '.$laporan['catatan']);
        }

        // Model siap dipakai: tempelkan hasil klasifikasi ke tiap baris riwayat.
        $terpasang = $klasifikasi->klasifikasiSemua();

        if (($terpasang['diklasifikasi'] ?? 0) > 0) {
            $this->info('  Kategori RF ditulis ke '.$terpasang['diklasifikasi'].' baris riwayat.');
        } elseif (isset($terpasang['catatan'])) {
            $this->warn('  '.$terpasang['catatan']);
        }
    }

    protected function barisBenchmark(string $target, array $m): array
    {
        $baris = [];

        foreach (PrediksiMendatangService::KANDIDAT as $kunci => $nama) {
            $b = $m['benchmark'][$kunci] ?? null;

            if ($b === null) {
                $baris[] = [$nama, '-', '-', '-', 'tidak diuji'];

                continue;
            }

            if (isset($b['error'])) {
                $baris[] = [$nama, '-', '-', '-', 'gagal: '.$b['error']];

                continue;
            }

            $baris[] = [
                $nama,
                number_format((float) $b['mae'], 2),
                number_format((float) $b['rmse'], 2),
                $b['r2'] === null ? 'n/a' : number_format((float) $b['r2'], 4),
                $kunci === ($m['pemenang'] ?? null) ? 'DIPAKAI' : '',
            ];
        }

        return $baris;
    }
}
