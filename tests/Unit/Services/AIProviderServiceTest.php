<?php

use App\Models\User;
use App\Models\UserAiSetting;
use App\Services\AIProviderService;
use App\Services\GeminiService;
use App\Services\OllamaService;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->geminiMock = $this->mock(GeminiService::class);
    $this->ollamaMock = $this->mock(OllamaService::class);
    $this->provider = app(AIProviderService::class);
});

test('send calls Gemini first', function () {
    $this->geminiMock
        ->shouldReceive('send')
        ->once()
        ->andReturn('Respons dari Gemini');

    $result = $this->provider->send('Halo');

    expect($result)->toBe('Respons dari Gemini');
});

test('send falls back to Ollama when Gemini fails', function () {
    $this->geminiMock
        ->shouldReceive('send')
        ->once()
        ->andThrow(new RuntimeException('Gemini error'));

    $this->ollamaMock
        ->shouldReceive('send')
        ->once()
        ->andReturn('Respons dari Ollama');

    $result = $this->provider->send('Halo');

    expect($result)->toBe('Respons dari Ollama');
});

test('send melanjutkan fallback saat provider mengembalikan respons kosong', function () {
    $models = [];

    $this->geminiMock
        ->shouldReceive('send')
        ->once()
        ->andReturn('');

    $this->ollamaMock
        ->shouldReceive('send')
        ->twice()
        ->andReturnUsing(function ($prompt, $history, $model) use (&$models) {
            $models[] = $model;

            return count($models) === 1 ? '' : 'Respons model fallback';
        });

    expect($this->provider->send('Halo'))->toBe('Respons model fallback')
        ->and($models)->toBe([
            config('services.ollama.fallback_model_1'),
            config('services.ollama.fallback_model_2'),
        ]);
});

test('send throws when all providers fail', function () {
    $this->geminiMock
        ->shouldReceive('send')
        ->once()
        ->andThrow(new RuntimeException('Gemini error'));

    $this->ollamaMock
        ->shouldReceive('send')
        ->twice()
        ->andThrow(new RuntimeException('Ollama error'));

    expect(fn () => $this->provider->send('Halo'))
        ->toThrow(RuntimeException::class, 'Semua provider AI gagal');
});

test('sendForUser falls back globally when the personal provider returns an empty answer', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://example.test/*' => Http::response([
            'choices' => [['message' => ['content' => '   ']]],
        ]),
    ]);

    $setting = new UserAiSetting;
    $setting->setRawAttributes([
        'provider' => 'custom',
        'endpoint' => 'https://example.test/v1/chat/completions',
        'model' => 'custom-model',
        'enabled' => true,
        'options' => json_encode([]),
    ]);

    $user = Mockery::mock(User::class)->makePartial();
    $user->shouldReceive('getAttribute')->with('aiSetting')->andReturn($setting);

    $this->ollamaMock
        ->shouldReceive('send')
        ->once()
        ->andReturn('Respons dari provider global');

    expect($this->provider->sendForUser($user, 'Halo'))
        ->toBe('Respons dari provider global');
});

test('sendStreamForUser yields chunks from the personal provider', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://example.test/*' => Http::response(
            "data: {\"choices\":[{\"delta\":{\"content\":\"Halo\"}}]}\n\ndata: {\"choices\":[{\"delta\":{\"content\":\"!\"}}]}\n\ndata: [DONE]\n\n"
        ),
    ]);

    $setting = new UserAiSetting;
    $setting->setRawAttributes([
        'provider' => 'custom',
        'endpoint' => 'https://example.test/v1/chat/completions',
        'model' => 'custom-model',
        'enabled' => true,
        'options' => json_encode([]),
    ]);

    $user = Mockery::mock(User::class)->makePartial();
    $user->shouldReceive('getAttribute')->with('aiSetting')->andReturn($setting);

    expect(iterator_to_array($this->provider->sendStreamForUser($user, 'Halo'), false))
        ->toBe(['Halo', '!']);
});

test('sendStreamForUser falls back globally when the personal stream fails before yielding', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://example.test/*' => Http::response([], 503),
    ]);

    $setting = new UserAiSetting;
    $setting->setRawAttributes([
        'provider' => 'custom',
        'endpoint' => 'https://example.test/v1/chat/completions',
        'model' => 'custom-model',
        'enabled' => true,
        'options' => json_encode([]),
    ]);

    $user = Mockery::mock(User::class)->makePartial();
    $user->shouldReceive('getAttribute')->with('aiSetting')->andReturn($setting);

    $this->ollamaMock
        ->shouldReceive('sendStream')
        ->once()
        ->andReturn((function () {
            yield 'Jawaban global';
        })());

    expect(iterator_to_array($this->provider->sendStreamForUser($user, 'Halo'), false))
        ->toBe(['Jawaban global']);
});

test('sendLocalPreferred calls Ollama first', function () {
    $this->ollamaMock
        ->shouldReceive('send')
        ->once()
        ->andReturn('Respons dari Ollama');

    $result = $this->provider->sendLocalPreferred('Halo');

    expect($result)->toBe('Respons dari Ollama');
});

test('sendLocalPreferred falls back to Gemini when Ollama fails', function () {
    $this->ollamaMock
        ->shouldReceive('send')
        ->twice()
        ->andThrow(new RuntimeException('Ollama gagal'));

    $this->geminiMock
        ->shouldReceive('send')
        ->once()
        ->andReturn('Respons dari Gemini');

    $result = $this->provider->sendLocalPreferred('Halo');

    expect($result)->toBe('Respons dari Gemini');
});

test('sendLocalPreferred throws when all providers fail', function () {
    $this->ollamaMock
        ->shouldReceive('send')
        ->twice()
        ->andThrow(new RuntimeException('Ollama gagal'));

    $this->geminiMock
        ->shouldReceive('send')
        ->once()
        ->andThrow(new RuntimeException('Gemini gagal'));

    expect(fn () => $this->provider->sendLocalPreferred('Halo'))
        ->toThrow(RuntimeException::class, 'Semua provider AI gagal');
});

test('system prompt AI Tutor meminta rumus ditulis dalam LaTeX', function () {
    $captured = null;

    $this->geminiMock
        ->shouldReceive('send')
        ->once()
        ->withArgs(function ($prompt, $history, $timeout) use (&$captured) {
            $captured = $history[0]['content'] ?? '';

            return true;
        })
        ->andReturn('Jawaban');

    $this->provider->send('Halo');

    expect($captured)->toContain('LaTeX');
});
