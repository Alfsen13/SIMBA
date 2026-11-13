<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Document;
use App\Models\MateriBabSummary;
use App\Services\AIProviderService;
use App\Services\DocumentService;
use App\Services\EmbeddingService;
use App\Services\RumusPenanda;
use App\Services\SymbolService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;
use ZipArchive;

class ProcessDocument implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(public Document $document) {}

    /** Pindai rumus sesudah ingest. materi:reingest mematikannya: guru yang memilih waktu pindai. */
    public bool $deteksi = true;

    /** Sudah sampai tahap chunk lama dibuang; kegagalan sesudah itu menyisakan chunk parsial. */
    public bool $sudahMenghapus = false;

    public function handle(DocumentService $documentService, EmbeddingService $embedding): void
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        $this->document->update(['status' => 'processing']);

        try {
            $jumlah = $this->ingest($documentService, $embedding);

            $this->document->update([
                'status' => 'completed',
                'error_message' => null,
            ]);

            Log::info('ProcessDocument selesai: {name} ({count} chunks, ringkasan tersimpan)', [
                'name' => $this->document->original_name,
                'count' => $jumlah,
            ]);

            if ($this->deteksi && $this->perluDeteksi()) {
                try {
                    $this->document->update(['formula_ocr_status' => 'pending', 'formula_ocr_error' => null]);
                    DetectDocumentFormulas::dispatch($this->document->id)->afterCommit();
                } catch (Throwable $e) {
                    $this->document->update([
                        'formula_ocr_status' => 'failed',
                        'formula_ocr_error' => 'schedule_failed',
                    ]);
                    Log::warning('Gagal menjadwalkan deteksi rumus', ['document_id' => $this->document->id, 'error' => $e->getMessage()]);
                }
            }
        } catch (Throwable $e) {
            Log::error('ProcessDocument gagal: {msg}', ['msg' => $e->getMessage()]);

            try {
                $this->document->chunks()->delete();
            } catch (Throwable $cleanupException) {
                Log::critical('ProcessDocument gagal membersihkan chunk parsial untuk dokumen {id}: {msg}', [
                    'id' => $this->document->id,
                    'msg' => $cleanupException->getMessage(),
                ]);
            }

            $this->document->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Ekstrak, tanam, lalu simpan chunk dan ringkasan. Semua chunk dokumen ini
     * diganti, jadi pointer review lama dibuang lebih dulu supaya tidak ada
     * approve yang menunjuk baris yang sudah tidak ada.
     *
     * @return int jumlah chunk yang tersimpan
     */
    public function ingest(DocumentService $documentService, EmbeddingService $embedding): int
    {
        $path = Storage::disk('public')->path($this->document->path);
        if (! file_exists($path)) {
            throw new RuntimeException('File tidak ditemukan: '.$path);
        }

        $extension = strtolower(pathinfo($this->document->original_name, PATHINFO_EXTENSION));
        $chunkSize = (int) config('services.rag.chunk_size', 1000);
        $overlap = (int) config('services.rag.chunk_overlap', 200);

        $bahan = $this->bahan($documentService, $extension, $path, $chunkSize, $overlap);
        $text = $bahan['text'];
        $chunks = $bahan['chunks'];

        if (empty($chunks)) {
            throw new RuntimeException('Tidak ada teks yang bisa diekstrak dari file.');
        }

        $chunkTexts = array_map(fn ($c) => $c['content'], $chunks);
        $embeddings = [];

        $embedRetries = 0;
        while (true) {
            try {
                $embeddings = $embedding->embedBatch($chunkTexts);
                break;
            } catch (Throwable $e) {
                $embedRetries++;
                if ($embedRetries <= 3 && str_contains($e->getMessage(), '429')) {
                    Log::warning('ProcessDocument: rate limited, retry {i}/3', ['i' => $embedRetries]);
                    sleep(20 * $embedRetries);

                    continue;
                }
                Log::error('ProcessDocument: embedding gagal total: {msg}', ['msg' => $e->getMessage()]);
                throw $e;
            }
        }

        if (count($embeddings) !== count($chunks)
            || collect($embeddings)->contains(fn ($vector) => ! is_array($vector) || $vector === [])) {
            throw new RuntimeException('Hasil embedding tidak lengkap untuk seluruh chunk dokumen.');
        }

        $this->document->formulaReviews()->update([
            'document_chunk_id' => null,
            'repaired_chunk_ids' => null,
            'repaired_via_marker' => null,
        ]);
        $this->sudahMenghapus = true;
        $this->document->chunks()->delete();

        $this->simpanChunk($chunks, $embeddings);

        if ($this->document->materi_id) {
            Storage::disk('public')->put($this->sidecarPath(), RumusPenanda::manusiakan($text));
            $this->simpanRingkasan($text);
        }

        return count($chunks);
    }

    private function perluDeteksi(): bool
    {
        $extension = strtolower(pathinfo($this->document->original_name, PATHINFO_EXTENSION));

        return $extension === 'pdf' && config('services.pdf.formula_ocr_enabled');
    }

    /**
     * Ekstraksi + anotasi simbol + pemotongan, tanpa menulis apa pun. Chunk
     * tetap membawa token penanda mentah karena token itulah alamat substitusi
     * saat guru menyetujui hasil OCR.
     *
     * @return array{pages: list<array{page: int, text: string}>, text: string, chunks: list<array<string, mixed>>}
     */
    public function bahan(DocumentService $documentService, string $extension, string $path, int $chunkSize, int $overlap): array
    {
        if ($extension !== 'pdf') {
            $text = match ($extension) {
                'txt', 'md', 'csv' => file_get_contents($path),
                'docx' => self::extractFromDocx($path),
                'doc' => throw new RuntimeException('Format .doc lama tidak didukung. Konversi ke .docx atau PDF.'),
                'pptx' => self::extractFromZipXml($path, 'ppt/slides/'),
                'xlsx' => self::extractFromZipXml($path, 'xl/sharedStrings.xml', 'xl/worksheets/'),
                default => throw new RuntimeException("Tipe file {$extension} tidak didukung."),
            };

            return ['pages' => [], 'text' => $text, 'chunks' => $documentService->chunkText($text, $chunkSize, $overlap)];
        }

        $pages = $documentService->extractPagesFromPdfFile($path);
        $pages = app(SymbolService::class)->annotateFormulaPages($pages, $this->document->kategori);

        return [
            'pages' => $pages,
            'text' => implode("\n\n", array_map(fn ($p) => $p['text'], $pages)),
            'chunks' => $documentService->chunkTextWithPages($pages, $chunkSize, $overlap),
        ];
    }

    protected function sidecarPath(): string
    {
        $dir = $this->document->kategori ? 'summaries/'.$this->document->kategori : 'summaries';

        return $dir.'/materi_'.$this->document->materi_id.'.txt';
    }

    /** @param list<array<string, mixed>> $chunks @param list<array<mixed>> $embeddings */
    protected function simpanChunk(array $chunks, array $embeddings): void
    {
        foreach ($chunks as $i => $chunk) {
            $this->document->chunks()->create([
                'content' => $chunk['content'],
                'chunk_index' => $chunk['index'],
                'kategori' => $this->document->kategori,
                'embedding' => $embeddings[$i],
                'page_start' => $chunk['page_start'] ?? null,
                'page_end' => $chunk['page_end'] ?? null,
            ]);
        }
    }

    protected function simpanRingkasan(string $text): void
    {
        MateriBabSummary::updateOrCreate(
            ['materi_id' => $this->document->materi_id],
            ['ringkasan' => $this->generateSummary(RumusPenanda::manusiakan($text))],
        );
    }

    protected function generateSummary(string $text): string
    {
        if (mb_strlen($text) <= 500) {
            return $text;
        }

        try {
            $ai = app(AIProviderService::class);
            $prompt = "Buatlah ringkasan singkat (maksimal 3 paragraf) dari teks materi pembelajaran berikut. Gunakan bahasa yang jelas dan mudah dipahami.\n\nTeks:\n".mb_substr($text, 0, 8000);
            $summary = $ai->sendLocalPreferred($prompt, [], 'Anda adalah asisten yang membuat ringkasan materi pembelajaran.');

            if (mb_strlen($summary) > 10) {
                return $summary;
            }
        } catch (Throwable $e) {
            Log::warning('ProcessDocument: AI summary gagal, fallback ke teks awal: {msg}', ['msg' => $e->getMessage()]);
        }

        return mb_substr($text, 0, 1000);
    }

    public static function extractFromDocx(string $path): string
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Gagal membuka file DOCX.');
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            throw new RuntimeException('Tidak dapat membaca konten DOCX.');
        }

        $text = strip_tags($xml);
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }

    public static function extractFromZipXml(string $path, string ...$dirs): string
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new RuntimeException('Gagal membuka file.');
        }

        $text = '';

        foreach ($dirs as $dir) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if ($name === false || ! str_starts_with($name, $dir)) {
                    continue;
                }
                $xml = $zip->getFromIndex($i);
                if ($xml !== false) {
                    $text .= ' '.strip_tags($xml);
                }
            }
        }

        $zip->close();

        if (empty(trim($text))) {
            throw new RuntimeException('Tidak dapat mengekstrak teks dari file.');
        }

        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text);
    }
}
