<?php

namespace App\Jobs;

use App\Models\MateriRemedial;
use App\Models\PrediksiAi;
use App\Notifications\RemedialTersedia;
use App\Services\RemedialService;
use App\Services\RemedialTugasService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GenerateRemedialAI implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, Queueable;

    public $tries = 3;

    public $timeout = 600;

    public $backoff = [30, 120];

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

        $remedialAda = MateriRemedial::where('prediksi_ai_id', $this->prediksiAiId)
            ->where('status', '!=', 'selesai')
            ->latest('id')
            ->first();

        if ($remedialAda) {
            $perluNotifikasi = empty($remedialAda->tugas_remedial);
            $remedialAda->update(['generasi_gagal_at' => null]);
            $this->pastikanTugasRemedial($remedialAda);
            if ($perluNotifikasi) {
                $remedialAda->siswa?->notify(new RemedialTersedia($remedialAda->id));
            }

            return;
        }

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
            $this->pastikanTugasRemedial($remedial);
            $remedial->siswa?->notify(new RemedialTersedia($remedial->id));
        }
    }

    private function pastikanTugasRemedial(MateriRemedial $remedial): void
    {
        if (empty($remedial->tugas_remedial)
            && ! app(RemedialTugasService::class)->generateTugas($remedial)) {
            throw new RuntimeException("Gagal membuat tugas untuk remedial {$remedial->id}.");
        }
    }

    public function failed(\Throwable $e): void
    {
        $remedial = MateriRemedial::query()
            ->where('prediksi_ai_id', $this->prediksiAiId)
            ->where('siswa_id', $this->siswaId)
            ->where('status', '!=', 'selesai')
            ->latest('id')
            ->first();

        if ($remedial && empty($remedial->tugas_remedial)) {
            $remedial->update(['generasi_gagal_at' => now()]);
        }

        Log::error('GenerateRemedialAI gagal untuk siswa {siswa_id} dan prediksi {prediksi_id}: {msg}', [
            'siswa_id' => $this->siswaId,
            'prediksi_id' => $this->prediksiAiId,
            'msg' => $e->getMessage(),
        ]);
    }
}
