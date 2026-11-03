<?php

namespace App\Jobs;

use App\Models\Pertemuan;
use App\Services\KuisGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateSoalKuisAI implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public $timeout = 600;

    public function __construct(
        public int $userId,
        public int $pertemuanId,
        public int $jumlah,
        public string $kesulitan,
        public ?string $topik,
        public array $sumber,
        public string $cacheKey,
    ) {
        $this->onQueue('ai-soal');
    }

    public function handle(KuisGeneratorService $generator): void
    {
        set_time_limit(0);
        try {
            $pertemuan = Pertemuan::with('kelas')->findOrFail($this->pertemuanId);

            $soal = $generator->generate(
                $pertemuan,
                $this->jumlah,
                $this->kesulitan,
                $this->topik,
                $this->sumber
            );

            Cache::put($this->cacheKey, [
                'status' => 'done',
                'soal' => $soal,
            ], now()->addMinutes(15));
        } catch (Throwable $e) {
            Log::error('GenerateSoalKuisAI gagal', [
                'user_id' => $this->userId,
                'pertemuan_id' => $this->pertemuanId,
                'error' => $e->getMessage(),
            ]);

            Cache::put($this->cacheKey, [
                'status' => 'failed',
                'message' => 'Gagal generate soal. Silakan coba lagi.',
            ], now()->addMinutes(15));
        }
    }
}
