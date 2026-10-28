@props([
    // Nama variabel Alpine yang berisi objek soal ('q' untuk PG, 's' untuk esai).
    'soal' => 'q',
])

{{--
    Badge keakurasian soal terhadap materi — hanya muncul ketika soal hasil
    generate AI membawa `akurasi_materi`. Soal manual tidak menampilkan ini.
--}}
<template x-if="{{ $soal }}.akurasi_materi !== undefined && {{ $soal }}.akurasi_materi !== null">
    <div class="mb-2 flex flex-wrap items-center gap-2">
        <span
            title="{{ __('guru_soal.akurasi_help') }}"
            class="inline-flex items-center gap-1.5 rounded-md px-2 py-0.5 text-[11px] font-medium"
            :class="{{ $soal }}.akurasi_materi >= 80 ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/30' : ({{ $soal }}.akurasi_materi >= 60 ? 'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200 dark:bg-yellow-500/10 dark:text-yellow-300 dark:ring-yellow-500/30' : 'bg-red-50 text-red-700 ring-1 ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30')"
        >
            {{ __('guru_soal.akurasi_materi') }}: <span x-text="{{ $soal }}.akurasi_materi"></span>%
        </span>
        <span
            x-show="{{ $soal }}.catatan_akurasi"
            x-cloak
            class="text-xs text-slate-500 dark:text-slate-400"
            x-text="{{ $soal }}.catatan_akurasi"
        ></span>
    </div>
</template>
