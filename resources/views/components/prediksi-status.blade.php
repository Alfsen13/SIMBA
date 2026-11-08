@props(['status'])

@php($statusAwal = $status['status'] ?? 'idle')

@if($statusAwal !== 'idle')
    <div
        x-data="{
            state: {{ json_encode($statusAwal, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR) }},
            endpoint: {{ json_encode(route('pengajar.prediksi.status'), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) }},
            async poll() {
                try {
                    const response = await fetch(this.endpoint, {
                        headers: { Accept: 'application/json' },
                    });
                    if (!response.ok) throw new Error('Status prediksi tidak dapat dimuat.');
                    this.state = (await response.json()).status;
                    if (this.state === 'completed') {
                        window.location.reload();
                        return;
                    }
                    if (['queued', 'processing'].includes(this.state)) {
                        window.setTimeout(() => this.poll(), 3000);
                    }
                } catch {
                    this.state = 'unavailable';
                }
            },
        }"
        x-init="if (['queued', 'processing'].includes(state)) window.setTimeout(() => poll(), 1500)"
        class="mb-4 rounded-lg border p-4 text-sm"
        :class="state === 'failed' || state === 'unavailable'
            ? 'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-500/10 dark:text-red-300'
            : state === 'completed'
                ? 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300'
                : 'border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-800 dark:bg-blue-500/10 dark:text-blue-300'"
        role="status"
        aria-live="polite"
    >
        <p x-show="state === 'queued'">{{ __('guru_analitik.prediksi.queued') }}</p>
        <p x-show="state === 'processing'">{{ __('guru_analitik.prediksi.processing') }}</p>
        <p x-show="state === 'completed'">{{ __('guru_analitik.prediksi.completed') }}</p>
        <p x-show="state === 'failed'">{{ __('guru_analitik.prediksi.failed') }}</p>
        <p x-show="state === 'unavailable'">{{ __('guru_analitik.prediksi.status_unavailable') }}</p>
    </div>
@endif
