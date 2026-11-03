<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Generic AI provider untuk "Bring Your Own AI".
 * Mendukung OpenAI-compatible chat/completions (OpenAI, Nvidia NIM, Ollama, custom),
 * serta format khusus Gemini dan Anthropic.
 *
 * Digunakan oleh AIProviderService::sendForUser() ketika user
 * mengkonfigurasi API key sendiri di UserAiSetting.
 */
class CustomAiService
{
    /**
     * @param  array{
     *     provider: string,
     *     endpoint?: string,
     *     apiKey?: string,
     *     model?: string,
     *     options?: array,
     * }  $config
     */
    public function __construct(
        public string $provider,
        public ?string $endpoint = null,
        public ?string $apiKey = null,
        public ?string $model = null,
        public array $options = [],
    ) {}

    /**
     * Kirim pesan chat non-stream. Return string content atau null jika gagal.
     */
    public function chat(array $messages, array $override = []): ?string
    {
        $opts = array_merge($this->options, $override);
        $url = $this->resolveEndpoint();

        $headers = ['Content-Type' => 'application/json'];
        if ($this->apiKey) {
            $headers['Authorization'] = 'Bearer ' . $this->apiKey;
        }

        $payload = array_filter([
            'model'       => $opts['model'] ?? $this->model,
            'messages'    => $messages,
            'temperature' => $opts['temperature'] ?? 0.7,
            'max_tokens'  => $opts['max_tokens'] ?? 4096,
            'stream'      => false,
        ], fn ($v) => $v !== null);

        try {
            $response = Http::timeout((int) ($opts['timeout'] ?? 60))
                ->withHeaders($headers)
                ->post($url, $payload);

            if (! $response->successful()) {
                Log::warning('Custom AI request failed', [
                    'provider' => $this->provider,
                    'status'   => $response->status(),
                    'body'     => Str::limit($response->body(), 500),
                ]);
                return null;
            }

            return $this->extractContent($response->json());
        } catch (\Throwable $e) {
            Log::error('Custom AI exception', [
                'provider' => $this->provider,
                'error'    => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Kirim pesan chat dengan streaming (untuk AI Tutor SSE).
     * Setiap chunk dipanggil melalui $onChunk.
     */
    public function chatStream(array $messages, callable $onChunk, array $override = []): void
    {
        $opts = array_merge($this->options, $override);
        $url = $this->resolveEndpoint();

        $headers = ['Content-Type' => 'application/json', 'Accept' => 'text/event-stream'];
        if ($this->apiKey) {
            $headers['Authorization'] = 'Bearer ' . $this->apiKey;
        }

        $payload = array_filter([
            'model'       => $opts['model'] ?? $this->model,
            'messages'    => $messages,
            'temperature' => $opts['temperature'] ?? 0.7,
            'max_tokens'  => $opts['max_tokens'] ?? 4096,
            'stream'      => true,
        ], fn ($v) => $v !== null);

        try {
            $response = Http::timeout((int) ($opts['timeout'] ?? 600))
                ->withHeaders($headers)
                ->post($url, $payload, ['stream' => true]);

            if (! $response->successful()) {
                Log::warning('Custom AI stream failed', [
                    'provider' => $this->provider,
                    'status'   => $response->status(),
                ]);
                return;
            }

            $stream = $response->getBody();
            $stream->rewind();
            while (! $stream->eof()) {
                $line = trim($stream->readLine());
                if ($line === '' || ! Str::startsWith($line, 'data:')) {
                    continue;
                }

                $data = trim(substr($line, 5));
                if ($data === '[DONE]') {
                    break;
                }

                $decoded = json_decode($data, true);
                if ($decoded) {
                    $content = $this->extractStreamContent($decoded, $this->provider);
                    if ($content !== null) {
                        $onChunk($content);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('Custom AI stream exception', [
                'provider' => $this->provider,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    /**
     * Tes koneksi sederhana.
     */
    public function test(): array
    {
        $result = $this->chat([
            ['role' => 'user', 'content' => 'Balas dengan satu kata: OK'],
        ]);

        if ($result !== null) {
            return ['ok' => true, 'reply' => trim($result)];
        }

        return ['ok' => false, 'error' => 'Gagal terhubung ke provider ' . $this->provider];
    }

    /**
     * Resolve endpoint URL berdasarkan provider.
     */
    private function resolveEndpoint(): string
    {
        return match ($this->provider) {
            'ollama' => rtrim($this->endpoint ?? config('services.ollama.base_url', 'http://localhost:11434'), '/') . '/api/chat',
            'gemini' => 'https://generativelanguage.googleapis.com/v1beta/models/'
                . ($this->model ?: config('services.gemini.model', 'gemini-3.8-flash'))
                . ':generateContent',
            'openai'   => 'https://api.openai.com/v1/chat/completions',
            'nvidia_nim' => 'https://integrate.api.nvidia.com/v1/chat/completions',
            'anthropic' => 'https://api.anthropic.com/v1/messages',
            'custom' => $this->endpoint ?? '',
            default => throw new \InvalidArgumentException("Unknown AI provider: {$this->provider}"),
        };
    }

    /**
     * Ekstrak teks content dari response JSON (non-stream).
     */
    private function extractContent(array $data): ?string
    {
        // OpenAI / NVIDIA NIM / Ollama-compatible
        if (isset($data['choices'][0]['message']['content'])) {
            return $data['choices'][0]['message']['content'];
        }

        // Ollama /api/chat
        if (isset($data['message']['content'])) {
            return $data['message']['content'];
        }

        // Gemini generateContent
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return $data['candidates'][0]['content']['parts'][0]['text'];
        }

        // Anthropic
        if (isset($data['content'][0]['text'])) {
            return $data['content'][0]['text'];
        }

        return null;
    }

    /**
     * Ekstrak teks dari chunk stream.
     */
    private function extractStreamContent(array $chunk, string $provider): ?string
    {
        return match ($provider) {
            'anthropic' => $chunk['delta']['text'] ?? null,
            default     => $chunk['choices'][0]['delta']['content'] ?? null,
        };
    }
}
