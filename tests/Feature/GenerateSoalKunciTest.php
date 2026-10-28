<?php

/*
 * Penjaga "kunci jawaban hasil AI otomatis tercentang" di builder soal PG.
 *
 * Dua hal harus berlaku supaya radio kunci jawaban tercentang setelah AI
 * mengembalikan `jawaban`:
 *
 * 1. State editor benar-benar membawa indeks jawaban dari AI.
 * 2. Tiap soal punya grup radio sendiri (`:name="'jawaban_' + i"`).
 *
 * Nomor 2 yang pernah rusak di halaman kuis: `name="jawaban_radio"` ditulis
 * statis, jadi seluruh opsi dari SEMUA soal berbagi satu grup radio. Dalam satu
 * form, browser treats radio dengan nama sama sebagai grup eksklusif — hanya
 * satu yang boleh `checked`, yaitu yang terakhir di-set. Akibatnya guru melihat
 * kunci jawaban AI tercentang pada satu soal saja, padahal soal lain juga punya
 * kunci. Tidak ada error; hanya "hilang" di mata.
 *
 * Alpine sudah menyetel `el.checked = el.value == value` saat init (lihat
 * `effect` pada `x-model` di node_modules/alpinejs), jadi begitu nama radio
 * dipisah per soal, kunci jawaban langsung tampil centang tanpa perlu logika
 * tambahan di builder.
 *
 * Assertion di sini membaca sumber Blade, bukan response HTTP: centang/unchecked
 * adalah perilaku runtime Alpine di browser, jadi tidak bisa diamati dari test
 * server tanpa browser test.
 */

use Illuminate\Support\Facades\File;

$builderViews = [
    'guru/kuis/create.blade.php',
    'guru/kuis/edit.blade.php',
    'guru/tugas/create.blade.php',
    'guru/tugas/edit.blade.php',
];

it('tidak ada grup radio statis yang dipakai bersama antar soal', function () use ($builderViews) {
    foreach ($builderViews as $view) {
        $isi = File::get(resource_path('views/'.$view));

        // `name="jawaban_radio"` (tanpa `:`) = satu grup untuk semua soal.
        expect($isi)
            ->not->toMatch('/(?<!:)\bname="jawaban_radio"/');
    }
});

it('radio kunci jawaban dikelompokkan per soal lewat indeks i', function () use ($builderViews) {
    foreach ($builderViews as $view) {
        $isi = File::get(resource_path('views/'.$view));

        expect($isi)->toContain(':name="\'jawaban_\' + i"');
    }
});

it('opsi kunci jawaban ditandai visual supaya guru bisa lihat', function () use ($builderViews) {
    foreach ($builderViews as $view) {
        $isi = File::get(resource_path('views/'.$view));

        expect($isi)->toContain('x-show="Number(q.jawaban) === Number(j)"');
    }
});

it('builder kuis tetap membawa indeks jawaban dari AI ke state editor', function () {
    $isi = File::get(resource_path('js/pages/kuis-builder.js'));

    // Tanpa baris ini, `jawaban` hasil AI dibuang dan radio jatuh ke default 0.
    expect($isi)->toContain('jawaban: q.jawaban ?? 0');
    expect($isi)->toMatch('/const jawaban = \[0, 1, 2, 3\]\.includes\(Number\(s\.jawaban\)\)/');
});
