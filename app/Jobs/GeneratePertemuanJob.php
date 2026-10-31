<?php

namespace App\Jobs;

use App\Models\Kelas;
use App\Models\Pertemuan;
use App\Notifications\GeneratePertemuanGagal;
use App\Services\AIProviderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GeneratePertemuanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;

    public $tries = 2;

    public function __construct(
        public int $kelasId,
        public string $materiTopics,
        public int $jumlahPertemuan,
        public ?string $catatan = null,
    ) {
        $this->onQueue('pertemuan');
    }

    public function handle(AIProviderService $aiService): int
    {
        $kelas = Kelas::findOrFail($this->kelasId);

        $kategori = $kelas->kategori ? "Kategori: {$kelas->kategori}" : 'Kategori tidak ditentukan';
        $deskripsiKelas = $kelas->deskripsi ?: '-';
        $catatan = $this->catatan ?: '-';

        $prompt = <<<PROMPT
Kelas: {$kelas->nama_kelas}
{$kategori}
Deskripsi: {$deskripsiKelas}

Materi yang akan diajarkan:
{$this->materiTopics}

Jumlah pertemuan yang diinginkan: {$this->jumlahPertemuan}

Catatan tambahan:
{$catatan}

Buatlah daftar pertemuan (RPP/silabus) sebanyak {$this->jumlahPertemuan} pertemuan berdasarkan informasi di atas.
Format JSON array of objects dengan key:
- nama_pertemuan (string)
- deskripsi (string)
- urutan (integer, mulai dari 1)

Berikan hanya JSON array, tanpa teks lain.
PROMPT;

        $response = $aiService->sendLocalPreferred($prompt);

        $pertemuanList = $this->extractJsonArray($response);

        if (empty($pertemuanList) || ! isset($pertemuanList[0]['nama_pertemuan'])) {
            throw new \RuntimeException('Respons AI tidak valid: '.substr($response, 0, 200));
        }

        $now = now();
        $records = [];
        foreach ($pertemuanList as $item) {
            $records[] = [
                'kelas_id' => $kelas->id,
                'nama_pertemuan' => $item['nama_pertemuan'],
                'urutan' => $item['urutan'] ?? 1,
                'deskripsi' => $item['deskripsi'] ?? '',

                'dibuka' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Pertemuan::insert($records);

        Log::info('GeneratePertemuanJob sukses: {kelas} ({count} pertemuan)', [
            'kelas' => $kelas->nama_kelas,
            'count' => count($records),
        ]);

        return count($records);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('GeneratePertemuanJob gagal: kelas={kelas} msg={msg}', [
            'kelas' => $this->kelasId,
            'msg' => $e->getMessage(),
        ]);

        try {
            $kelas = Kelas::find($this->kelasId);
            if ($kelas && $kelas->guru_id) {
                $guru = $kelas->guru;
                $guru?->notify(new GeneratePertemuanGagal($kelas->nama_kelas, $e->getMessage(), app()->getLocale()));
            }
        } catch (\Throwable $notifE) {
            Log::warning('GeneratePertemuanJob: gagal kirim notifikasi: {msg}', ['msg' => $notifE->getMessage()]);
        }
    }

     
    protected function extractJsonArray(string $response): ?array
    {
        $clean = trim($response);
        $clean = preg_replace('/^```(?:json)?\s*|\s*```$/', '', $clean);

        $decoded = json_decode($clean, true, 512, JSON_THROW_ON_ERROR);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\[.*\]/s', $clean, $m)) {
            $decoded = json_decode($m[0], true, 512, JSON_THROW_ON_ERROR);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        throw new \RuntimeException('Respons AI bukan JSON array: '.substr($response, 0, 200));
    }
}

