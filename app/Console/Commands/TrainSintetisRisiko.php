<?php

namespace App\Console\Commands;

use App\Services\DatasetLatihService;
use App\Services\DatasetSintetisRisikoService;
use App\Services\KlasifikasiRisikoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class TrainSintetisRisiko extends Command
{
    protected $signature = 'ml:train-sintetis-risiko';

    protected $description = 'Latih Random Forest dengan data simulasi tanpa mengubah data siswa atau model SVR';

    public function handle(DatasetSintetisRisikoService $simulasi, DatasetLatihService $snapshot, KlasifikasiRisikoService $klasifikasi): int
    {
        set_time_limit(0);

        try {
            $data = $simulasi->bangun();
            $tuple = $snapshot->datasetRf($data);
            if ($tuple === null || $data['rf']['fitur'] !== KlasifikasiRisikoService::FITUR) {
                throw new RuntimeException('Format dataset sintetis tidak sesuai dengan fitur model.');
            }

            $snapshot->tulis($data, DatasetSintetisRisikoService::PATH);
            $hasil = $klasifikasi->train($tuple);
            $path = $klasifikasi->benchmarkPath();
            $benchmark = json_decode((string) Storage::get($path), true, flags: JSON_THROW_ON_ERROR);
            $benchmark['sumber_dataset'] = 'simulasi sintetis, bukan data siswa nyata';
            if (! Storage::put($path, json_encode($benchmark, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR))) {
                throw new RuntimeException('Gagal menandai sumber sintetis pada benchmark.');
            }
        } catch (Throwable $e) {
            report($e);
            $this->error('Pelatihan sintetis gagal: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('Random Forest dilatih dari simulasi; data siswa dan model SVR tidak diubah.');
        $this->line(sprintf(
            '%d sampel, %d siswa; latih %d, uji %d; akurasi %.2f%%.',
            $hasil['total_samples'], $hasil['siswa_unik'], $hasil['train_samples'],
            $hasil['test_samples'], $hasil['akurasi'] * 100,
        ));
        $this->warn('Metrik hanya mengukur peniruan aturan risiko pada simulasi, bukan akurasi pada siswa nyata.');

        return self::SUCCESS;
    }
}
