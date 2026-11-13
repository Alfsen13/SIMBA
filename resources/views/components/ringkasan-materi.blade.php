@props(['materi'])

<section class="mt-6" aria-labelledby="ringkasan-materi">
    <h2 id="ringkasan-materi" class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ __('guru_kelas.materi.ringkasan') }}</h2>
    @if(trim($materi->babSummary?->ringkasan ?? '') !== '')
        <p class="mt-2 overflow-x-auto whitespace-pre-wrap text-sm leading-relaxed text-slate-700 dark:text-slate-300"><x-math :value="$materi->babSummary->ringkasan" /></p>
    @else
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ __('guru_kelas.materi.ringkasan_belum_tersedia') }}</p>
    @endif
</section>
