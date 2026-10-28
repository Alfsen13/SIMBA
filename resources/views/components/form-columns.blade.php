{{--
    Wrapper kolom responsif untuk halaman form.

    Kartu form sebelumnya dibatasi `max-w-xl` / `max-w-2xl` supaya input tidak
    selebar 2000px di monitor besar. Tapi batas itu ditempel tanpa `mx-auto`,
    jadi kartu menempel di kiri dan separuh layar jadi ruang kosong — gejalanya
    seperti kartu terpotong di kanan.

    Wrapper ini melepas batasnya (kartu ikut lebar layar) lalu membagi isian ke
    beberapa kolom lewat flex: `flex-wrap` + `basis` membuat tiap isian
    selebar basis dan otomatis turun ke baris baru kalau ruang tidak cukup.

    WAJIB mengeluarkan `{{ $slot }}`. Versi pertama komponen lupa mengeluarkannya
    dan seluruh isi form hilang tanpa error Blade — test penjaganya ada di
    `tests/Feature/LayoutFormTest.php`.
--}}
@props([
    'cols' => 1,
])

@php
    $basis = match ((int) $cols) {
        2 => 'md:basis-1/2',
        3 => 'md:basis-1/3',
        default => 'md:basis-full',
    };
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-wrap -mx-3']) }}>
    <div class="w-full {{ $basis }} px-3">
        {{ $slot }}
    </div>
</div>