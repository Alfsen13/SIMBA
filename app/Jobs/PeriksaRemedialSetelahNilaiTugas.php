<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Tugas;
use App\Services\RemedialService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class PeriksaRemedialSetelahNilaiTugas implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, Queueable;

    public $tries = 2;

    public $timeout = 600;

    public $backoff = [5, 30];

    public $uniqueFor = 3600;

    public function __construct(public int $tugasId) {}

    public function uniqueId(): string
    {
        return (string) $this->tugasId;
    }

    public function handle(RemedialService $remedial): void
    {
        $tugas = Tugas::find($this->tugasId);

        if ($tugas) {
            $remedial->periksaSetelahNilaiTugas($tugas);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('PeriksaRemedialSetelahNilaiTugas gagal untuk tugas {id}: {msg}', [
            'id' => $this->tugasId,
            'msg' => $e->getMessage(),
        ]);
    }
}
