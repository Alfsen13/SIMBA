<?php

use App\Services\CustomAiService;
use App\Services\UserAiEndpointPolicy;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('Gemini requests use the generate content protocol', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [[
                'content' => ['parts' => [['text' => 'Jawaban Gemini']]],
            ]],
        ]),
    ]);

    $service = new CustomAiService('gemini', apiKey: 'gemini-key', model: 'gemini-model');

    expect($service->chat([
        ['role' => 'system', 'content' => 'Ikuti konteks.'],
        ['role' => 'user', 'content' => 'Pertanyaan'],
        ['role' => 'assistant', 'content' => 'Jawaban sebelumnya'],
    ]))->toBe('Jawaban Gemini');

    Http::assertSent(fn (Request $request) => $request->url() === 'https://generativelanguage.googleapis.com/v1beta/models/gemini-model:generateContent'
        && $request->hasHeader('x-goog-api-key', 'gemini-key')
        && $request['systemInstruction']['parts'][0]['text'] === 'Ikuti konteks.'
        && $request['contents'][0]['role'] === 'user'
        && $request['contents'][1]['role'] === 'model');
});

test('Anthropic requests use the messages protocol', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://api.anthropic.com/*' => Http::response([
            'content' => [['type' => 'text', 'text' => 'Jawaban Claude']],
        ]),
    ]);

    $service = new CustomAiService('anthropic', apiKey: 'anthropic-key', model: 'claude-model');

    expect($service->chat([
        ['role' => 'system', 'content' => 'Ikuti konteks.'],
        ['role' => 'user', 'content' => 'Pertanyaan'],
    ]))->toBe('Jawaban Claude');

    Http::assertSent(fn (Request $request) => $request->url() === 'https://api.anthropic.com/v1/messages'
        && $request->hasHeader('x-api-key', 'anthropic-key')
        && $request->hasHeader('anthropic-version', '2023-06-01')
        && $request['system'] === 'Ikuti konteks.'
        && $request['max_tokens'] === 4096
        && $request['messages'] === [['role' => 'user', 'content' => 'Pertanyaan']]);
});

test('Gemini and Anthropic streams yield their provider-specific text deltas', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://generativelanguage.googleapis.com/*' => Http::response(
            "data: {\"candidates\":[{\"content\":{\"parts\":[{\"text\":\"Halo Gemini\"}]}}]}\n\n"
        ),
        'https://api.anthropic.com/*' => Http::response(
            "event: content_block_delta\ndata: {\"type\":\"content_block_delta\",\"delta\":{\"type\":\"text_delta\",\"text\":\"Halo Claude\"}}\n\n"
        ),
    ]);

    $gemini = new CustomAiService('gemini', apiKey: 'gemini-key', model: 'gemini-model');
    $anthropic = new CustomAiService('anthropic', apiKey: 'anthropic-key', model: 'claude-model');

    expect(iterator_to_array($gemini->chatStream([['role' => 'user', 'content' => 'Hai']]), false))
        ->toBe(['Halo Gemini'])
        ->and(iterator_to_array($anthropic->chatStream([['role' => 'user', 'content' => 'Hai']]), false))
        ->toBe(['Halo Claude']);

    Http::assertSent(fn (Request $request) => str_contains($request->url(), ':streamGenerateContent?alt=sse')
        && $request['contents'][0]['role'] === 'user'
        && ! isset($request['stream']));
    Http::assertSent(fn (Request $request) => $request->url() === 'https://api.anthropic.com/v1/messages'
        && $request['stream'] === true);
});

test('Anthropic model discovery authenticates and returns accessible model identifiers', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://api.anthropic.com/v1/models*' => Http::response([
            'data' => [
                ['id' => 'claude-available'],
            ],
        ]),
    ]);

    $service = new CustomAiService('anthropic', apiKey: 'anthropic-key');

    expect($service->listModels())->toBe(['claude-available']);

    Http::assertSent(fn (Request $request) => $request->url() === 'https://api.anthropic.com/v1/models?limit=100'
        && $request->hasHeader('x-api-key', 'anthropic-key')
        && $request->hasHeader('anthropic-version', '2023-06-01'));
});

test('Ollama model discovery uses the configured local endpoint', function () {
    Http::preventStrayRequests();
    Http::fake([
        'http://host.docker.internal:11434/api/tags' => Http::response([
            'models' => [
                ['name' => 'qwen2.5:7b'],
                ['name' => 'llama3.2:1b'],
            ],
        ]),
    ]);

    $service = new CustomAiService('ollama', endpoint: 'http://host.docker.internal:11434');

    expect($service->listModels())->toBe(['llama3.2:1b', 'qwen2.5:7b']);

    Http::assertSent(fn (Request $request) => $request->url() === 'http://host.docker.internal:11434/api/tags');
});

test('user-configured AI endpoints cannot target private services', function (string $provider, string $endpoint) {
    Http::preventStrayRequests();

    $service = new CustomAiService($provider, endpoint: $endpoint);

    expect(fn () => UserAiEndpointPolicy::httpOptions($provider, $endpoint))
        ->toThrow(InvalidArgumentException::class);

    if ($provider === 'ollama') {
        expect(fn () => $service->listModels())->toThrow(InvalidArgumentException::class);
    } else {
        expect($service->chat([['role' => 'user', 'content' => 'Hai']]))->toBeNull();
    }

    Http::assertNothingSent();
})->with([
    'Ollama loopback' => ['ollama', 'http://127.0.0.1:11434'],
    'custom loopback' => ['custom', 'https://127.0.0.1/v1/chat/completions'],
    'custom private address' => ['custom', 'https://192.168.1.10/v1/chat/completions'],
    'custom localhost' => ['custom', 'https://localhost/v1/chat/completions'],
]);

test('Ollama endpoint policy only allows its Docker host alias on the Ollama port', function () {
    expect(UserAiEndpointPolicy::httpOptions('ollama', 'http://host.docker.internal:11434'))
        ->toBe(['allow_redirects' => false]);

    expect(fn () => UserAiEndpointPolicy::httpOptions('ollama', 'http://host.docker.internal:8080'))
        ->toThrow(InvalidArgumentException::class);
});

test('public custom endpoints use HTTPS and pin the validated destination', function () {
    $options = UserAiEndpointPolicy::httpOptions('custom', 'https://8.8.8.8/v1/chat/completions');

    expect($options['allow_redirects'])->toBeFalse()
        ->and($options['curl'][CURLOPT_RESOLVE])->toBe(['8.8.8.8:443:8.8.8.8']);
});
