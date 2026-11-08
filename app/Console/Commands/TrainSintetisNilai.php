<?php

namespace App\Console\Commands;

use App\Services\DatasetLatihService;
use App\Services\DatasetSintetisNilaiService;
use App\Services\PrediksiMendatangService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class TrainSintetisNilai extends Command
{
    protected $signature = 'ml:train-sintetis-nilai';

    protected $description = 'Latih prediksi nilai dari tujuh pola simulasi tanpa mengubah data siswa atau model risiko';

    public function handle(DatasetSintetisNilaiService $simulasi, DatasetLatihService $snapshot, PrediksiMendatangService $prediksi): int
    {
        set_time_limit(0);

        try {
            $data = $simulasi->bangun();
            $tuple = $snapshot->datasetSvr($data);
            if ($tuple === null || $snapshot->alasanFiturTidakCocok($data) !== null) {
                throw new RuntimeException('Format dataset sintetis tidak sesuai dengan fitur model.');
            }

            $snapshot->tulis($data, DatasetSintetisNilaiService::PATH);
            $hasil = $prediksi->train($tuple);
            $path = 'ml/benchmark_'.PrediksiMendatangService::TARGET.'.json';
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

        $model = $hasil['models'][PrediksiMendatangService::TARGET];
        $this->info('Model SVR dilatih dari simulasi; data siswa dan model risiko tidak diubah.');
        $this->line(sprintf(
            '%d pasangan, %d siswa; latih %d, uji %d; MAE %.2f, RMSE %.2f.',
            $hasil['total_samples'], $hasil['siswa_unik'], $hasil['train_samples'],
            $hasil['test_samples'], $model['mae'], $model['rmse'],
        ));
        $this->warn('Metrik hanya berlaku pada data simulasi, bukan bukti akurasi pada siswa nyata.');

        return self::SUCCESS;
    }
}
