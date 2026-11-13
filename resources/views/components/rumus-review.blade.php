@props(['review'])

@php
    $belumDitinjau = $review->status === \App\Models\DocumentFormulaReview::STATUS_PENDING;
    $badge = match ($review->status) {
        \App\Models\DocumentFormulaReview::STATUS_APPROVED => 'success',
        \App\Models\DocumentFormulaReview::STATUS_REJECTED => 'danger',
        default => 'warning',
    };
@endphp

<div
    x-data="rumusReview({{ json_encode((string) ($review->latex ?? '')) }})"
    class="rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4"
    x-bind:class="centang[{{ $review->id }}] === true && 'border-emerald-500 dark:border-emerald-600'"
>
    <div class="flex flex-wrap items-center gap-2">
        @if($belumDitinjau)
            <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 dark:text-slate-400">
                <input
                    type="checkbox"
                    name="selected[]"
                    value="{{ $review->id }}"
                    x-model="centang[{{ $review->id }}]"
                    class="size-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500/30 dark:border-slate-600 dark:bg-slate-800"
                >
                <span>{{ __('guru_kelas.materi.rumus_keranjang_pilih') }}</span>
            </label>
        @endif

        <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">
            {{ __('guru_kelas.materi.halaman', ['number' => $review->page]) }}
        </span>
        <x-badge :variant="$badge" :label="__('guru_kelas.materi.rumus_status_'.$review->status)" />
    </div>

    <img
        src="data:image/png;base64,{{ $review->image_base64 }}"
        alt="{{ __('guru_kelas.materi.halaman', ['number' => $review->page]) }}"
        class="mt-3 max-h-40 w-auto max-w-full rounded border border-slate-200 dark:border-slate-700 bg-white object-contain"
    >

    @if($belumDitinjau && $review->ocr_error)
        <p class="mt-3 text-xs text-amber-700 dark:text-amber-400">{{ __('guru_kelas.materi.rumus_ocr_gagal') }}</p>
    @endif

    <div class="mt-3">
        <label for="latex-{{ $review->id }}" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
            {{ __('guru_kelas.materi.rumus_latex') }}
        </label>
        <input
            id="latex-{{ $review->id }}"
            name="reviews[{{ $review->id }}][latex]"
            type="text"
            maxlength="2000"
            autocomplete="off"
            x-model="latex"
            @if(! $belumDitinjau) disabled @endif
            class="w-full px-3 py-2 text-sm font-mono text-slate-900 dark:text-slate-100 bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        >
    </div>

    @if($belumDitinjau)
        <div x-show="siapDikirim" x-cloak class="mt-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/50 px-3 py-2">
            <span class="text-xs font-medium text-slate-600 dark:text-slate-400">{{ __('guru_soal.pratinjau_rumus') }}:</span>
            <div class="mt-1 overflow-x-auto text-sm text-slate-700 dark:text-slate-300" x-html="mathPreview('$' + latex + '$')"></div>
        </div>
    @endif
</div>
