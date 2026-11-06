<?php

namespace App\Services;

use App\Models\User;
use App\Support\Bahasa;
use App\Services\CustomAiService;
use Generator;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AIProviderService
{
    protected GeminiService $gemini;

    protected OllamaService $ollama;

    protected string $primaryModel;

    protected string $fallbackModel1;

    protected string $fallbackModel2;

    protected string $systemPrompt = 'Anda adalah asisten AI pendidikan bernama SIMBA. Tugas Anda adalah membantu siswa belajar dengan menjawab pertanyaan tentang materi pembelajaran. Anda HANYA boleh menjawab berdasarkan dokumen pembelajaran yang diberikan dalam konteks. JANGAN PERNAH membuat, menebak, atau menambahkan materi atau konten yang tidak ada dalam dokumen. Jika dokumen tidak memiliki informasi yang cukup untuk menjawab pertanyaan, katakan dengan jujur bahwa materi tersebut belum tersedia atau belum diunggah. Jangan mencoba menjawab dengan pengetahuan umum Anda. Jawablah dengan jelas, ramah, dan mendidik. Tuliskan semua rumus atau ekspresi matematika/ilmiah memakai notasi LaTeX dengan pembatas $...$ untuk rumus inline dan $$...$$ untuk rumus blok, agar dapat ditampilkan dengan benar.';

    public function __construct()
    {
        $this->gemini = app(GeminiService::class);
        $this->ollama = app(OllamaService::class);
        $this->primaryModel = config('services.ollama.model', 'qwen2.5:7b');
        $this->fallbackModel1 = config('services.ollama.fallback_model_1', 'llama3.2:1b');
        $this->fallbackModel2 = config('services.ollama.fallback_model_2', 'qwen2.5:7b');
    }

    protected array $refusalPatterns = [
        '/tidak bisa menjawab/i',
        '/tidak dapat menjawab/i',
        '/coba tanyakan/i',
        '/mohon maaf.*tidak/i',
        '/di luar kemampuan/i',
        '/tidak memiliki informasi/i',
        '/tidak dirancang/i',
        '/tidak ada respons/i',
    ];

     
    public function send(string $prompt, array $history = [], ?string $systemPrompt = null): string
    {
        $geminiTimeout = min((int) config('services.gemini.short_timeout', 8), 15);

        $aiOutput = $this->attemptWithFallback(fn ($model) => match ($model) {
            'gemini' => $this->gemini->send($prompt, $this->prepareHistory($history, $systemPrompt), $geminiTimeout),
            default => $this->ollama->send($prompt, $this->prepareHistory($history, $systemPrompt), $model, 600),
        });

        if (empty(trim($aiOutput))) {
            throw new RuntimeException('AI mengembalikan respons kosong di semua provider.');
        }

        if ($this->isRefusal($aiOutput)) {
            try {
                $aiOutput = $this->retryWithForcedPrompt($prompt, $history, $systemPrompt);
            } catch (\Throwable $e) {
                Log::warning('AIProvider: retry gagal total: {msg}', ['msg' => $e->getMessage()]);
                throw new RuntimeException('Maaf, layanan AI tidak tersedia saat ini. Silakan coba lagi.');
            }

            if ($this->isRefusal($aiOutput)) {
                Log::warning('AIProvider: retry masih refusal: {resp}', ['resp' => mb_substr($aiOutput, 0, 100)]);
                throw new RuntimeException('Maaf, layanan AI tidak tersedia saat ini. Silakan coba lagi.');
            }
        }

        return $aiOutput;
    }

     
    protected function isRefusal(string $response): bool
    {
        foreach ($this->refusalPatterns as $pattern) {
            if (preg_match($pattern, $response)) {
                Log::warning('AIProvider: refusal terdeteksi, retry dengan prompt paksa.', [
                    'pattern' => $pattern,
                    'response' => mb_substr($response, 0, 100),
                ]);

                return true;
            }
        }

        return false;
    }

     
    protected function retryWithForcedPrompt(string $originalPrompt, array $history, ?string $systemPrompt = null): string
    {
        $forcedPrompt = "PENTING: Jawab pertanyaan berikut berdasarkan dokumen yang diberikan. Jika dokumen tidak mencakup topik ini, katakan bahwa materi belum tersedia. Jangan membuat jawaban sendiri.\n\nPertanyaan: {$originalPrompt}";
        $forcedSystem = ($systemPrompt ?? $this->systemPrompt).' Anda HARUS menjawab berdasarkan dokumen saja. Jangan menolak, tapi juga jangan membuat materi yang tidak ada dalam dokumen.';

        $prepared = $this->prepareHistory($history, $forcedSystem);
        $prepared[0]['content'] = $forcedSystem;

        $geminiTimeout = min((int) config('services.gemini.short_timeout', 8), 15);

        try {
            return $this->gemini->send($forcedPrompt, $prepared, $geminiTimeout);
        } catch (\Throwable $e) {
            Log::warning('AIProvider: Gemini retry gagal, coba Ollama: {msg}', ['msg' => $e->getMessage()]);
        }

        try {
            return $this->ollama->send($forcedPrompt, $prepared, $this->fallbackModel1, 600);
        } catch (\Throwable $e) {
            Log::warning('AIProvider: Ollama retry gagal: {msg}', ['msg' => $e->getMessage()]);
            throw $e;
        }
    }

     
    public function sendStream(string $prompt, array $history = [], ?string $systemPrompt = null): Generator
    {
        $geminiTimeout = (int) config('services.gemini.short_timeout', 8);

        yield from $this->untilSuccess([
            fn () => $this->gemini->sendStream($prompt, $this->prepareHistory($history, $systemPrompt), $geminiTimeout),
            fn () => app(OllamaService::class)->sendStream($prompt, $this->prepareHistory($history, $systemPrompt), $this->fallbackModel1),
            fn () => app(OllamaService::class)->sendStream($prompt, $this->prepareHistory($history, $systemPrompt), $this->fallbackModel2),
        ]);
    }

     
    public function sendLocalPreferred(string $prompt, array $history = [], ?string $systemPrompt = null): string
    {
        $historyWithSystem = $this->prepareHistory($history, $systemPrompt);
        $geminiTimeout = min((int) config('services.gemini.short_timeout', 8), 15);

        // Ollama primary
        try {
            $out = $this->ollama->send($prompt, $historyWithSystem, $this->primaryModel, 600);
            if (! empty(trim($out)) && ! $this->isRefusal($out)) {
                return $out;
            }
            if ($this->isRefusal($out)) {
                Log::warning('Ollama ({model}) refusal, try next.', ['model' => $this->primaryModel, 'response' => mb_substr($out, 0, 100)]);
            }
        } catch (\Throwable $e) {
            Log::warning('Ollama ({model}) gagal: {msg}', ['model' => $this->primaryModel, 'msg' => $e->getMessage()]);
        }

        // Ollama fallback 1
        try {
            $out = $this->ollama->send($prompt, $historyWithSystem, $this->fallbackModel1, 600);
            if (! empty(trim($out)) && ! $this->isRefusal($out)) {
                return $out;
            }
            if ($this->isRefusal($out)) {
                Log::warning('Ollama ({model}) refusal, try next.', ['model' => $this->fallbackModel1, 'response' => mb_substr($out, 0, 100)]);
            }
        } catch (\Throwable $e) {
            Log::warning('Ollama ({model}) gagal: {msg}', ['model' => $this->fallbackModel1, 'msg' => $e->getMessage()]);
        }

        // Gemini (cloud)
        try {
            $out = $this->gemini->send($prompt, $historyWithSystem, $geminiTimeout);
            if (! empty(trim($out)) && ! $this->isRefusal($out)) {
                return $out;
            }
        } catch (\Throwable $e) {
            Log::warning('Gemini juga gagal: {msg}', ['msg' => $e->getMessage()]);
        }

        // Last resort: forced prompt (Gemini then Ollama fallback 1)
        try {
            $out = $this->retryWithForcedPrompt($prompt, $history, $systemPrompt);
            if (! empty(trim($out)) && ! $this->isRefusal($out)) {
                return $out;
            }
        } catch (\Throwable $e) {
            Log::warning('AIProvider: retry gagal total: {msg}', ['msg' => $e->getMessage()]);
        }

        throw new RuntimeException('Maaf, layanan AI tidak tersedia saat ini. Silakan coba lagi.');
    }

     
    public function sendStreamLocalPreferred(string $prompt, array $history = [], ?string $systemPrompt = null): Generator
    {
        $historyWithSystem = $this->prepareHistory($history, $systemPrompt);
        $geminiTimeout = (int) config('services.gemini.short_timeout', 8);

        yield from $this->untilSuccess([
            fn () => app(OllamaService::class)->sendStream($prompt, $historyWithSystem, $this->primaryModel),
            fn () => app(OllamaService::class)->sendStream($prompt, $historyWithSystem, $this->fallbackModel1),
            fn () => $this->gemini->sendStream($prompt, $historyWithSystem, $geminiTimeout),
        ]);
    }

     
    public function sendOllama(string $prompt, array $history = [], string $model = 'qwen2.5-coder:1.5b-base'): string
    {
        return $this->ollama->send($prompt, $this->prepareHistory($history), $model, 600);
    }

     
    protected function attemptWithFallback(callable $callable): string
    {
        $exceptions = [];

        try {
            return $callable('gemini');
        } catch (\Throwable $e) {
            $exceptions[] = $e->getMessage();
        }

        try {
            return $callable($this->fallbackModel1);
        } catch (\Throwable $e) {
            $exceptions[] = $e->getMessage();
        }

        try {
            return $callable($this->fallbackModel2);
        } catch (\Throwable $e) {
            $exceptions[] = $e->getMessage();
        }

        $detail = implode(' | ', array_unique($exceptions));
        throw new RuntimeException("Semua provider AI gagal. {$detail}");
    }

     
    protected function untilSuccess(array $generators): Generator
    {
        $indexes = ['Ollama (primary)', 'Ollama (fallback)', 'Gemini'];

        foreach ($generators as $i => $generator) {
            try {
                yield from $generator();

                return;
            } catch (\Throwable $e) {
                Log::warning('AI Provider {idx} gagal: {msg}', [
                    'idx' => $indexes[$i] ?? "#{$i}",
                    'msg' => $e->getMessage(),
                ]);

                continue;
            }
        }
    }

     
    protected function prepareHistory(array $history, ?string $systemPrompt = null): array
    {
        $prompt = trim(($systemPrompt ?? $this->systemPrompt).' '.Bahasa::instruksi());
        array_unshift($history, [
            'role' => 'system',
            'content' => $prompt,
        ]);

        return $history;
    }

    /**
     * Kirim chat menggunakan AI pribadi user (Bring Your Own AI).
     * Jika user tidak punya setting enabled atau gagal, fallback ke provider global.
     */
    public function sendForUser(User $user, string $prompt, array $history = [], ?string $systemPrompt = null): string
    {
        $setting = $user->aiSetting;

        if ($setting && $setting->enabled && $setting->isReady()) {
            $custom = new CustomAiService(
                provider: $setting->provider,
                endpoint: $setting->endpoint,
                apiKey: $setting->api_key,
                model: $setting->model,
                options: $setting->options ?? [],
            );

            $result = $custom->chat(
                array_merge($this->prepareHistory($history, $systemPrompt), [['role' => 'user', 'content' => $prompt]])
            );

            if ($result !== null && trim($result) !== '') {
                return $result;
            }

            Log::warning('AIProvider: user AI failed, fallback global', [
                'user_id' => $user->id,
                'provider' => $setting->provider,
            ]);
        }

        return $this->sendLocalPreferred($prompt, $history, $systemPrompt);
    }

    /**
     * Stream chat menggunakan AI pribadi user. Fallback ke global jika gagal.
     */
    public function sendStreamForUser(User $user, string $prompt, array $history = [], ?string $systemPrompt = null): Generator
    {
        $setting = $user->aiSetting;

        if ($setting && $setting->enabled && $setting->isReady()) {
            $custom = new CustomAiService(
                provider: $setting->provider,
                endpoint: $setting->endpoint,
                apiKey: $setting->api_key,
                model: $setting->model,
                options: $setting->options ?? [],
            );

            $hasOutput = false;

            try {
                foreach ($custom->chatStream(
                    array_merge($this->prepareHistory($history, $systemPrompt), [['role' => 'user', 'content' => $prompt]])
                ) as $chunk) {
                    $hasOutput = true;
                    yield $chunk;
                }

                if ($hasOutput) {
                    return;
                }
            } catch (\Throwable $e) {
                Log::warning('AIProvider: user stream failed, fallback global', [
                    'user_id' => $user->id,
                    'provider' => $setting->provider,
                    'error' => $e->getMessage(),
                ]);

                if ($hasOutput) {
                    throw $e;
                }
            }
        }

        yield from $this->sendStreamLocalPreferred($prompt, $history, $systemPrompt);
    }
}
