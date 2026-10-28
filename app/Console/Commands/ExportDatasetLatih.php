<?php

namespace App\Console\Commands;

use App\Services\DatasetLatihService;
use Illuminate\Console\Command;
use Throwable;

class ExportDatasetLatih extends Command
{
    protected $signature = 'ml:dataset-export
                            {--path= : Lokasi snapshot (relatif ke disk default), default ml/dataset_latih.json}';

    protected $description = 'Tulis snapshot dataset latih (RF + SVR) dari data yang ada di database ke berkas JSON, supaya model bisa dilatih ulang lewat `ml:train --dataset` tanpa menghidupkan lagi data user di database';

    public function handle(DatasetLatihService $dataset): int
    {
        $path = $this->option('path') ?: DatasetLatihService::PATH;

        try {
            $hasil = $dataset->export($path, 'ml:dataset-export');
        } catch (Throwable $e) {
            $this->error('Gagal menulis snapshot: '.$e->getMessage());

            return Command::FAILURE;
        }

        $ringkasan = $hasil['ringkasan'] ?? [];

        $this->table(
            ['Bagian', 'Baris', 'Siswa unik'],
            [
                ['RF (kategori risiko)', $ringkasan['rf_samples'] ?? 0, $ringkasan['rf_siswa_unik'] ?? 0],
                ['SVR (nilai pertemuan berikutnya)', $ringkasan['svr_samples'] ?? 0, $ringkasan['svr_siswa_unik'] ?? 0],
            ],
        );

        if (($ringkasan['rf_samples'] ?? 0) === 0 && ($ringkasan['svr_samples'] ?? 0) === 0) {
            $this->warn('Tidak ada data latih di database: snapshot ditulis kosong.');

            return Command::SUCCESS;
        }

        $this->line('Distribusi label: '.json_encode($ringkasan['distribusi_label'] ?? []));
        $this->line('Versi snapshot: '.($hasil['versi'] ?? '-'));
        $this->info('Snapshot dataset ditulis: '.$path);
        $this->line('Latih ulang dari snapshot: php artisan ml:train --dataset='.$path);

        return Command::SUCCESS;
    }
}
