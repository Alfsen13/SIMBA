<?php

use App\Services\GeminiService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('sends the Gemini API key in a header instead of the request URL', function () {
    config([
        'services.gemini.api_key' => 'test-key',
        'services.gemini.endpoint' => 'https://example.test/v1beta/models/test:generateContent',
    ]);

    Http::preventStrayRequests();
    Http::fake([
        'example.test/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'Jawaban']]]]],
        ]),
    ]);

    expect(app(GeminiService::class)->send('Pertanyaan'))->toBe('Jawaban');

    Http::assertSent(fn (Request $request) => $request->url() === 'https://example.test/v1beta/models/test:generateContent'
        && $request->hasHeader('x-goog-api-key', 'test-key'));
});

it('parses Gemini stream events separated by LF and CRLF', function (string $separator) {
    config([
        'services.gemini.api_key' => 'test-key',
        'services.gemini.endpoint' => 'https://example.test/v1beta/models/test:generateContent',
    ]);

    $event = fn (string $text): string => 'data: '.json_encode([
        'candidates' => [
            ['content' => ['parts' => [['text' => $text]]]],
        ],
    ], JSON_THROW_ON_ERROR);

    Http::preventStrayRequests();
    Http::fake([
        'example.test/*' => Http::response(
            $event('potongan 1').$separator.$separator.$event('potongan 2').$separator.$separator,
            200,
            ['Content-Type' => 'text/event-stream'],
        ),
    ]);

    $hasil = iterator_to_array(app(GeminiService::class)->sendStream('Pertanyaan'));

    expect($hasil)->toBe(['potongan 1', 'potongan 2']);

    Http::assertSent(fn (Request $request) => $request->url() === 'https://example.test/v1beta/models/test:streamGenerateContent?alt=sse'
        && $request->hasHeader('x-goog-api-key', 'test-key'));
})->with([
    'LF' => "\n",
    'CRLF' => "\r\n",
]);
