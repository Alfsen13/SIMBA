<?php

namespace App\Jobs;

use App\Models\PengumpulanTugas;
use App\Services\PenilaianAIService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class PeriksaJawabanTugasAI implements ShouldQueue
{
    use Dispatchable, Queueable;

    public $tries = 3;

    public $timeout = 600;

    public $backoff = [30, 120];

    public function __construct(
        public int $pengumpulanId
    ) {}

    public function handle(PenilaianAIService $penilaianAI): void
    {
        $pengumpulan = PengumpulanTugas::find($this->pengumpulanId);
        if (! $pengumpulan) {
            return;
        }

        $penilaianAI->nilai($pengumpulan);
    }

    public function failed(\Throwable $exception): void
    {
        PengumpulanTugas::whereKey($this->pengumpulanId)
            ->update(['koreksi_ai_gagal' => true]);

        Log::error('PeriksaJawabanTugasAI gagal untuk pengumpulan {id}: {msg}', [
            'id' => $this->pengumpulanId,
            'msg' => $exception->getMessage(),
        ]);
    }
}
