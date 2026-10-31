<?php

namespace App\Jobs;

use App\Models\PrediksiAi;
use App\Services\RemedialService;
use App\Services\RemedialTugasService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateRemedialAI implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, Queueable;

    public $tries = 1;

    public $timeout = 600;

    public $uniqueFor = 3600;

    public function uniqueId(): string
    {
        return 'remedial-global-'.$this->siswaId;
    }

     
    public function __construct(
        public int $siswaId,
        public int $prediksiAiId
    ) {}

     
    public function handle(RemedialService $remedialService): void
    {
        $prediksi = PrediksiAi::find($this->prediksiAiId);
        if (! $prediksi || $prediksi->hasil !== 'berisiko_tinggi') {
            return;
        }







        $alasan = $remedialService->alasanDilewati($prediksi);

        if ($alasan !== null) {
            Log::info('GenerateRemedialAI dilewati', [
                'siswa_id' => $this->siswaId,
                'alasan' => $alasan,
            ]);

            return;
        }

        $remedial = $remedialService->generateForPrediksi($prediksi);

        if ($remedial) {
            app(RemedialTugasService::class)->generateTugas($remedial);
        }
    }
}

