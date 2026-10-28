<?php

namespace App\Jobs;

use App\Models\MateriRemedial;
use App\Services\RemedialTugasService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class NilaiRemedialTugasAI implements ShouldQueue
{
    use Dispatchable, Queueable;

    public int $tries = 1;

     
    public function __construct(
        public int $remedialId
    ) {}

     
    public function handle(RemedialTugasService $service): void
    {
        $remedial = MateriRemedial::find($this->remedialId);
        if (! $remedial) {
            return;
        }

        $service->nilai($remedial);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('NilaiRemedialTugasAI gagal untuk remedial {id}: {msg}', [
            'id' => $this->remedialId,
            'msg' => $e->getMessage(),
        ]);
    }
}

