<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DocumentChunk;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class EmbeddingService
{
    protected string $baseUrl;

    protected string $model;

    protected string $provider;

    public function __construct()
    {
        $this->provider = env('EMBEDDING_PROVIDER', 'ollama');
        $this->baseUrl = config('services.ollama.base_url', 'http://localhost:11434');

        if ($this->provider === 'gemini') {
            $this->model = config('services.gemini.embedding_model', 'gemini-embedding-001');
        } else {
            $this->model = config('services.ollama.embed_model', 'nomic-embed-text');
        }
    }

    public function embed(string $text): array
    {
        $text = $this->sanitizeText($text);

        if ($this->provider === 'gemini') {
            return $this->embedGemini($text);
        }

        return $this->embedOllama([$text])[0] ?? [];
    }

    public function embedBatch(array $texts, int $batchSize = 100, int $delayMs = 0): array
    {
        if (empty($texts)) {
            return [];
        }

        $texts = array_map(fn ($t) => $this->sanitizeText($t), $texts);

        if ($this->provider === 'gemini') {
            return $this->embedBatchGemini($texts, $batchSize, $delayMs);
        }

        $allEmbeddings = [];
        $chunks = array_chunk($texts, $batchSize);
        $totalBatches = count($chunks);

        foreach ($chunks as $batchIndex => $batch) {
            $embeddings = $this->embedOllama($batch);
            $allEmbeddings = array_merge($allEmbeddings, $embeddings);

            if ($batchIndex < $totalBatches - 1) {
                usleep($delayMs * 1000);
            }
        }

        return $allEmbeddings;
    }

    private function embedOllama(array $texts): array
    {
        $url = "{$this->baseUrl}/api/embed";

        try {
            $response = Http::timeout(120)->post($url, [
                'model' => $this->model,
                'input' => $texts,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Embedding Ollama: gagal: {msg}', ['msg' => $e->getMessage()]);
            throw new RuntimeException('Gagal membuat embedding Ollama: '.$e->getMessage());
        }

        if ($response->failed()) {
            throw new RuntimeException('Gagal membuat embedding Ollama: '.$response->body());
        }

        return $response->json('embeddings', []);
    }

    private function embedGemini(string $text): array
    {
        $apiKey = config('services.gemini.api_key');
        if (! $apiKey) {
            throw new RuntimeException('API key Gemini tidak dikonfigurasi.');
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:embedContent?key={$apiKey}";

        try {
            $response = Http::timeout(30)->post($url, [
                'model' => "models/{$this->model}",
                'content' => ['parts' => [['text' => $text]]],
            ]);
        } catch (\Throwable $e) {
            throw new RuntimeException('Gagal membuat embedding: '.$e->getMessage());
        }

        if ($response->failed()) {
            throw new RuntimeException('Gagal membuat embedding: '.$response->body());
        }

        return $response->json('embedding.values', []);
    }

    private function embedBatchGemini(array $texts, int $batchSize, int $delayMs): array
    {
        $apiKey = config('services.gemini.api_key');
        if (! $apiKey) {
            throw new RuntimeException('API key Gemini tidak dikonfigurasi.');
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:batchEmbedContents?key={$apiKey}";
        $allEmbeddings = [];
        $chunks = array_chunk($texts, $batchSize);
        $totalBatches = count($chunks);

        foreach ($chunks as $batchIndex => $batch) {
            $requests = [];
            foreach ($batch as $text) {
                $requests[] = [
                    'model' => "models/{$this->model}",
                    'content' => ['parts' => [['text' => $text]]],
                ];
            }

            try {
                $response = Http::timeout(60)->post($url, [
                    'requests' => $requests,
                ]);
            } catch (\Throwable $e) {
                throw new RuntimeException('Gagal membuat embedding batch: '.$e->getMessage());
            }

            if ($response->failed()) {
                throw new RuntimeException('Gagal membuat embedding batch: '.$response->body());
            }

            $embeddings = $response->json('embeddings', []);
            $allEmbeddings = array_merge($allEmbeddings, array_map(fn ($e) => $e['values'] ?? [], $embeddings));

            if ($batchIndex < $totalBatches - 1) {
                usleep($delayMs * 1000);
            }
        }

        return $allEmbeddings;
    }

    private function sanitizeText(string $text): string
    {
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);

        return $text;
    }

    public function cosineSimilarity(array $vectorA, array $vectorB): float
    {
        $dot = 0;
        $normA = 0;
        $normB = 0;

        $count = min(count($vectorA), count($vectorB));
        for ($i = 0; $i < $count; $i++) {
            $dot += $vectorA[$i] * $vectorB[$i];
            $normA += $vectorA[$i] * $vectorA[$i];
            $normB += $vectorB[$i] * $vectorB[$i];
        }

        $denom = sqrt($normA) * sqrt($normB);
        if ($denom === 0.0) {
            return 0.0;
        }

        return $dot / $denom;
    }

    public function searchChunks(string $query, int $k = 5, float $minScore = 0.4, ?string $kategori = null, ?int $materiId = null, ?int $pertemuanId = null): array
    {
        $queryEmbedding = $this->embed($query);

        $q = DocumentChunk::whereNotNull('embedding')
            ->whereRaw('JSON_LENGTH(embedding) > 0');
        if ($kategori !== null) {
            $q->where('kategori', $kategori);
        }
        if ($materiId !== null) {
            $q->whereHas('document', fn ($q) => $q->where('materi_id', $materiId));
        }
        if ($pertemuanId !== null) {
            $q->whereHas('document.materi', fn ($q) => $q->where('pertemuan_id', $pertemuanId));
        }

        $scoredChunks = [];
        $q->with('document:id,original_name,materi_id')
            ->select(['id', 'content', 'embedding', 'document_id', 'page_start', 'page_end'])
            ->chunkById(500, function ($batch) use ($queryEmbedding, $minScore, &$scoredChunks) {
                foreach ($batch as $chunk) {
                    $score = $this->cosineSimilarity($queryEmbedding, $chunk->embedding);
                    if ($score >= $minScore) {
                        $materi = $chunk->document?->materi;
                        $scoredChunks[] = [
                            'id' => $chunk->id,
                            'document_id' => $chunk->document_id,
                            'content' => $chunk->content,
                            'score' => $score,
                            'source' => $chunk->document?->original_name ?? 'Dokumen tidak dikenal',
                            'materi_id' => $materi?->id,
                            'materi_judul' => $materi?->judul,
                            'page_start' => $chunk->page_start,
                            'page_end' => $chunk->page_end,
                        ];
                    }
                }
            });

        usort($scoredChunks, fn ($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($scoredChunks, 0, $k);
    }
}
