<?php

namespace App\Jobs;

use App\Services\RemedialService;
use App\Services\RemedialTugasService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateRemedialPertemuanAI implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, Queueable;

    public $tries = 1;

    public $timeout = 600;

    /**
     * Kunci keunikan mengikat lock sampai job selesai, sehingga klik "Jalankan
     * Prediksi" berulang tidak menumpuk job untuk (siswa, pertemuan) yang sama.
     * Kedaluwarsa 1 jam supaya lock tidak nyangkut bila worker mati mendadak.
     */
    public $uniqueFor = 3600;

    public function uniqueId(): string
    {
        return $this->siswaId.'-'.$this->pertemuanId;
    }

    public function __construct(
        public int $siswaId,
        public int $pertemuanId,
        public ?float $skorPertemuan = null
    ) {}

    public function handle(RemedialService $remedialService): void
    {
        // Guard kedua: tombol guru bisa menekan dua kali sebelum baris remedial
        // pertama sempat ada, jadi cek idempotensi juga dilakukan di sini.
        $alasan = $remedialService->alasanDilewatiPertemuan($this->siswaId, $this->pertemuanId);

        if ($alasan !== null) {
            Log::info('GenerateRemedialPertemuanAI dilewati', [
                'siswa_id' => $this->siswaId,
                'pertemuan_id' => $this->pertemuanId,
                'alasan' => $alasan,
            ]);

            return;
        }

        $remedial = $remedialService->generateForPertemuan(
            $this->siswaId,
            $this->pertemuanId,
            $this->skorPertemuan
        );

        if ($remedial) {
            app(RemedialTugasService::class)->generateTugas($remedial);
        }
    }
}
