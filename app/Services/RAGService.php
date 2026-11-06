<?php

declare(strict_types=1);

namespace App\Services;

class RAGService
{
    protected EmbeddingService $embedding;

    protected int $topK;

    protected float $minScore;

     
    protected array $lastReferences = [];

    public function __construct()
    {
        $this->embedding = app(EmbeddingService::class);
        $this->topK = (int) config('services.rag.top_k', 5);
        $this->minScore = (float) config('services.rag.min_score', 0.4);
    }

    public function getLastReferences(): array
    {
        return $this->lastReferences;
    }

    public function retrieve(string $query, ?int $k = null, ?float $minScore = null, ?string $kategori = null, ?int $materiId = null, ?int $pertemuanId = null): array
    {
        $k = $k ?? $this->topK;
        $minScore = $minScore ?? $this->minScore;

        return $this->embedding->searchChunks($query, $k, $minScore, $kategori, $materiId, $pertemuanId);
    }

    public function buildContext(string $query, ?string $kategori = null, ?int $materiId = null, ?int $pertemuanId = null): string
    {
        $chunks = $this->retrieve($query, null, null, $kategori, $materiId, $pertemuanId);

        if (empty($chunks)) {
            return '';
        }

        $refMap = [];
        $refIndex = 0;
        $pageRanges = [];
        foreach ($chunks as $chunk) {
            $docId = $chunk['document_id'] ?? null;
            if ($docId && ! isset($refMap[$docId])) {
                $refIndex++;
                $refMap[$docId] = $refIndex;
            }
            if ($docId) {
                $ps = $chunk['page_start'] ?? null;
                $pe = $chunk['page_end'] ?? null;
                if ($ps !== null) {
                    if (! isset($pageRanges[$docId])) {
                        $pageRanges[$docId] = ['min' => $ps, 'max' => $pe ?? $ps];
                    } else {
                        $pageRanges[$docId]['min'] = min($pageRanges[$docId]['min'], $ps);
                        $pageRanges[$docId]['max'] = max($pageRanges[$docId]['max'], $pe ?? $ps);
                    }
                }
            }
        }

        $refSources = [];
        $context = "Berikut adalah informasi relevan dari dokumen pembelajaran:\n\n";

        foreach ($chunks as $i => $chunk) {
            $docId = $chunk['document_id'] ?? null;
            $refNum = $docId ? ($refMap[$docId] ?? ($refIndex + 1)) : ($refIndex + 1);

            if ($docId && ! isset($refSources[$docId])) {
                $materiJudul = $chunk['materi_judul'] ?? null;
                $docName = $chunk['source'] ?? 'Dokumen';
                $title = $materiJudul ?: $docName;
                if (isset($pageRanges[$docId])) {
                    $pr = $pageRanges[$docId];
                    $pageLabel = $pr['min'] === $pr['max'] ? "hlm. {$pr['min']}" : "hlm. {$pr['min']}-{$pr['max']}";
                    $title .= ", {$pageLabel}";
                }
                $refSources[$docId] = $title;
            }

            $refLabel = '['.($refSources[$docId] ?? "Sumber {$refNum}").']';
            $pageInfo = '';
            if (isset($chunk['page_start']) && $chunk['page_start'] !== null) {
                $ps = $chunk['page_start'];
                $pe = $chunk['page_end'] ?? $ps;
                $pageInfo = $ps === $pe ? " (hlm. {$ps})" : " (hlm. {$ps}-{$pe})";
            }
            $context .= "{$refLabel} ".$chunk['content']."{$pageInfo}\n\n";
        }

        $this->lastReferences = [];
        foreach ($refSources as $docId => $label) {
            $num = $refMap[$docId];
            $docChunks = array_filter($chunks, fn ($c) => ($c['document_id'] ?? null) === $docId);
            $firstChunk = reset($docChunks) ?: ($chunks[0] ?? []);
            $chunkContents = array_values(array_map(fn ($c) => $c['content'], $docChunks));

            $this->lastReferences[] = [
                'id' => $num,
                'judul' => $label,
                'document_id' => $docId,
                'materi_id' => $firstChunk['materi_id'] ?? null,
                'chunks' => $chunkContents,
            ];
        }

        $context .= "\nInstruksi: Jawab pertanyaan berdasarkan konteks di atas. Saat merujuk dokumen pembelajaran, WAJIB gunakan format: Menurut sumber yang dibagikan: [[Judul Materi]]. Contoh: Menurut sumber yang dibagikan: [[Hukum Hidrostatistika dan Pengapian]], tekanan pada fluida... Tanda [[ ]] akan dikonversi otomatis menjadi link oleh sistem. JANGAN gunakan format [Sumber N], (Referensi N), atau (Sumber N) dalam jawaban.\n\n";

        return $context;
    }

    public function isContextAvailable(string $query): bool
    {
        $chunks = $this->retrieve($query);

        return ! empty($chunks);
    }
}
