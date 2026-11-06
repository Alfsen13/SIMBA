<?php

namespace App\Jobs;

use App\Models\MateriRemedial;
use App\Services\RemedialTugasService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class NilaiRemedialTugasAI implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, Queueable;

    public $tries = 2;

    public $timeout = 600;

    public $backoff = [30, 120];

    public $uniqueFor = 3600;

    public function uniqueId(): string
    {
        return (string) $this->remedialId;
    }

    public function __construct(
        public int $remedialId
    ) {}

    public function handle(RemedialTugasService $service): void
    {
        $remedial = MateriRemedial::find($this->remedialId);
        if (! $remedial) {
            return;
        }

        if ($remedial->hasil_remedial !== null || empty($remedial->jawaban_remedial)) {
            return;
        }

        if (! $service->nilai($remedial)) {
            throw new RuntimeException("Penilaian AI tidak menghasilkan hasil untuk remedial {$this->remedialId}.");
        }
    }

    public function failed(\Throwable $e): void
    {
        MateriRemedial::whereKey($this->remedialId)
            ->whereNull('hasil_remedial')
            ->update(['penilaian_gagal_at' => now()]);

        Log::error('NilaiRemedialTugasAI gagal untuk remedial {id}: {msg}', [
            'id' => $this->remedialId,
            'msg' => $e->getMessage(),
        ]);
    }
}
