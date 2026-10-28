<?php

use App\Services\AIProviderService;
use App\Services\GeminiService;
use App\Services\OllamaService;

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
