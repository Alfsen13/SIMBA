<?php

namespace App\Jobs;

use App\Models\Kelas;
use App\Services\AIProviderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SaranMateriJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 90;

    public $tries = 2;

    public function __construct(
        public int $kelasId,
    ) {
        $this->onQueue('default');
    }

    public function handle(AIProviderService $aiService): void
    {
        $kelas = Kelas::findOrFail($this->kelasId);

        $kategori = $kelas->kategori ? "Kategori: {$kelas->kategori}" : 'Kategori tidak ditentukan';
        $deskripsiKelas = $kelas->deskripsi ?: '-';

        $prompt = <<<PROMPT
Kelas: {$kelas->nama_kelas}
{$kategori}
Deskripsi: {$deskripsiKelas}

Berdasarkan informasi kelas di atas, sarankan topik-topik materi yang cocok diajarkan dalam satu semester.
Berikan dalam format poin-poin sederhana (setiap baris satu topik), tanpa nomor urut atau markdown.
Minimal 8 topik, maksimal 16 topik.
PROMPT;

        $response = $aiService->sendLocalPreferred($prompt);
        $response = preg_replace('/^```(?:text)?\s*|\s*```$/', '', trim($response));

        Cache::put("saran_materi_{$this->kelasId}", $response, now()->addHours(2));

        Log::info('SaranMateriJob sukses: kelas={kelas}', ['kelas' => $kelas->nama_kelas]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SaranMateriJob gagal: kelas={kelas} msg={msg}', [
            'kelas' => $this->kelasId,
            'msg' => $e->getMessage(),
        ]);
    }
}
