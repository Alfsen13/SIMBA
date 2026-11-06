<?php

use App\Services\EmbeddingService;
use Illuminate\Http\Client\Request;
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

it('mengirim API key Gemini melalui header untuk embedding tunggal dan batch', function () {
    config()->set('services.embedding.provider', 'gemini');
    config()->set('services.gemini.api_key', 'test-key');
    Http::fake([
        '*:embedContent' => Http::response(['embedding' => ['values' => [0.1, 0.2]]]),
        '*:batchEmbedContents' => Http::response(['embeddings' => [['values' => [0.1, 0.2]]]]),
    ]);

    $service = app(EmbeddingService::class);

    expect($service->embed('teks'))->toBe([0.1, 0.2])
        ->and($service->embedBatch(['teks']))->toBe([[0.1, 0.2]]);

    Http::assertSent(fn (Request $request) => $request->url() === 'https://generativelanguage.googleapis.com/v1beta/models/gemini-embedding-001:embedContent'
        && $request->hasHeader('x-goog-api-key', 'test-key'));
    Http::assertSent(fn (Request $request) => $request->url() === 'https://generativelanguage.googleapis.com/v1beta/models/gemini-embedding-001:batchEmbedContents'
        && $request->hasHeader('x-goog-api-key', 'test-key'));
});

it('menolak perbandingan cosine untuk dimensi embedding yang berbeda', function () {
    expect(fn () => app(EmbeddingService::class)->cosineSimilarity([1.0, 0.0], [1.0, 0.0, 10.0]))
        ->toThrow(InvalidArgumentException::class, 'Embedding harus memiliki dimensi yang sama.');
});
