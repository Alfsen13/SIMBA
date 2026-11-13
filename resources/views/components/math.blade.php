@props(['value' => ''])

{{--
    Menampilkan teks yang mungkin memuat rumus LaTeX. Isi asli hanya disimpan di
    `data-raw` (di-escape otomatis oleh Blade) lalu dirender KaTeX oleh
    `resources/js/pages/math-content.js`. Teks mentah sengaja TIDAK dicetak sebagai
    isi elemen agar `$$x^2$$` tidak sempat terbaca; `aria-label` menjaga teks tetap
    tersedia untuk screen reader dan `<noscript>` jadi fallback saat JS mati.
--}}
<span class="math-content" data-raw="{{ $value }}" aria-label="{{ $value }}"><span aria-hidden="true">…</span><noscript>{{ $value }}</noscript></span>
