@extends('layouts.app')

@section('content')
    @php
        $aiRoutePrefix = auth()->user()->role === 'pelajar' ? 'pelajar' : 'pengajar';
        $savedProvider = $setting->provider;
        $selectedModel = old('model', $setting->model);
    @endphp

    <div class="max-w-2xl mx-auto">
        <div class="mb-6">
            <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">Pengaturan AI Pribadi</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Gunakan API AI milik Anda sendiri (OpenAI, Nvidia NIM, Ollama, Gemini, Anthropic, atau endpoint custom)
                untuk generate soal, remedial, dan AI Tutor. Jika tidak dikonfigurasi, sistem menggunakan provider global.
            </p>
        </div>

        @if (session('success'))
            <div class="mb-4 p-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded text-emerald-800 dark:text-emerald-300 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route($aiRoutePrefix . '.ai-settings.update') }}" class="space-y-4" id="ai-settings-form">
            @csrf
            @method('PUT')

            <div class="bg-white dark:bg-slate-800 p-5 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form-select
                        name="provider"
                        label="Provider AI"
                        :value="$setting->provider"
                        :options="[
                            'ollama'      => 'Ollama (Local)',
                            'gemini'      => 'Google Gemini',
                            'nvidia_nim'  => 'Nvidia NIM',
                            'openai'      => 'OpenAI',
                            'anthropic'   => 'Anthropic (Claude)',
                            'custom'      => 'Endpoint Custom',
                        ]"
                    />

                    <x-form-select
                        name="model"
                        label="Model"
                        :value="$selectedModel"
                        :options="$selectedModel ? [$selectedModel => $selectedModel] : []"
                        required
                    />

                    <div id="model-manual-wrapper" class="hidden">
                        <label for="model-manual" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Model</label>
                        <input
                            type="text"
                            name="model"
                            id="model-manual"
                            value="{{ old('model', $setting->model) }}"
                            placeholder="Masukkan nama model"
                            class="w-full px-3 py-2 text-sm text-slate-900 dark:text-slate-100 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-colors duration-150"
                            disabled
                        />
                        @error('model')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div id="endpoint-wrapper">
                        <x-form-input
                            name="endpoint"
                            label="Endpoint URL"
                            :value="old('endpoint', $setting->endpoint)"
                            placeholder="http://host.docker.internal:11434"
                            hint="Diperlukan untuk Ollama dan endpoint custom."
                        />
                    </div>

                    <div id="api-key-wrapper">
                        <x-form-input
                            name="api_key"
                            label="API Key"
                            type="password"
                            placeholder="Masukkan API key provider"
                            hint="Diuji saat memuat model dan disimpan terenkripsi. Kosongkan untuk memakai key yang sudah tersimpan."
                        />
                    </div>
                </div>

                <div class="mt-2">
                    <button type="button" id="load-models" class="btn-secondary">
                        Muat model yang tersedia
                    </button>
                    <div id="models-result" class="mt-3 hidden p-3 rounded text-sm" role="status"></div>
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                        API key divalidasi saat model dimuat. Untuk endpoint custom, isi nama model secara manual; tombol Test Koneksi menguji pemanggilan model.
                    </p>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Aktifkan</label>
                        <input type="hidden" name="enabled" value="0" />
                        <input
                            type="checkbox"
                            name="enabled"
                            value="1"
                            {{ old('enabled', $setting->enabled ?? true) ? 'checked' : '' }}
                            class="rounded-slate bg-slate-50 dark:bg-slate-700 border-slate-300 dark:border-slate-600 text-emerald-600 focus:ring-emerald-500 focus:ring-2"
                        />
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-form-input
                        name="options[temperature]"
                        label="Temperature"
                        type="number"
                        step="0.1"
                        min="0"
                        max="2"
                        :value="old('options.temperature', $setting->options['temperature'] ?? 0.7)"
                        placeholder="0.7"
                    />
                    <x-form-input
                        name="options[max_tokens]"
                        label="Max Tokens"
                        type="number"
                        :value="old('options.max_tokens', $setting->options['max_tokens'] ?? 4096)"
                        placeholder="4096"
                    />
                </div>

                <div class="mt-6 flex flex-col sm:flex-row gap-3">
                    <button type="submit" class="btn-primary flex-1">
                        Simpan Pengaturan
                    </button>

                    <button
                        type="button"
                        id="test-ai"
                        class="btn-secondary flex-1"
                    >
                        Test Koneksi
                    </button>
                </div>

                <div id="test-result" class="mt-3 hidden p-3 rounded text-sm"></div>
            </div>
        </form>
    </div>

@endsection

@push('scripts')
<script>
document.getElementById('test-ai').addEventListener('click', async () => {
    const btn = document.getElementById('test-ai');
    const result = document.getElementById('test-result');

    btn.disabled = true;
    btn.textContent = 'Menguji...';
    result.classList.add('hidden');
    result.className = 'mt-3 p-3 rounded text-sm';

    try {
        const res = await fetch('{{ route($aiRoutePrefix . '.ai-settings.test') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
        });

        const data = await res.json();

        if (data.ok) {
            result.className = 'mt-3 p-3 rounded text-sm bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300';
            result.textContent = '✓ Berhasil terhubung: ' + data.reply;
        } else {
            result.className = 'mt-3 p-3 rounded text-sm bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300';
            result.textContent = '✗ Gagal: ' + (data.error ?? 'Tidak diketahui');
        }
    } catch (e) {
        result.className = 'mt-3 p-3 rounded text-sm bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300';
        result.textContent = '✗ Terjadi kesalahan jaringan.';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Test Koneksi';
        result.classList.remove('hidden');
    }
});

const provider = document.getElementById('provider');
const modelSelect = document.getElementById('model');
const modelManual = document.getElementById('model-manual');
const manualWrapper = document.getElementById('model-manual-wrapper');
const endpointWrapper = document.getElementById('endpoint-wrapper');
const apiKeyWrapper = document.getElementById('api-key-wrapper');
const endpointInput = document.getElementById('endpoint');
const apiKeyInput = document.getElementById('api_key');
const loadModelsButton = document.getElementById('load-models');
const modelsResult = document.getElementById('models-result');
const savedProvider = @json($savedProvider);
const modelsUrl = @json(route($aiRoutePrefix . '.ai-settings.models'));
const csrfToken = @json(csrf_token());

function updateProviderFields() {
    const isCustom = provider.value === 'custom';
    const isOllama = provider.value === 'ollama';

    modelSelect.disabled = isCustom;
    modelSelect.required = !isCustom;
    modelManual.disabled = !isCustom;
    modelManual.required = isCustom;
    manualWrapper.classList.toggle('hidden', !isCustom);
    endpointWrapper.classList.toggle('hidden', !isOllama && !isCustom);
    apiKeyWrapper.classList.toggle('hidden', isOllama);
    loadModelsButton.disabled = isCustom;
    loadModelsButton.classList.toggle('hidden', isCustom);

    if (isCustom && modelSelect.value) {
        modelManual.value = modelSelect.value;
    }

    if (!isCustom && modelManual.value) {
        selectModelOption(modelManual.value);
    }
}

function selectModelOption(value) {
    if (!value) {
        return;
    }

    let option = [...modelSelect.options].find((item) => item.value === value);
    if (!option) {
        option = new Option(value, value);
        modelSelect.add(option);
    }
    modelSelect.value = value;
}

async function loadModels() {
    if (provider.value === 'custom') {
        return;
    }

    loadModelsButton.disabled = true;
    loadModelsButton.textContent = 'Memvalidasi dan memuat...';
    modelsResult.className = 'mt-3 p-3 rounded text-sm';
    modelsResult.textContent = '';

    const payload = {
        provider: provider.value,
        api_key: apiKeyInput.value,
    };
    if (endpointInput.value) {
        payload.endpoint = endpointInput.value;
    }

    try {
        const response = await fetch(modelsUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(payload),
        });
        const data = await response.json();

        if (!response.ok || !data.ok) {
            throw new Error(data.error ?? 'Tidak dapat memuat model.');
        }

        const selected = modelSelect.value;
        modelSelect.replaceChildren(new Option('Pilih model', ''));
        data.models.forEach((model) => modelSelect.add(new Option(model, model)));
        modelSelect.value = data.models.includes(selected) ? selected : (data.models[0] ?? '');
        modelsResult.className = 'mt-3 p-3 rounded text-sm bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300';
        modelsResult.textContent = `API key valid. ${data.models.length} model tersedia.`;
    } catch (error) {
        modelsResult.className = 'mt-3 p-3 rounded text-sm bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300';
        modelsResult.textContent = error.message;
    } finally {
        loadModelsButton.disabled = provider.value === 'custom';
        loadModelsButton.textContent = 'Muat model yang tersedia';
    }
}

provider.addEventListener('change', () => {
    updateProviderFields();
    if (provider.value === 'ollama') {
        loadModels();
    } else if (provider.value !== 'custom' && apiKeyInput.value.trim() !== '') {
        loadModels();
    }
});
apiKeyInput.addEventListener('change', loadModels);
endpointInput.addEventListener('change', () => {
    if (provider.value === 'ollama') {
        loadModels();
    }
});
loadModelsButton.addEventListener('click', loadModels);
updateProviderFields();

if (savedProvider === provider.value && provider.value !== 'custom') {
    loadModels();
}
</script>
@endpush