<?php

namespace App\Jobs;

use App\Services\KlasifikasiRisikoService;
use App\Services\PrediksiMendatangService;
use App\Services\PrediksiRisikoService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RunPrediksiJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, Queueable;

    public const STATUS_CACHE_KEY = 'prediksi:risiko:status';

    public $timeout = 600;

    public $tries = 3;

    public $backoff = [30, 120];

    public $uniqueFor = 3600;

    public function __construct()
    {
        $this->onQueue('default');
    }

    public function uniqueId(): string
    {
        return 'global';
    }

    public function handle(
        PrediksiRisikoService $risiko,
        KlasifikasiRisikoService $klasifikasi,
        PrediksiMendatangService $mendatang
    ): void {
        Cache::put(self::STATUS_CACHE_KEY, ['status' => 'processing'], now()->addHours(2));
        Log::info('RunPrediksiJob: mulai menghitung skor penyimpangan per-pertemuan');

        $laporan = $risiko->jalankan();
        $hasilKlasifikasi = $klasifikasi->klasifikasiSemua();
        $nilaiAktual = $mendatang->sinkronkanNilaiAktual();

        Cache::put(self::STATUS_CACHE_KEY, [
            'status' => 'completed',
            'laporan' => $laporan,
            'klasifikasi' => $hasilKlasifikasi,
            'nilai_aktual' => $nilaiAktual,
        ], now()->addHours(2));

        Log::info('RunPrediksiJob: selesai, {siswa} siswa, {pertemuan} baris risiko', [
            'siswa' => $laporan['siswa'],
            'pertemuan' => $laporan['pertemuan'],
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Cache::put(self::STATUS_CACHE_KEY, [
            'status' => 'failed',
            'message' => $e->getMessage(),
        ], now()->addHours(2));

        Log::error('RunPrediksiJob gagal: {msg}', ['msg' => $e->getMessage()]);
    }
}
