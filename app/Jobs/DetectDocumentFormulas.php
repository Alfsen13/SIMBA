<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Document;
use App\Models\DocumentFormulaReview;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class DetectDocumentFormulas implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, Queueable;

    /** Region selesai dipindai, tetapi anggaran waktu habis sebelum semua region terjawab. */
    public const STATUS_PARTIAL = 'partial';

    /** Waktu tersisa minimum agar satu region masih layak dikirim ke model. */
    private const SISA_MINIMUM_OCR = 5;

    public int $tries = 1;

    /** Kedua batas waktu diisi dari anggaran di konstruktor. */
    public int $timeout;

    public int $uniqueFor;

    public function __construct(public int $documentId)
    {
        $budget = self::budget();

        // Queue tersendiri supaya pemindaian tidak mengantre di belakang job soal
        // atau pertemuan; guru menekan tombol dan hasil segera dikerjakan.
        $this->onQueue('rumus');

        // Worker membunuh job sebelum halaman berhenti menunggu, dan kunci unique
        // ikut kedaluwarsa bila worker mati di tengah jalan.
        $this->timeout = $budget + 20;
        $this->uniqueFor = $budget + 20;
    }

    public static function budget(): int
    {
        return max(30, (int) config('services.pdf.formula_ocr_budget', 180));
    }

    public function uniqueId(): string
    {
        return (string) $this->documentId;
    }

    /**
     * Batas akhir pemindaian dalam detik unix. Kelas uji menggesernya untuk
     * melatih jalur anggaran habis tanpa menunggu tiga menit.
     */
    protected function tenggat(): int
    {
        return time() + self::budget();
    }

    public function handle(): void
    {
        $document = Document::find($this->documentId);
        if (! $document) {
            return;
        }

        // Anggaran dihitung sejak job diambil worker, bukan sejak tombol ditekan.
        $tenggat = $this->tenggat();

        $document->update(['formula_ocr_status' => 'processing', 'formula_ocr_error' => null]);
        $path = Storage::disk('public')->path($document->path);
        if (! is_file($path)) {
            throw new RuntimeException('File PDF tidak ditemukan.');
        }

        $command = config('services.pdf.python_path').' '
            .escapeshellarg(base_path('app/Python/detect_formula_regions.py')).' '.escapeshellarg($path);
        // Deteksi region tetap diberi waktu walau anggaran tinggal sedikit:
        // tanpanya hasil pemindaian cuma berupa kegagalan proses.
        $process = Process::fromShellCommandline($command)->setTimeout($this->sisa($tenggat, 60, 20));
        $process->mustRun();
        $regions = json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($regions)) {
            throw new RuntimeException('Hasil deteksi rumus tidak valid.');
        }

        $baseUrl = rtrim((string) config('services.ollama.base_url'), '/');
        $host = parse_url($baseUrl, PHP_URL_HOST);
        if (! in_array($host, ['localhost', '127.0.0.1', '::1', '[::1]', 'host.docker.internal'], true)) {
            throw new RuntimeException('OCR rumus hanya dapat menggunakan Ollama lokal.');
        }

        $terpotong = false;

        foreach ($regions as $region) {
            if (time() + self::SISA_MINIMUM_OCR >= $tenggat) {
                $terpotong = true;

                break;
            }

            $known = $document->formulaReviews()
                ->where('page', $region['page'])
                ->where('region_key', $region['region_key'])
                ->first();
            if ($known) {
                // Review dari pemindaian sebelum fitur ini belum punya teks
                // sumber, tanpa itu rumus tidak bisa menambal chunk aslinya.
                if (! $known->source_text && ($region['source_text'] ?? '') !== '') {
                    $known->update(['source_text' => $region['source_text']]);
                }

                continue;
            }

            $review = [
                'page' => $region['page'],
                'region_key' => $region['region_key'],
                'image_base64' => $region['image_base64'],
                'source_text' => ($region['source_text'] ?? '') !== '' ? $region['source_text'] : null,
                'status' => DocumentFormulaReview::STATUS_PENDING,
            ];

            try {
                $review['latex'] = $this->ocrLatex($baseUrl, $region['image_base64'], $this->sisa($tenggat, 90));
            } catch (Throwable $e) {
                // Satu region gagal tidak boleh membatalkan seluruh pemindaian.
                Log::warning('OCR satu region rumus gagal', [
                    'document_id' => $this->documentId,
                    'page' => $region['page'],
                    'error' => $e->getMessage(),
                ]);
                $review['latex'] = null;
                $review['ocr_error'] = 'Model gagal membaca region ini. Coba pindai ulang atau tulis manual.';
            }

            $document->formulaReviews()->create($review);

            // Detak: selama region masih masuk, pemindaian tidak dianggap macet
            // oleh halaman maupun pemicuan ulang.
            $document->touch();
        }

        $document->update([
            'formula_ocr_status' => $terpotong ? self::STATUS_PARTIAL : 'completed',
            'formula_ocr_error' => null,
        ]);
    }

    /** Sisa waktu anggaran, dibatasi $maksimal dan tidak pernah di bawah $minimal. */
    private function sisa(int $tenggat, int $maksimal, int $minimal = 1): int
    {
        return max($minimal, min($maksimal, $tenggat - time()));
    }

    private function ocrLatex(string $baseUrl, string $imageBase64, int $timeout): string
    {
        $response = Http::connectTimeout(5)->timeout($timeout)->post($baseUrl.'/api/generate', [
            'model' => config('services.pdf.formula_ocr_model'),
            'prompt' => 'Transkripsikan hanya rumus matematika yang tampak dalam gambar ini ke LaTeX. Pertahankan simbol di atas huruf: panah dan garis biasa berbeda. Jangan menebak jika tidak jelas. Berikan hanya LaTeX.',
            'images' => [$imageBase64],
            'stream' => false,
            'think' => false,
            'options' => ['temperature' => 0, 'num_predict' => 256],
        ])->throw();

        $latex = trim((string) $response->json('response'));
        $latex = preg_replace('/^```(?:latex|tex)?\s*|\s*```$/i', '', $latex);
        $latex = trim((string) $latex, " \t\n\r\0\x0B$");

        if ($latex === '' || mb_strlen($latex) > 2000) {
            throw new RuntimeException('Model tidak menghasilkan rumus yang dapat diperiksa.');
        }

        return $latex;
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Deteksi rumus dokumen gagal', [
            'document_id' => $this->documentId,
            'error' => $exception?->getMessage(),
        ]);
        Document::whereKey($this->documentId)->update([
            'formula_ocr_status' => 'failed',
            'formula_ocr_error' => 'ocr_unavailable',
        ]);
    }
}
