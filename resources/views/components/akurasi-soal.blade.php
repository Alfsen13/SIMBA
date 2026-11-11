@props([
    'soal' => 'q',
])

<template x-if="Object.prototype.hasOwnProperty.call({{ $soal }}, 'kemiripan_materi')">
    <div class="mb-2 text-xs text-slate-700 dark:text-slate-300">
        <p>
            {{ __('guru_soal.kemiripan_materi') }}:
            <span x-show="{{ $soal }}.kemiripan_pertanyaan === {{ $soal }}.pertanyaan && {{ $soal }}.kemiripan_materi !== null" x-text="{{ $soal }}.kemiripan_materi"></span>
            <span x-show="{{ $soal }}.kemiripan_pertanyaan !== {{ $soal }}.pertanyaan || {{ $soal }}.kemiripan_materi === null" x-cloak>{{ __('guru_soal.kemiripan_tidak_tersedia') }}</span>
        </p>
        <p>{{ __('guru_soal.kemiripan_help') }}</p>
    </div>
</template>
