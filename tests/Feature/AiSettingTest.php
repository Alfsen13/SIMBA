<?php

use App\Models\User;
use App\Models\UserAiSetting;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('pengajar tetap dapat menonaktifkan AI ketika provider gagal atau API key tidak tersedia', function () {
    Http::preventStrayRequests();

    $pengajar = User::factory()->pengajar()->verified()->create();
    $setting = UserAiSetting::create([
        'user_id' => $pengajar->id,
        'provider' => 'openai',
        'api_key' => 'openai-secret',
        'model' => 'gpt-test',
        'enabled' => true,
    ]);

    $this->actingAs($pengajar)
        ->put(route('pengajar.ai-settings.update'), [
            'provider' => 'openai',
            'model' => 'gpt-test',
            'enabled' => '0',
        ])
        ->assertRedirect();

    expect($setting->refresh()->enabled)->toBeFalse()
        ->and($setting->api_key)->toBe('openai-secret');
});

test('mengganti provider tanpa API key baru menghapus key provider sebelumnya', function () {
    $pengajar = User::factory()->pengajar()->verified()->create();
    $setting = UserAiSetting::create([
        'user_id' => $pengajar->id,
        'provider' => 'openai',
        'api_key' => 'openai-secret',
        'model' => 'gpt-test',
        'enabled' => true,
    ]);

    $this->actingAs($pengajar)
        ->put(route('pengajar.ai-settings.update'), [
            'provider' => 'gemini',
            'model' => 'gemini-test',
            'enabled' => '0',
        ])
        ->assertRedirect();

    expect($setting->refresh()->provider)->toBe('gemini')
        ->and($setting->api_key)->toBeNull();
});

test('pengajar dapat memvalidasi API key Gemini dan memuat model yang tersedia', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://generativelanguage.googleapis.com/*' => Http::response([
            'models' => [
                [
                    'name' => 'models/gemini-test',
                    'supportedGenerationMethods' => ['generateContent'],
                ],
                [
                    'name' => 'models/gemini-embedding-test',
                    'supportedGenerationMethods' => ['embedContent'],
                ],
            ],
        ]),
    ]);

    $pengajar = User::factory()->pengajar()->verified()->create();

    $this->actingAs($pengajar)
        ->postJson(route('pengajar.ai-settings.models'), [
            'provider' => 'gemini',
            'api_key' => 'temporary-key',
        ])
        ->assertSuccessful()
        ->assertJsonPath('models', ['gemini-test']);

    Http::assertSent(fn (Request $request) => $request->hasHeader('x-goog-api-key', 'temporary-key'));
});

test('API key dan model diperiksa lagi sebelum pengaturan AI disimpan', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://generativelanguage.googleapis.com/*' => Http::response([
            'models' => [[
                'name' => 'models/gemini-test',
                'supportedGenerationMethods' => ['generateContent'],
            ]],
        ]),
    ]);

    $pengajar = User::factory()->pengajar()->verified()->create();

    $this->actingAs($pengajar)
        ->put(route('pengajar.ai-settings.update'), [
            'provider' => 'gemini',
            'api_key' => 'valid-key',
            'model' => 'gemini-test',
            'enabled' => '1',
        ])
        ->assertRedirect();

    $setting = $pengajar->aiSetting()->firstOrFail();

    expect($setting->api_key)->toBe('valid-key')
        ->and($setting->model)->toBe('gemini-test')
        ->and($setting->enabled)->toBeTrue();
});

test('model yang tidak tersedia tidak dapat disimpan', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://api.openai.com/*' => Http::response([
            'data' => [['id' => 'gpt-live']],
        ]),
    ]);

    $pengajar = User::factory()->pengajar()->verified()->create();

    $this->actingAs($pengajar)
        ->from(route('pengajar.ai-settings.edit'))
        ->put(route('pengajar.ai-settings.update'), [
            'provider' => 'openai',
            'api_key' => 'valid-key',
            'model' => 'gpt-not-available',
            'enabled' => '1',
        ])
        ->assertRedirect(route('pengajar.ai-settings.edit'))
        ->assertSessionHasErrors('model');

    expect($pengajar->aiSetting)->toBeNull();
});

test('endpoint AI privat ditolak sebelum disimpan', function () {
    $pengajar = User::factory()->pengajar()->verified()->create();

    $this->actingAs($pengajar)
        ->from(route('pengajar.ai-settings.edit'))
        ->put(route('pengajar.ai-settings.update'), [
            'provider' => 'custom',
            'endpoint' => 'http://127.0.0.1/v1/chat/completions',
            'model' => 'model-test',
            'enabled' => '1',
        ])
        ->assertRedirect(route('pengajar.ai-settings.edit'))
        ->assertSessionHasErrors('endpoint');

    expect($pengajar->aiSetting)->toBeNull();
});

test('endpoint Ollama loopback ditolak saat memuat model', function () {
    Http::preventStrayRequests();

    $pengajar = User::factory()->pengajar()->verified()->create();

    $this->actingAs($pengajar)
        ->postJson(route('pengajar.ai-settings.models'), [
            'provider' => 'ollama',
            'endpoint' => 'http://127.0.0.1:11434',
        ])
        ->assertUnprocessable()
        ->assertJsonPath('ok', false);

    Http::assertNothingSent();
});

test('API key yang ditolak provider tidak dikembalikan dalam respons', function () {
    Http::preventStrayRequests();
    Http::fake([
        'https://api.openai.com/*' => Http::response([
            'error' => ['message' => 'invalid api key'],
        ], 401),
    ]);

    $pengajar = User::factory()->pengajar()->verified()->create();

    $this->actingAs($pengajar)
        ->postJson(route('pengajar.ai-settings.models'), [
            'provider' => 'openai',
            'api_key' => 'secret-invalid-key',
        ])
        ->assertUnprocessable()
        ->assertJsonPath('ok', false)
        ->assertDontSee('secret-invalid-key');
});

test('siswa menggunakan route pengaturan AI miliknya sendiri', function () {
    $siswa = User::factory()->verified()->create(['role' => 'pelajar']);

    $this->actingAs($siswa)
        ->get(route('pelajar.ai-settings.edit'))
        ->assertOk()
        ->assertSee(route('pelajar.ai-settings.update'), false);
});

test('halaman pengaturan AI menutup output buffer untuk setiap peran', function (string $role) {
    $user = User::factory()->verified()->create(['role' => $role]);
    $bufferSebelum = ob_get_level();

    $this->actingAs($user)
        ->get(route($role.'.ai-settings.edit'))
        ->assertOk();

    expect(ob_get_level())->toBe($bufferSebelum);
})->with(['pelajar', 'pengajar']);
