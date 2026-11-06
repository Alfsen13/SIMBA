<?php

namespace App\Jobs;

use App\Models\MateriRemedial;
use App\Services\RemedialService;
use App\Services\RemedialTugasService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GenerateRemedialPertemuanAI implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, Queueable;

    public $tries = 3;

    public $timeout = 600;

    public $backoff = [30, 120];

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
        $remedialAda = MateriRemedial::where('siswa_id', $this->siswaId)
            ->where('pertemuan_id', $this->pertemuanId)
            ->where('status', '!=', 'selesai')
            ->latest('id')
            ->first();

        if ($remedialAda) {
            $this->pastikanTugasRemedial($remedialAda);

            return;
        }

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
            $this->pastikanTugasRemedial($remedial);
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
        Log::error('GenerateRemedialPertemuanAI gagal untuk siswa {siswa_id}, pertemuan {pertemuan_id}: {msg}', [
            'siswa_id' => $this->siswaId,
            'pertemuan_id' => $this->pertemuanId,
            'msg' => $e->getMessage(),
        ]);
    }
}
