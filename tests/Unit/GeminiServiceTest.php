<?php

use App\Services\GeminiService;
use Illuminate\Support\Facades\Http;

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
})->with([
    'LF' => "\n",
    'CRLF' => "\r\n",
]);
