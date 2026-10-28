<?php

namespace App\Jobs;

use App\Services\PrediksiRisikoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunPrediksiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 180;

    public int $tries = 2;

    public function __construct()
    {
        $this->onQueue('default');
    }

     
    public function handle(PrediksiRisikoService $risiko): void
    {
        Log::info('RunPrediksiJob: mulai menghitung skor penyimpangan per-pertemuan');

        $laporan = $risiko->jalankan();

        Log::info(
            'RunPrediksiJob: selesai, {siswa} siswa, {pertemuan} baris risiko, {snapshot} snapshot',
            [
                'siswa' => $laporan['siswa'],
                'pertemuan' => $laporan['pertemuan'],
                'snapshot' => $laporan['snapshot'],
            ]
        );

        foreach ($laporan['catatan'] as $catatan) {
            Log::warning('RunPrediksiJob: {catatan}', ['catatan' => $catatan]);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('RunPrediksiJob gagal: {msg}', ['msg' => $e->getMessage()]);
    }
}

