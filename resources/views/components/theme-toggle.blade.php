@php $themeLabel = __('common.toggle_theme'); @endphp

{{--
    Tombol toggle terang/gelap. State disinkronkan dengan class `.dark` di <html>
    (yang sudah dipasang skrip no-FOUC), lalu pilihan disimpan ke localStorage
    supaya bertahan antar halaman. `x-data` membaca class saat Alpine start,
    jadi tidak pernah berbeda dari tampilan awal.
--}}
<button
    type="button"
    x-data="{ dark: document.documentElement.classList.contains('dark') }"
    @click="dark = !dark; document.documentElement.classList.toggle('dark', dark); try { localStorage.setItem('theme', dark ? 'dark' : 'light'); } catch (e) {}"
    title="{{ $themeLabel }}"
    aria-label="{{ $themeLabel }}"
    {{ $attributes->merge(['class' => 'min-h-11 min-w-11 p-2 text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800 rounded-lg transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700 dark:focus-visible:outline-emerald-300']) }}
>
    <svg aria-hidden="true" x-show="!dark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
    </svg>
    <svg aria-hidden="true" x-show="dark" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
    </svg>
</button>
