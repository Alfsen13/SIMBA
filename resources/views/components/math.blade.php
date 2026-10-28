@props(['value' => ''])

{{--
    Menampilkan teks yang mungkin memuat rumus LaTeX. Isi asli disimpan di
    `data-raw` (di-escape otomatis oleh Blade) lalu dirender KaTeX oleh
    `resources/js/pages/math-content.js` saat halaman selesai dimuat.
--}}
<span class="math-content" data-raw="{{ $value }}">{{ $value }}</span>
