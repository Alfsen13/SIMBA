<?php

namespace App\Jobs;

use App\Models\Pertemuan;
use App\Services\KuisGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\TimeoutExceededException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateSoalTugasAI implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public $timeout = 600;

    public function __construct(
        public int $userId,
        public int $pertemuanId,
        public string $tipe,
        public int $jumlah,
        public ?string $kesulitan,
        public ?string $topik,
        public array $sumber,
        public string $cacheKey,
    ) {
        $this->onQueue('ai-soal');
    }

    public function handle(KuisGeneratorService $generator): void
    {
        try {
            $pertemuan = Pertemuan::with('kelas')->findOrFail($this->pertemuanId);

            $soal = $this->tipe === 'essai'
                ? $generator->generateEssai($pertemuan, $this->jumlah, $this->topik, $this->sumber)
                : $generator->generate($pertemuan, $this->jumlah, $this->kesulitan ?? 'sedang', $this->topik, $this->sumber);

            Cache::put($this->cacheKey, [
                'status' => 'done',
                'soal' => $soal,
            ], now()->addMinutes(15));
        } catch (Throwable $e) {
            Log::error('GenerateSoalTugasAI gagal', [
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

    public function failed(Throwable $e): void
    {
        Log::error('GenerateSoalTugasAI gagal di worker', [
            'user_id' => $this->userId,
            'pertemuan_id' => $this->pertemuanId,
            'error' => $e->getMessage(),
        ]);

        Cache::put($this->cacheKey, [
            'status' => 'failed',
            'message' => $e instanceof TimeoutExceededException
                ? 'Generate soal melewati batas waktu. Silakan coba lagi.'
                : 'Gagal generate soal. Silakan coba lagi.',
        ], now()->addMinutes(15));
    }
}
