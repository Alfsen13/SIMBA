<?php

namespace App\Jobs;

use App\Models\PengumpulanTugas;
use App\Services\PenilaianAIService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;

class PeriksaJawabanTugasAI implements ShouldQueue
{
    use Dispatchable, Queueable;

    public $tries = 1;

     
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
}

