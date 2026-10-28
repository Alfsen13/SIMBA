<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Document;
use App\Models\MateriBabSummary;
use App\Services\AIProviderService;
use App\Services\DocumentService;
use App\Services\EmbeddingService;
use App\Services\SymbolService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

class ProcessDocument implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(public Document $document) {}

    public function handle(DocumentService $documentService, EmbeddingService $embedding): void
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        $this->document->update(['status' => 'processing']);

        try {
            $path = Storage::disk('public')->path($this->document->path);
            if (! file_exists($path)) {
                throw new RuntimeException('File tidak ditemukan: '.$path);
            }

            $extension = strtolower(pathinfo($this->document->original_name, PATHINFO_EXTENSION));
            $chunkSize = (int) config('services.rag.chunk_size', 1000);
            $overlap = (int) config('services.rag.chunk_overlap', 200);

            if ($extension === 'pdf') {
                $pages = $documentService->extractPagesFromPdfFile($path);
                $pages = app(SymbolService::class)->annotateFormulaPages($pages, $this->document->kategori);
                $text = implode("\n\n", array_map(fn ($p) => $p['text'], $pages));
                $chunks = $documentService->chunkTextWithPages($pages, $chunkSize, $overlap);
            } else {
                $text = match ($extension) {
                    'txt', 'md', 'csv' => file_get_contents($path),
                    'docx' => self::extractFromDocx($path),
                    'doc' => throw new RuntimeException('Format .doc lama tidak didukung. Konversi ke .docx atau PDF.'),
                    'pptx' => self::extractFromZipXml($path, 'ppt/slides/'),
                    'xlsx' => self::extractFromZipXml($path, 'xl/sharedStrings.xml', 'xl/worksheets/'),
                    default => throw new RuntimeException("Tipe file {$extension} tidak didukung."),
                };
                $chunks = $documentService->chunkText($text, $chunkSize, $overlap);
            }

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
                } catch (\Throwable $e) {
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

            foreach ($chunks as $i => $chunk) {
                $this->document->chunks()->create([
                    'content' => $chunk['content'],
                    'chunk_index' => $chunk['index'],
                    'kategori' => $this->document->kategori,
                    'embedding' => $embeddings[$i] ?? [],
                    'page_start' => $chunk['page_start'] ?? null,
                    'page_end' => $chunk['page_end'] ?? null,
                ]);
            }

            if ($this->document->materi_id) {
                $kategoriDir = $this->document->kategori ? 'summaries/'.$this->document->kategori : 'summaries';
                $textPath = $kategoriDir.'/materi_'.$this->document->materi_id.'.txt';
                Storage::disk('public')->put($textPath, $text);

                $ringkasan = $this->generateSummary($text);
                MateriBabSummary::updateOrCreate(
                    ['materi_id' => $this->document->materi_id],
                    ['ringkasan' => $ringkasan],
                );
            }

            $this->document->update([
                'status' => 'completed',
                'error_message' => null,
            ]);

            Log::info('ProcessDocument selesai: {name} ({count} chunks, ringkasan tersimpan)', [
                'name' => $this->document->original_name,
                'count' => count($chunks),
            ]);
        } catch (\Throwable $e) {
            Log::error('ProcessDocument gagal: {msg}', ['msg' => $e->getMessage()]);
            $this->document->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
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
        } catch (\Throwable $e) {
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
