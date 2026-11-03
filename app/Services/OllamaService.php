<?php

namespace App\Services;

use RuntimeException;

class OllamaService
{
    protected string $baseUrl;

    protected string $model;

    public function __construct()
    {
        $this->baseUrl = config('services.ollama.base_url', 'http://localhost:11434');
        $this->model = config('services.ollama.model', 'llama3');
    }

     
    public function isAvailable(): bool
    {
        $url = $this->baseUrl.'/api/tags';

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 3,
                'ignore_errors' => true,
            ],
        ]);

        $result = @file_get_contents($url, false, $context);

        return is_string($result) && trim($result) !== '';
    }

     
    public function send(string $prompt, array $history = [], ?string $model = null, int $timeout = 1200, ?int $numPredict = null): string
    {
        $numPredict = $numPredict ?? (int) config('services.ollama.num_predict', 2048);
        $model = $model ?? $this->model;
        $messages = $this->buildMessages($prompt, $history);

        $payload = json_encode([
            'model' => $model,
            'messages' => $messages,
            'stream' => false,
            'options' => $this->buildOptions($numPredict),
        ]);

        $result = $this->httpPost('/api/chat', $payload, $timeout);

        $data = json_decode($result, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Gagal parse respons Ollama.');
        }

        return $data['message']['content'] ?? 'Maaf, tidak ada respons.';
    }

     
    protected function buildOptions(int $numPredict): array
    {
        return [
            'temperature' => (float) config('services.ollama.temperature', 0.2),
            'num_ctx' => (int) config('services.ollama.num_ctx', 16384),
            'num_predict' => $numPredict,
        ];
    }

     
    protected function httpPost(string $path, string $payload, int $timeout): string
    {
        $url = $this->baseUrl.$path;

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $payload,
                'timeout' => $timeout,
                'ignore_errors' => true,
            ],
        ]);

        $result = @file_get_contents($url, false, $context);

        if ($result === false) {
            $err = error_get_last();
            $msg = $err['message'] ?? 'Unknown error';
            throw new RuntimeException('Gagal terhubung ke Ollama: '.$msg);
        }

        return $result;
    }

     
    public function sendStream(string $prompt, array $history = [], ?string $model = null, ?int $numPredict = null): \Generator
    {
        $numPredict = $numPredict ?? (int) config('services.ollama.num_predict', 2048);
        $model = $model ?? $this->model;
        $messages = $this->buildMessages($prompt, $history);

        $payload = json_encode([
            'model' => $model,
            'messages' => $messages,
            'stream' => true,
            'options' => $this->buildOptions($numPredict),
        ]);

        $url = $this->baseUrl.'/api/chat';
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $payload,
                'timeout' => 600,
                'ignore_errors' => true,
            ],
        ]);

        $fp = @fopen($url, 'r', false, $context);
        if ($fp === false) {
            $err = error_get_last();
            throw new RuntimeException('Gagal terhubung ke Ollama: '.($err['message'] ?? 'Unknown error'));
        }

        $responseHeaders = function_exists('http_get_last_response_headers')
            ? http_get_last_response_headers()
            : ($http_response_header ?? []);

        $httpCode = 200;
        foreach ($responseHeaders as $h) {
            if (preg_match('/^HTTP\/\d+\.?\d*\s+(\d+)\s/', $h, $m)) {
                $httpCode = (int) $m[1];
                break;
            }
        }

        if ($httpCode >= 400) {
            $body = stream_get_contents($fp);
            fclose($fp);
            throw new RuntimeException('Ollama API error: '.mb_substr($body, 0, 200));
        }

        stream_set_timeout($fp, 1200);

        while (! feof($fp)) {
            $line = fgets($fp);
            if ($line === false) {
                if (feof($fp)) {
                    break;
                }
                $meta = stream_get_meta_data($fp);
                if (! empty($meta['timed_out'])) {
                    fclose($fp);
                    throw new RuntimeException('Ollama streaming timeout (membaca respons).');
                }
                break;
            }

            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $data = json_decode($line, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                continue;
            }

            if (isset($data['error'])) {
                fclose($fp);
                throw new RuntimeException('Ollama error: '.$data['error']);
            }

            if (isset($data['message']['content'])) {
                yield $data['message']['content'];
            }

            if (! empty($data['done'])) {
                break;
            }
        }

        fclose($fp);
    }

     
    protected function buildMessages(string $prompt, array $history): array
    {
        $messages = [];
        foreach ($history as $msg) {
            $messages[] = [
                'role' => $msg['role'] ?? 'user',
                'content' => $msg['content'] ?? '',
            ];
        }
        $messages[] = ['role' => 'user', 'content' => $prompt];

        return $messages;
    }
}

