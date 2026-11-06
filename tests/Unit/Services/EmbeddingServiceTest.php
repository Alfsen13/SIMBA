<?php

use App\Services\EmbeddingService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

it('menolak batch Ollama yang mengembalikan jumlah vektor lebih sedikit dari input', function () {
    config()->set('services.embedding.provider', 'ollama');
    Http::fake([
        '*/api/embed' => Http::response(['embeddings' => [[0.1, 0.2]]]),
    ]);

    expect(fn () => app(EmbeddingService::class)->embedBatch(['chunk satu', 'chunk dua']))
        ->toThrow(RuntimeException::class, 'Respons embedding Ollama tidak lengkap.');
});

it('menolak vektor kosong dari Ollama', function () {
    config()->set('services.embedding.provider', 'ollama');
    Http::fake([
        '*/api/embed' => Http::response(['embeddings' => [[]]]),
    ]);

    expect(fn () => app(EmbeddingService::class)->embedBatch(['chunk']))
        ->toThrow(RuntimeException::class, 'Respons embedding Ollama berisi vektor kosong atau tidak valid.');
});

it('menolak hasil batch Gemini yang kehilangan vektor', function () {
    config()->set('services.embedding.provider', 'gemini');
    config()->set('services.gemini.api_key', 'test-key');
    Http::fake([
        '*:batchEmbedContents*' => Http::response([
            'embeddings' => [
                ['values' => [0.1, 0.2]],
                [],
            ],
        ]),
    ]);

    expect(fn () => app(EmbeddingService::class)->embedBatch(['chunk satu', 'chunk dua']))
        ->toThrow(RuntimeException::class, 'Respons embedding Gemini berisi vektor kosong atau tidak valid.');
});

it('menerima embedding valid dari kedua provider', function (string $provider, array $response, string $url) {
    config()->set('services.embedding.provider', $provider);
    config()->set('services.gemini.api_key', 'test-key');
    Http::fake([$url => Http::response($response)]);

    expect(app(EmbeddingService::class)->embedBatch(['chunk']))->toBe([[0.1, 0.2]]);
})->with([
    'Ollama' => ['ollama', ['embeddings' => [[0.1, 0.2]]], '*/api/embed'],
    'Gemini' => ['gemini', ['embeddings' => [['values' => [0.1, 0.2]]]], '*:batchEmbedContents*'],
]);
