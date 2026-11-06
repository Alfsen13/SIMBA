<?php

namespace App\Http\Controllers\Web\Guru;

use App\Http\Controllers\Controller;
use App\Models\UserAiSetting;
use App\Services\CustomAiService;
use App\Services\UserAiEndpointPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiSettingController extends Controller
{
    /**
     * Halaman pengaturan AI pribadi (Bring Your Own AI).
     */
    public function edit()
    {
        $setting = auth()->user()->aiSetting ?? new UserAiSetting();

        return view('guru.ai-settings.edit', [
            'setting' => $setting,
        ]);
    }

    /**
     * Simpan konfigurasi AI pribadi user.
     */
    public function update(Request $r)
    {
        $validated = $r->validate([
            'provider' => 'required|in:ollama,gemini,nvidia_nim,openai,anthropic,custom',
            'endpoint' => 'nullable|url|required_if:provider,custom,ollama',
            'api_key'  => 'nullable|string|max:1000',
            'model'    => 'nullable|string|max:100|required',
            'options.temperature' => 'nullable|numeric|between:0,2',
            'options.max_tokens'  => 'nullable|integer|min:1|max:32768',
            'options.timeout'     => 'nullable|integer|min:5|max:3600',
            'enabled' => 'boolean',
        ]);

        $user = auth()->user();
        $currentSetting = $user->aiSetting;
        $provider = $validated['provider'];
        $providerChanged = $currentSetting !== null && $currentSetting->provider !== $provider;
        try {
            UserAiEndpointPolicy::httpOptions($provider, $validated['endpoint'] ?? null);
        } catch (\InvalidArgumentException $e) {
            return back()
                ->withErrors(['endpoint' => $e->getMessage()])
                ->withInput();
        }

        $apiKey = $validated['api_key'] ?? (
            $currentSetting?->provider === $provider ? $currentSetting->api_key : null
        );

        if ($r->boolean('enabled') && $provider !== 'ollama' && $provider !== 'custom' && ! $apiKey) {
            return back()
                ->withErrors(['api_key' => 'Masukkan API key provider ini terlebih dahulu.'])
                ->withInput();
        }

        if ($r->boolean('enabled') && $provider !== 'custom') {
            try {
                $models = $this->makeAiService($provider, $validated['endpoint'] ?? null, $apiKey, $validated['model'])
                    ->listModels();
            } catch (\Throwable $e) {
                Log::warning('AI model validation failed', [
                    'user_id' => $user->id,
                    'provider' => $provider,
                    'exception' => $e::class,
                ]);

                return back()
                    ->withErrors(['api_key' => 'Validasi provider gagal. Periksa API key atau endpoint Ollama.'])
                    ->withInput();
            }

            if (! in_array($validated['model'], $models, true)) {
                return back()
                    ->withErrors(['model' => 'Pilih model dari daftar model provider yang tersedia.'])
                    ->withInput();
            }
        }

        // Jika api_key dikosongkan, jangan overwrite yang sudah ada di DB
        if (empty($validated['api_key'])) {
            if ($providerChanged) {
                $validated['api_key'] = null;
            } else {
                unset($validated['api_key']);
            }
        }

        $validated['enabled'] = $r->boolean('enabled');

        auth()->user()->aiSetting()->updateOrCreate(
            ['user_id' => auth()->id()],
            $validated
        );

        return back()->with('success', 'Pengaturan AI disimpan.');
    }

    /**
     * Tes koneksi ke provider AI user.
     */
    public function test(Request $r)
    {
        $setting = auth()->user()->aiSetting;

        if (! $setting) {
            return response()->json([
                'ok' => false,
                'error' => 'Belum dikonfigurasi. Simpan pengaturan terlebih dahulu.',
            ], 422);
        }

        if (! $setting->model) {
            return response()->json([
                'ok' => false,
                'error' => 'Model harus diisi.',
            ], 422);
        }

        $service = $this->makeAiService(
            $setting->provider,
            $setting->endpoint,
            $setting->api_key,
            $setting->model,
            $setting->options ?? [],
        );

        $result = $service->test();

        if ($result['ok']) {
            $setting->update(['tested_at' => now()]);
            Log::info('AI setting test berhasil', [
                'user_id' => auth()->id(),
                'provider' => $setting->provider,
                'model' => $setting->model,
            ]);

            return response()->json($result);
        }

        Log::warning('AI setting test gagal', [
            'user_id' => auth()->id(),
            'provider' => $setting->provider,
            'error' => $result['error'],
        ]);

        return response()->json($result, 422);
    }

    /**
     * Muat dan validasi daftar model dari provider pilihan user.
     */
    public function models(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|in:ollama,gemini,nvidia_nim,openai,anthropic,custom',
            'endpoint' => 'nullable|url|required_if:provider,ollama',
            'api_key' => 'nullable|string|max:1000',
        ]);

        if ($validated['provider'] === 'custom') {
            return response()->json([
                'ok' => false,
                'error' => 'Endpoint custom tidak menyediakan format daftar model standar. Masukkan model secara manual.',
            ], 422);
        }

        try {
            UserAiEndpointPolicy::httpOptions($validated['provider'], $validated['endpoint'] ?? null);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'ok' => false,
                'error' => $e->getMessage(),
            ], 422);
        }

        $setting = auth()->user()->aiSetting;
        $provider = $validated['provider'];
        $apiKey = filled($validated['api_key'] ?? null)
            ? trim($validated['api_key'])
            : ($setting?->provider === $provider ? $setting->api_key : null);

        if ($provider !== 'ollama' && ! $apiKey) {
            return response()->json([
                'ok' => false,
                'error' => 'Masukkan API key untuk memvalidasi dan memuat model.',
            ], 422);
        }

        try {
            $models = $this->makeAiService($provider, $validated['endpoint'] ?? null, $apiKey)
                ->listModels();
        } catch (\Throwable $e) {
            Log::warning('AI model listing failed', [
                'user_id' => auth()->id(),
                'provider' => $provider,
                'exception' => $e::class,
            ]);

            return response()->json([
                'ok' => false,
                'error' => 'Gagal memvalidasi provider. Periksa API key atau endpoint Ollama.',
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'models' => $models,
        ]);
    }

    private function makeAiService(
        string $provider,
        ?string $endpoint,
        ?string $apiKey,
        ?string $model = null,
        array $options = [],
    ): CustomAiService {
        return new CustomAiService(
            provider: $provider,
            endpoint: $endpoint,
            apiKey: $apiKey,
            model: $model,
            options: $options,
        );
    }
}
