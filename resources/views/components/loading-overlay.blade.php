@props([
    'judul',
    'heading' => __('komponen.ai_sedang_menyusun'),
    'catatan' => __('komponen.catatan_waktu_proses'),
    'footer' => __('komponen.mohon_tunggu'),
])

<div
    x-show="generating"
    x-cloak
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
>
    <div class="bg-white dark:bg-slate-800 rounded-lg shadow-xl w-full max-w-md p-6 text-center">
        <div class="mx-auto w-14 h-14 mb-5">
            <svg class="w-14 h-14 text-emerald-700 animate-spin" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $heading }} {{ $judul }}</h3>
        <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
            {{ $catatan }}
        </p>
        <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">{{ $footer }}</p>
    </div>
</div>