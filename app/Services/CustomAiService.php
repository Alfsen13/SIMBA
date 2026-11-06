<?php

namespace App\Services;

use Generator;
use GuzzleHttp\Psr7\Utils;
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
        [$url, $headers, $payload] = $this->requestData($messages, $opts);

        try {
            $response = Http::timeout((int) ($opts['timeout'] ?? 60))
                ->connectTimeout(10)
                ->withOptions(UserAiEndpointPolicy::httpOptions($this->provider, $this->endpoint))
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
     */
    public function chatStream(array $messages, array $override = []): Generator
    {
        $opts = array_merge($this->options, $override);
        [$url, $headers, $payload] = $this->requestData($messages, $opts, true);
        $headers['Accept'] = 'text/event-stream';

        try {
            $response = Http::timeout((int) ($opts['timeout'] ?? 600))
                ->connectTimeout(10)
                ->withHeaders($headers)
                ->withOptions(array_merge(
                    UserAiEndpointPolicy::httpOptions($this->provider, $this->endpoint),
                    ['stream' => true],
                ))
                ->post($url, $payload);

            if (! $response->successful()) {
                Log::warning('Custom AI stream failed', [
                    'provider' => $this->provider,
                    'status'   => $response->status(),
                ]);
                throw new \RuntimeException('Custom AI stream failed with HTTP status ' . $response->status());
            }

            $stream = $response->getBody();
            while (! $stream->eof()) {
                $line = trim(Utils::readLine($stream));
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
                        yield $content;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('Custom AI stream exception', [
                'provider' => $this->provider,
                'error'    => $e->getMessage(),
            ]);
            throw $e;
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
     * Ambil model yang dapat diakses melalui konfigurasi provider ini.
     *
     * @return list<string>
     */
    public function listModels(): array
    {
        [$url, $headers] = match ($this->provider) {
            'ollama' => [
                rtrim($this->endpoint ?? config('services.ollama.base_url', 'http://localhost:11434'), '/') . '/api/tags',
                [],
            ],
            'gemini' => [
                'https://generativelanguage.googleapis.com/v1beta/models?pageSize=100',
                ['x-goog-api-key' => $this->apiKey ?? ''],
            ],
            'openai' => [
                'https://api.openai.com/v1/models',
                ['Authorization' => 'Bearer ' . ($this->apiKey ?? '')],
            ],
            'nvidia_nim' => [
                'https://integrate.api.nvidia.com/v1/models',
                ['Authorization' => 'Bearer ' . ($this->apiKey ?? '')],
            ],
            'anthropic' => [
                'https://api.anthropic.com/v1/models?limit=100',
                [
                    'x-api-key' => $this->apiKey ?? '',
                    'anthropic-version' => '2023-06-01',
                ],
            ],
            default => throw new \RuntimeException('Daftar model otomatis tidak didukung untuk endpoint custom.'),
        };

        $requestOptions = array_merge(
            ['allow_redirects' => false],
            UserAiEndpointPolicy::httpOptions($this->provider, $this->endpoint),
        );

        $response = Http::connectTimeout(5)
            ->timeout(20)
            ->withOptions($requestOptions)
            ->withHeaders($headers)
            ->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException('Tidak dapat memuat model. Periksa API key dan endpoint.');
        }

        $models = match ($this->provider) {
            'ollama' => collect($response->json('models', []))
                ->pluck('name'),
            'gemini' => collect($response->json('models', []))
                ->filter(fn (array $model) => in_array('generateContent', $model['supportedGenerationMethods'] ?? [], true))
                ->pluck('name')
                ->map(fn (string $name) => preg_replace('/^models\//', '', $name)),
            default => collect($response->json('data', []))
                ->pluck('id'),
        };

        $models = $models
            ->filter(fn ($model) => is_string($model) && $model !== '')
            ->unique()
            ->sort()
            ->values()
            ->all();

        if ($models === []) {
            throw new \RuntimeException('Provider tidak mengembalikan model yang dapat digunakan.');
        }

        return $models;
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
     * @return array{string, array<string, string>, array<string, mixed>}
     */
    private function requestData(array $messages, array $options, bool $stream = false): array
    {
        $headers = ['Content-Type' => 'application/json'];
        $model = $options['model'] ?? $this->model;

        if ($this->provider === 'gemini') {
            $system = [];
            $contents = [];

            foreach ($messages as $message) {
                if (($message['role'] ?? null) === 'system') {
                    $system[] = (string) ($message['content'] ?? '');

                    continue;
                }

                $contents[] = [
                    'role' => ($message['role'] ?? 'user') === 'assistant' ? 'model' : 'user',
                    'parts' => [['text' => (string) ($message['content'] ?? '')]],
                ];
            }

            $payload = ['contents' => $contents];
            if ($system !== []) {
                $payload['systemInstruction'] = [
                    'parts' => [['text' => implode("\n", $system)]],
                ];
            }
            if (isset($options['temperature'])) {
                $payload['generationConfig']['temperature'] = $options['temperature'];
            }
            if (isset($options['max_tokens'])) {
                $payload['generationConfig']['maxOutputTokens'] = $options['max_tokens'];
            }

            $url = $this->resolveEndpoint();
            if ($stream) {
                $url = str_replace(':generateContent', ':streamGenerateContent', $url) . '?alt=sse';
            }
            if ($this->apiKey) {
                $headers['x-goog-api-key'] = $this->apiKey;
            }

            return [$url, $headers, $payload];
        }

        if ($this->provider === 'anthropic') {
            $system = [];
            $conversation = [];

            foreach ($messages as $message) {
                if (($message['role'] ?? null) === 'system') {
                    $system[] = (string) ($message['content'] ?? '');

                    continue;
                }

                $conversation[] = [
                    'role' => ($message['role'] ?? 'user') === 'assistant' ? 'assistant' : 'user',
                    'content' => (string) ($message['content'] ?? ''),
                ];
            }

            $payload = [
                'model' => $model,
                'max_tokens' => $options['max_tokens'] ?? 4096,
                'messages' => $conversation,
                'stream' => $stream,
            ];
            if ($system !== []) {
                $payload['system'] = implode("\n", $system);
            }
            if (isset($options['temperature'])) {
                $payload['temperature'] = $options['temperature'];
            }

            $headers['x-api-key'] = $this->apiKey ?? '';
            $headers['anthropic-version'] = '2023-06-01';

            return [$this->resolveEndpoint(), $headers, $payload];
        }

        if ($this->apiKey) {
            $headers['Authorization'] = 'Bearer '.$this->apiKey;
        }

        return [
            $this->resolveEndpoint(),
            $headers,
            array_filter([
                'model' => $model,
                'messages' => $messages,
                'temperature' => $options['temperature'] ?? 0.7,
                'max_tokens' => $options['max_tokens'] ?? 4096,
                'stream' => $stream,
            ], fn ($value) => $value !== null),
        ];
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
            'anthropic' => ($chunk['type'] ?? null) === 'content_block_delta'
                && ($chunk['delta']['type'] ?? null) === 'text_delta'
                    ? ($chunk['delta']['text'] ?? null)
                    : null,
            'gemini' => $chunk['candidates'][0]['content']['parts'][0]['text'] ?? null,
            default => $chunk['choices'][0]['delta']['content'] ?? null,
        };
    }
}
