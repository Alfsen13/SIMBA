<?php

namespace App\Http\Controllers\Web\Guru;

use App\Http\Controllers\Controller;
use App\Models\UserAiSetting;
use App\Services\CustomAiService;
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

        // Jika api_key dikosongkan, jangan overwrite yang sudah ada di DB
        if (empty($validated['api_key'])) {
            unset($validated['api_key']);
        }

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

        $service = new CustomAiService(
            provider: $setting->provider,
            endpoint: $setting->endpoint,
            apiKey: $setting->api_key,
            model: $setting->model,
            options: $setting->options ?? [],
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
}
