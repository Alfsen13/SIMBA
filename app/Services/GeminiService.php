<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GeminiService
{
    protected ?string $apiKey;

    protected string $endpoint;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->endpoint = config('services.gemini.endpoint', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent');
    }

    public function send(string $prompt, array $history = [], int $timeout = 120): string
    {
        if (! $this->apiKey) {
            throw new RuntimeException('Maaf, layanan AI belum dikonfigurasi.');
        }

        $contents = $this->buildContents($prompt, $history);

        try {
            $response = Http::withHeaders(['x-goog-api-key' => $this->apiKey])
                ->timeout($timeout)
                ->post($this->endpoint, [
                    'contents' => $contents,
                    'safetySettings' => [
                        ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_ONLY_HIGH'],
                        ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_ONLY_HIGH'],
                        ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_ONLY_HIGH'],
                        ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_ONLY_HIGH'],
                    ],
                ]);
        } catch (\Throwable $e) {
            throw new RuntimeException('Gagal terhubung ke Gemini: '.$e->getMessage());
        }

        if ($response->failed()) {
            $body = $response->body();
            Log::warning('Gemini HTTP error: {status} {body}', [
                'status' => $response->status(),
                'body' => mb_substr($body, 0, 500),
            ]);
            throw new RuntimeException('Maaf, terjadi kesalahan komunikasi dengan AI.');
        }

        $text = $response->json('candidates.0.content.parts.0.text');
        if ($text === null || $text === '') {
            Log::warning('Gemini: response tanpa teks', ['body' => mb_substr($response->body(), 0, 500)]);
        }

        return $text ?? 'Maaf, tidak ada respons.';
    }

    public function sendStream(string $prompt, array $history = [], int $timeout = 120): \Generator
    {
        if (! $this->apiKey) {
            throw new RuntimeException('Layanan AI belum dikonfigurasi.');
        }

        $contents = $this->buildContents($prompt, $history);

        $streamUrl = str_replace(':generateContent', ':streamGenerateContent', $this->endpoint);

        try {
            $response = Http::withOptions(['stream' => true])
                ->withHeaders(['x-goog-api-key' => $this->apiKey])
                ->timeout($timeout)
                ->post("{$streamUrl}?alt=sse", [
                    'contents' => $contents,
                    'safetySettings' => [
                        ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_NONE'],
                        ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_NONE'],
                        ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_NONE'],
                        ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_NONE'],
                    ],
                ]);
        } catch (\Throwable $e) {
            throw new RuntimeException('Gagal terhubung ke Gemini: '.$e->getMessage());
        }

        if ($response->failed()) {
            throw new RuntimeException('Gagal terhubung ke Gemini API.');
        }

        $body = $response->toPsrResponse()->getBody();
        $buffer = '';

        while (! $body->eof()) {
            $buffer .= $body->read(8192);

            while (preg_match('/\r\n\r\n|\n\n|\r\r/', $buffer, $separator, PREG_OFFSET_CAPTURE)) {
                $pos = $separator[0][1];
                $event = substr($buffer, 0, $pos);
                $buffer = substr($buffer, $pos + strlen($separator[0][0]));

                foreach (preg_split('/\r\n|\r|\n/', $event) as $line) {
                    $line = trim($line);
                    if (! str_starts_with($line, 'data: ')) {
                        continue;
                    }

                    $ssePayload = json_decode(substr($line, 6), true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        continue;
                    }

                    $text = $ssePayload['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if ($text !== null) {
                        yield $text;
                    }
                }
            }
        }
    }

    protected function buildContents(string $prompt, array $history): array
    {
        $contents = [];
        $systemText = '';

        foreach ($history as $msg) {
            if ($msg['role'] === 'system') {
                $systemText .= $msg['content']."\n";

                continue;
            }
            $contents[] = [
                'role' => $msg['role'] === 'user' ? 'user' : 'model',
                'parts' => [['text' => $msg['content']]],
            ];
        }

        $finalPrompt = $systemText ? "[Sistem: {$systemText}]\n\n{$prompt}" : $prompt;
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $finalPrompt]],
        ];

        return $contents;
    }
}
